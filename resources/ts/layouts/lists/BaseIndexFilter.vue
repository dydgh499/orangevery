<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { ko } from 'date-fns/locale'
import { DateFilters } from '@core/enums'
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
const date_selecter = ref()

const { theme } = useThemeConfig()
const range_date = ref(<string[]>(['', '']))
const date = ref(<string>(''))
const dates = [
    { id: 'today', title: '당일' },
    { id: '1 day', title: '어제' },
    { id: '3 day', title: '3일전' },
    { id: '1 mon', title: '1개월' },
    { id: '3 mon', title: '3개월' },
]

store.params.use_search_date_detail = Number(corp.pv_options.free.use_search_date_detail)

const handleEnterKey = (event: KeyboardEvent) => {
    if (event.keyCode === 13)
        store.setTable()
}
const getDateFormat = (date: Date) => {
    if (corp.pv_options.free.use_search_date_detail && props.date_filter_type == DateFilters.DATE_RANGE)
        return formatDate(date) + " " + formatTime(date)
    else
        return formatDate(date)
}
const getRangeFormat = (dates: Date[]) => {
    if (props.date_filter_type == DateFilters.DATE_RANGE) {
        const setRangeFormat = (date: Date) => {
            if (formatTime(date) === "00:00:00" || formatTime(date) === "23:59:59")
                return formatDate(date)
            else
                return formatDate(date) + " " + formatTime(date)
        }
        const s_date = setRangeFormat(dates[0])
        const e_date = setRangeFormat(dates[1])
        return s_date + "  -  " + e_date
    }
    else if(props.date_filter_type == DateFilters.SETTLE_RANGE) {
        
        return formatDate(dates[0]) + "  -  " + formatDate(dates[1])
    }
}
const setDate = () => {
    if (date_selecter.value) {
        setDateRange(date_selecter.value)
    }
}
const setDateRange = (type: string) => {
    let s_date = undefined
    let e_date = undefined
    const date = new Date();
    if (type == 'today') {
        s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0);
        e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59);
    }
    else if (type == '1 day') {
        s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 0, 0, 0);
        e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 23, 59, 59);
    }
    else if (type == '3 day') {
        s_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 3, 0, 0, 0);
        e_date = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1, 23, 59, 59);
    }
    else if (type == '1 mon') {
        s_date = new Date(date.getFullYear(), date.getMonth() - 1, 1, 0, 0, 0)
        e_date = new Date(date.getFullYear(), date.getMonth(), 0, 23, 59, 59)
    }
    else if (type == '3 mon') {
        s_date = new Date(date.getFullYear(), date.getMonth() - 3, 1, 0, 0, 0);
        e_date = new Date(date.getFullYear(), date.getMonth(), 0, 23, 59, 59);
    }
    else {
        s_date = new Date()
        e_date = new Date()
    }
    range_date.value[0] = getDateFormat(s_date)
    range_date.value[1] = getDateFormat(e_date)
}

if (props.date_filter_type == DateFilters.DATE_RANGE) {
    const date = new Date()
    const s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
    const e_date = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59)
    range_date.value[0] = getDateFormat(s_date)
    range_date.value[1] = getDateFormat(e_date)
}
else if (props.date_filter_type == DateFilters.SETTLE_RANGE) {
    const date = new Date()
    const s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
    const e_date = date
    range_date.value[0] = getDateFormat(s_date)
    range_date.value[1] = getDateFormat(e_date)
}
else if (props.date_filter_type == DateFilters.DATE)
    date.value = formatDate(new Date())

watchEffect(() => {
    store.setChartProcess()
    if (props.date_filter_type == DateFilters.DATE_RANGE || props.date_filter_type == DateFilters.SETTLE_RANGE) {
        const s_date = new Date(range_date.value[0])
        const e_date = new Date(range_date.value[1])
        store.params.s_dt = getDateFormat(s_date)
        store.params.e_dt = getDateFormat(e_date)
    }
    else if (props.date_filter_type == DateFilters.DATE) {
        const dt = new Date(date.value)
        store.params.dt = formatDate(dt)
    }
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
                                            @update:modelValue="date_selecter = null" select-text="Search" time-picker-inline/>
                                    </template>
                                    <template v-else-if="props.date_filter_type == DateFilters.SETTLE_RANGE">
                                        <VueDatePicker v-model="range_date"
                                            :text-input="{ format: 'yyyy-MM-dd' }" locale="ko" :format-locale="ko" range
                                            multi-calendars :dark="theme === 'dark'" autocomplete="on" utc
                                            :format="getRangeFormat" :teleport="true" input-class-name="search-input"
                                            @update:modelValue="date_selecter = null" select-text="Search" :enable-time-picker="false"/>
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
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)">
                                엑셀 추출
                            </VBtn>
                        </div>

                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField id="search" :placeholder="props.placeholder" density="compact"
                                @keyup.enter="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input">
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
