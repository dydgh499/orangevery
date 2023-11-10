<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { ko } from 'date-fns/locale'
import { DateFilters } from '@core/enums'
import { salesLevels } from '@/views/salesforces/useStore'
import { DateSetter } from '@/views/searcher'
import type { Options } from '@/views/types'
import corp from '@corp'

interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    date_filter_type: number | null,
}

const route = useRoute()
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


const queryToStoreParams = () => {
    const str_keys = ['search', 's_dt', 'e_dt', 'dt']
    const keys = Object.keys(route.query).filter(key => !str_keys.includes(key));
    for (let i = 0; i < keys.length; i++) {
        store.params[keys[i]] = route.query[keys[i]] != null ? parseInt(route.query[keys[i]] as string) : null
    }

    if (!store.params.page)
        store.params.page = 1
    if (!store.params.page_size)
        store.params.page_size = 20

    if (route.query.search) {
        store.params.search = route.query.search
        search.value = store.params.search
    }
    store.updateQueryString(store.params)
}

const handleEnterKey = (event: KeyboardEvent) => {
    if (event.keyCode === 13) {
        store.setTable()
        store.updateQueryString({ search: search.value })
    }
}

const getSalesforceItems = computed(() => {
    if (head.path === 'salesforces' || head.path === 'transactions/settle-histories/salesforces')
        return [<Options>({ id: null, title: '전체' })].concat(salesLevels())
    else
        return salesLevels()
})

if (props.date_filter_type == DateFilters.DATE_RANGE) {
    store.params.use_search_date_detail = Number(corp.pv_options.free.use_search_date_detail)
    enable.value = true
    format.value = { format: 'yyyy-MM-dd HH:mm:ss' }
    time_picker.value = true
}
else if (props.date_filter_type == DateFilters.SETTLE_RANGE) {
    enable.value = false
    format.value = { format: 'yyyy-MM-dd' }
    time_picker.value = false
}
init(store)
queryToStoreParams()

</script>
<template>
    <div class="d-inline-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12">
            <VCardText>
                <VRow>
                    <div class="d-inline-flex align-center flex-wrap gap-4 float-left justify-center">
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
                                    <VTextField type="date" v-model="range_date[0]"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line
                                        @update:modelValue="[dateChanged(store)]" />
                                    <VTextField type="date" v-model="range_date[1]"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="종료일 입력" single-line
                                        @update:modelValue="[dateChanged(store)]" />
                                </template>
                            </template>
                            <template v-else-if="props.date_filter_type == DateFilters.DATE">
                                <VueDatePicker v-model="date" :text-input="{ format: 'yyyy-MM-dd' }" locale="ko"
                                    :format-locale="ko" :dark="theme === 'dark'" autocomplete="on" utc :format="formatDate"
                                    :teleport="true" @update:modelValue="[dateChanged(store)]" />
                            </template>
                            <template v-if="head.path === 'transactions'">
                                <VSelect v-model="date_selecter" :items="[{ id: null, title: '기간 조회' }].concat(dates)"
                                    density="compact" variant="outlined" item-title="title" item-value="id"
                                    style="min-width: 10em;" @update:modelValue="[setDateRange(), dateChanged(store)]"
                                    label="기간 조회" />
                            </template>
                            <template
                                v-else-if="head.path === 'salesforces' || head.path === 'transactions/settle/salesforces' || head.path === 'transactions/settle-histories/salesforces'">
                                <VSelect v-model="store.params.level" :items="getSalesforceItems" density="compact"
                                    variant="outlined" item-title="title" item-value="id" style="min-width: 10em;"
                                    @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                            </template>
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)">
                                엑셀 추출
                            </VBtn>
                        </div>
                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField id="search" :placeholder="props.placeholder" density="compact" v-model="search"
                                @keyup="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input">
                                <VTooltip activator="parent" location="top">
                                    {{ props.placeholder }}
                                </VTooltip>
                            </VTextField>
                            <VBtn prepend-icon="tabler:search"
                                @click="store.setTable(); store.updateQueryString({ search: search })">
                                검색
                            </VBtn>
                            <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter"
                                @click="head.filter.show()">
                                검색 필터
                            </VBtn>
                            <VBtn prepend-icon="tabler-plus" @click="store.edit(0)" v-if="props.add">
                                {{ props.add_name }} 추가
                            </VBtn>
                            <slot name="index_extra_field"></slot>
                        </div>
                    </div>
                </VRow>
            </VCardText>
        </VCol>
    </div>
</template>
