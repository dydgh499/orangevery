<script setup lang="ts">
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab'
import { DateSetter } from '@/views/searcher'
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { DateFilters } from '@core/enums'
import corp from '@corp'
import { ko } from 'date-fns/locale'

interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    date_filter_type: number | null,
    sub_search_name?: string,
    sub_search_placeholder?: string,
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const head = <any>(inject('head'))
const exporter = <any>(inject('exporter'))
const formatDate = <any>(inject('$formatDate'))
const formatTime = <any>(inject('$formatTime'))
const { theme } = useThemeConfig()
const dates = [
    { id: 'today', title: '당일' },
    { id: '1 day', title: '어제' },
    { id: '3 day', title: '3일전' },
    { id: '1 mon', title: '1개월' },
    { id: '3 mon', title: '3개월' },
]
const {
    getRangeFormat,
    setDateRange,
    init,
    dateChanged,
    range_date,
    date,
    date_selecter,
} = DateSetter(props, formatDate, formatTime)

const enable = ref(true)
const format = ref({})
const time_picker = ref(true)
const search = ref(<string>(''))
const search2 = ref(<string>(''))

const handleEnterKey = (event: KeyboardEvent) => {
    if (event.keyCode === 13) {
        store.setTable()
        store.updateQueryString({ search: search.value, search2: search2.value })
    }
}

const getLastParams = () => {
    const params = useDynamicTabStore().getLastParams()
    if(params) {
        Object.assign(store.params, params)
        if(store.params.search)
            search.value = store.params.search
        if(store.params.search2)
            search.value = store.params.search2
    }
}

const useDateSelecter = computed(() => {
    return (
        head.path === 'transactions' || head.path === 'transactions/summary' || 
        head.path === 'transactions/settle/salesforces' || head.path === 'transactions/settle/merchandises' || 
        head.path === 'transactions/settle-histories/merchandises' || head.path === 'transactions/settle-histories/salesforces' ||
        head.path === 'services/head-office-accounts' || head.path === 'services/cms-transactions' ||
        head.path === 'services/operator-historiesv2'        
    )
})

if (props.date_filter_type == DateFilters.DATE_RANGE) {
    enable.value = true
    format.value = { format: 'yyyy-MM-dd HH:mm:ss' }
    time_picker.value = true
}
else if (props.date_filter_type == DateFilters.SETTLE_RANGE) {
    enable.value = false
    format.value = { format: 'yyyy-MM-dd' }
    time_picker.value = false
}
getLastParams()
init(store)
</script>
<template>
    <VCol cols="12">
        <VCardText :style="$vuetify.display.smAndDown ? 'padding: 0px;' : ''">
            <template v-if="$vuetify.display.smAndDown">
                <VRow 
                    v-if="props.date_filter_type == DateFilters.DATE_RANGE || props.date_filter_type == DateFilters.SETTLE_RANGE"
                    style="align-items: center; justify-content: space-around;"
                    class="compact-density">
                    <VCol :cols="12">
                        <VueDatePicker 
                            v-if="corp.pv_options.free.use_search_date_detail"
                            v-model="range_date" :enable-seconds="enable" :text-input="format"
                            locale="ko" :format-locale="ko" range multi-calendars :dark="theme === 'dark'"
                            autocomplete="on" utc :format="getRangeFormat" :teleport="true"
                            select-text="Search"
                            :enable-time-picker="time_picker"
                            @update:modelValue="[dateChanged(store)]" />
                        <template v-else>
                            <div style="display: flex;justify-content: space-between;">
                                <AppDateTimePicker 
                                    v-model="range_date[0]" 
                                    prepend-inner-icon="ic-baseline-calendar-today" 
                                    label="시작일 입력" 
                                    @update:modelValue="[dateChanged(store)]"
                                    style="flex-grow: 1; margin-right: 0.25em;"
                                />
                                <AppDateTimePicker 
                                    v-model="range_date[1]" 
                                    prepend-inner-icon="ic-baseline-calendar-today" 
                                    label="종료일 입력" 
                                    @update:modelValue="[dateChanged(store)]"
                                    style="flex-grow: 1; margin-left: 0.25em;"
                                />
                            </div>
                        </template>
                    </VCol>
                    <VCol cols="12" v-if="useDateSelecter" >
                        <VSelect 
                            v-model="date_selecter" :items="[{ id: null, title: '기간 조회' }].concat(dates)"
                            density="compact" variant="outlined" item-title="title" item-value="id" label="기간 조회"
                            @update:modelValue="[setDateRange(), dateChanged(store)]"
                            />
                    </VCol>
                </VRow>
                <VRow v-else-if="props.date_filter_type == DateFilters.DATE">
                    <VCol cols="6">
                        <AppDateTimePicker 
                            v-model="date" 
                            prepend-inner-icon="ic-baseline-calendar-today" 
                            label="조회일 입력" 
                            @update:modelValue="[dateChanged(store)]"
                            style="min-width: 11em;"
                        />
                    </VCol>
                </VRow>
                <VRow style="align-items: center; justify-content: space-around; justify-content: center;">
                    <VCol cols="12" style="display: flex;">
                        <VTextField id="search" :placeholder="props.placeholder" density="compact" v-model="search"
                            :label="props.add_name + ' 정보 검색'" @keyup="handleEnterKey" prepend-inner-icon="tabler:search">
                            <VTooltip activator="parent" location="top" >
                                {{ props.placeholder }}
                            </VTooltip>
                        </VTextField>

                        <VBtn prepend-icon="tabler:search"  size="small" style="height: 36px;margin-left: 0.5em;"
                            @click="store.setTable(); store.updateQueryString({ search: search })">
                            검색
                        </VBtn>
                    </VCol>
                    <VCol cols="12" style="padding: 0 12px;" v-show="props.sub_search_name">
                        <VTextField  id="search2" :placeholder="props.sub_search_placeholder" 
                            density="compact" v-model="search2" @keyup="handleEnterKey" prepend-inner-icon="tabler:search"
                            :label="props.sub_search_name + ' 정보 검색'">
                            <VTooltip activator="parent" location="top">
                                {{ props.sub_search_placeholder }}
                            </VTooltip>
                        </VTextField>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol cols="12" style="display: flex; flex-direction: column; justify-content: center;">
                        <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter" 
                            :style="'margin: 0.25em;'" size="small"
                            @click="head.filter.show()">
                            검색필터
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel" 
                            :style="'margin: 0.25em;'" size="small"
                            @click="exporter()">
                            엑셀추출
                        </VBtn>
                        <VBtn prepend-icon="tabler-plus" @click="store.edit(0)" v-if="props.add" 
                            :style="'margin: 0.25em;'" size="small">
                            {{ props.add_name }}
                        </VBtn>
                        <slot name="index_extra_field"></slot>                                
                    </VCol>
                </VRow>
            </template>
            <template v-else>
                <VRow>
                    <div class="d-inline-flex align-center flex-wrap gap-4 float-left justify-center">
                        <template
                            v-if="props.date_filter_type == DateFilters.DATE_RANGE || props.date_filter_type == DateFilters.SETTLE_RANGE">
                            <template v-if="corp.pv_options.free.use_search_date_detail">
                                <div class="d-inline-flex">
                                    <VueDatePicker v-model="range_date" :enable-seconds="enable" :text-input="format"
                                        locale="ko" :format-locale="ko" range multi-calendars :dark="theme === 'dark'"
                                        autocomplete="on" utc :format="getRangeFormat" :teleport="true"
                                        input-class-name="search-input" select-text="Search"
                                        :enable-time-picker="time_picker"
                                        @update:modelValue="[dateChanged(store)]" />
                                </div>
                            </template>
                            <template v-else>
                                <AppDateTimePicker 
                                    v-model="range_date[0]" 
                                    prepend-inner-icon="ic-baseline-calendar-today" 
                                    label="시작일 입력" 
                                    @update:modelValue="[dateChanged(store)]"
                                    style="min-width: 11em;"
                                />
                                <AppDateTimePicker 
                                    v-model="range_date[1]" 
                                    prepend-inner-icon="ic-baseline-calendar-today" 
                                    label="종료일 입력" 
                                    @update:modelValue="[dateChanged(store)]"
                                    style="min-width: 11em;"
                                />
                            </template>
                        </template>
                        <template v-else-if="props.date_filter_type == DateFilters.DATE">
                            <AppDateTimePicker 
                                v-model="date" 
                                prepend-inner-icon="ic-baseline-calendar-today" 
                                label="조회일 입력" 
                                @update:modelValue="[dateChanged(store)]"
                                style="min-width: 11em;"
                            />
                        </template>
                        <template v-if="useDateSelecter">
                            <VSelect v-model="date_selecter" :items="[{ id: null, title: '기간 조회' }].concat(dates)"
                                density="compact" variant="outlined" item-title="title" item-value="id" label="기간 조회" single-line
                                @update:modelValue="[setDateRange(), dateChanged(store)]"
                            />
                        </template>
                        <VTextField id="search" :placeholder="props.placeholder" density="compact" v-model="search"
                            @keyup="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input"
                            :label="add_name +' 정보 검색'">
                            <VTooltip activator="parent" location="top">
                                {{ props.placeholder }}
                            </VTooltip>
                        </VTextField>
                        <VTextField v-show="props.sub_search_name"  id="search2" :placeholder="props.sub_search_placeholder" 
                            density="compact" v-model="search2" @keyup="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input"
                            :label="props.sub_search_name + ' 정보 검색'">
                            <VTooltip activator="parent" location="top">
                                {{ props.sub_search_placeholder }}
                            </VTooltip>
                        </VTextField>
                        <VBtn prepend-icon="tabler:search"  size="small"
                            @click="store.setTable(); store.updateQueryString({ search: search, search2: search2 });">
                            검색
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter" size="small"
                            @click="head.filter.show()">
                            검색 필터
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel" size="small"
                            @click="exporter()">
                            엑셀 추출
                        </VBtn>
                        <VBtn prepend-icon="tabler-plus" @click="store.edit(0)" v-if="props.add" size="small">
                            {{ props.add_name }}
                            <VTooltip activator="parent" location="top">
                                추가하기
                            </VTooltip>
                        </VBtn>
                        <slot name="index_extra_field"></slot>
                    </div>
                </VRow>
            </template>
        </VCardText>
    </VCol>
</template>
<style scoped>
.search-input {
  min-inline-size: 15.35rem;
}

:deep(.v-field__field) {
  block-size: 36px;
}

:deep(.v-field__input) {
  min-block-size: 36px !important;
  padding-block-start: 2px !important;
}
</style>
