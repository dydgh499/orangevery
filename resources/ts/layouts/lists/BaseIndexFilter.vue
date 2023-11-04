<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { ko } from 'date-fns/locale'
import { DateFilters } from '@core/enums'
import { salesLevels } from '@/views/salesforces/useStore'
import { DateSetter } from '@/views/searcher'
import corp from '@corp'

interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    date_filter_type: number | null,
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
    setDate,
    init,
    dateChanged,
    range_date,
    date,
    date_selecter,
} = DateSetter(props, formatDate, formatTime)

store.params.use_search_date_detail = Number(corp.pv_options.free.use_search_date_detail)

const handleEnterKey = (event: KeyboardEvent) => {
    if (event.keyCode === 13)
        store.setTable()
}

const updateSearchQuery = () => {
    const search = (document.getElementById('search') as HTMLInputElement).value
    //store.updateQueryString({ search: search })
}

onMounted(() => {
    init(store.params)
    watchEffect(() => {
        store.setChartProcess()
        dateChanged(store)
    })
})

</script>
<template>
    <div class="d-inline-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12">
            <VCardText>
                <VRow>
                    <div class="d-inline-flex align-center flex-wrap gap-4 float-left justify-center">
                        <div class="d-inline-flex align-center flex-wrap gap-4 float-left justify-center">
                            <template v-if="corp.pv_options.free.use_search_date_detail">
                                <div class="d-inline-flex">
                                    <template v-if="props.date_filter_type == DateFilters.DATE_RANGE">
                                        <VueDatePicker v-model="range_date" :enable-seconds="true"
                                            :text-input="{ format: 'yyyy-MM-dd HH:mm:ss' }" locale="ko" :format-locale="ko"
                                            range multi-calendars :dark="theme === 'dark'" autocomplete="on" utc
                                            :format="getRangeFormat" :teleport="true" input-class-name="search-input"
                                            @update:modelValue="date_selecter = null" select-text="Search"
                                            time-picker-inline />
                                    </template>
                                    <template v-else-if="props.date_filter_type == DateFilters.SETTLE_RANGE">
                                        <VueDatePicker v-model="range_date" :text-input="{ format: 'yyyy-MM-dd' }"
                                            locale="ko" :format-locale="ko" range multi-calendars :dark="theme === 'dark'"
                                            autocomplete="on" utc :format="getRangeFormat" :teleport="true"
                                            input-class-name="search-input" @update:modelValue="date_selecter = null"
                                            select-text="Search" :enable-time-picker="false" />
                                    </template>
                                    <template v-else-if="props.date_filter_type == DateFilters.DATE">
                                        <VueDatePicker v-model="date" :text-input="{ format: 'yyyy-MM-dd' }" locale="ko"
                                            :format-locale="ko" :dark="theme === 'dark'" autocomplete="on" utc
                                            :format="formatDate" :teleport="true" />
                                    </template>
                                </div>
                            </template>
                            <template v-else>
                                <template
                                    v-if="props.date_filter_type == DateFilters.DATE_RANGE || props.date_filter_type == DateFilters.SETTLE_RANGE">
                                    <VTextField type="date" v-model="range_date[0]"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line />
                                    <VTextField type="date" v-model="range_date[1]"
                                        prepend-inner-icon="ic-baseline-calendar-today" label="종료일 입력" single-line />
                                </template>
                                <template v-else-if="props.date_filter_type == DateFilters.DATE">
                                    <VTextField type="date" v-model="date" prepend-inner-icon="ic-baseline-calendar-today"
                                        label="검색일 입력" single-line />
                                </template>
                            </template>
                            <template v-if="head.path === 'transactions'">
                                <VSelect v-model="date_selecter" :items="[{ id: null, title: '기간 조회' }].concat(dates)"
                                    density="compact" variant="outlined" item-title="title" item-value="id"
                                    style="min-width: 10em;" @update:modelValue="setDate()" label="기간 조회" />
                            </template>
                            <template
                                v-else-if="head.path === 'salesforces' || head.path === 'transactions/settle/salesforces' || head.path === 'transactions/settle-histories/salesforces'">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                                    style="min-width: 10em;" :items="[{ id: null, title: '전체' }].concat(salesLevels())"
                                    :label="`조회등급`" item-title="title" item-value="id"
                                    @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                            </template>
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)">
                                엑셀 추출
                            </VBtn>
                        </div>
                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField id="search" :placeholder="props.placeholder" density="compact"
                                @keyup="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input"
                                @input="updateSearchQuery()">
                                <VTooltip activator="parent" location="top">
                                    {{ props.placeholder }}
                                </VTooltip>
                            </VTextField>
                            <VBtn prepend-icon="tabler:search" @click="store.setTable()">
                                검색
                            </VBtn>
                            <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter"
                                @click="head.filter.show()">
                                검색 필터
                            </VBtn>
                            <VBtn prepend-icon="tabler-plus" @click="store.create()" v-if="props.add">
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
