<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { ko } from 'date-fns/locale';

interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    is_range_date: boolean | null,
}
const props = defineProps<Props>()
const store = <any>(inject('store'))
const head = <any>(inject('head'))
const exporter = <any>(inject('exporter'))
const formatDate = <any>(inject('$formatDate'))
const formatTime = <any>(inject('$formatTime'))


const { theme } = useThemeConfig()
const range_date = ref(<string[]>(['', '']))
const date = ref(<string>(''));

const handleEnterKey = (event: KeyboardEvent) => {
    if (event.keyCode === 13)
        store.setTable()
}
const getRangeFormat = (dates: Date[]) => {
    const s_date = formatDate(dates[0]) + " " + formatTime(dates[0])
    const e_date = dates.length == 2 ? formatDate(dates[1]) + " " + formatTime(dates[1]) : ""
    return s_date + "  -  " + e_date
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
    range_date.value[0] = formatDate(s_date) + " " + formatTime(s_date) 
    range_date.value[1] = formatDate(e_date) + " " + formatTime(e_date) 
}

if (props.is_range_date == true)
{
    const date = new Date();
    const s_date = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0)
    const e_date = new Date(date.getFullYear(), date.getMonth()+1, 0, 23, 59, 59)
    range_date.value[0] = formatDate(s_date) + " " + formatTime(s_date) 
    range_date.value[1] = formatDate(e_date) + " " + formatTime(e_date) 
}
else if (props.is_range_date == false)
    date.value = formatDate(new Date())

watchEffect(() => {
    store.setChartProcess()
    if (props.is_range_date == true) {
        const s_date = new Date(range_date.value[0])
        const e_date = new Date(range_date.value[1])
        store.params.s_dt = formatDate(s_date) + " " + formatTime(s_date) 
        store.params.e_dt = formatDate(e_date) + " " + formatTime(e_date) 
    }
    else if (props.is_range_date == false) {
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
                            <div class="d-inline-flex search-input">
                                <VueDatePicker v-model="range_date" v-if="props.is_range_date == true"
                                    :action-row="{ showNow: true }" :enable-seconds="true"
                                    :text-input="{ format: 'yyyy-MM-dd HH:mm:ss' }" locale="ko" :format-locale="ko" range
                                    multi-calendars :dark="theme === 'dark'" autocomplete="on" utc :format="getRangeFormat"
                                    :teleport="true" />
                                <VueDatePicker v-model="date" v-if="props.is_range_date == false"
                                    :text-input="{ format: 'yyyy-MM-dd' }" locale="ko" :format-locale="ko"
                                    :dark="theme === 'dark'" autocomplete="on" utc :format="formatDate" :teleport="true" />
                            </div>
                            <template v-if="head.path === 'transactions'">
                                <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today"
                                    @click="setDateRange('today')">
                                    당일
                                </VBtn>
                                <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today"
                                    @click="setDateRange('1 day')">
                                    어제
                                </VBtn>
                                <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today"
                                    @click="setDateRange('3 day')">
                                    3일전
                                </VBtn>
                                <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today"
                                    @click="setDateRange('1 mon')">
                                    1개월
                                </VBtn>
                                <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today"
                                    @click="setDateRange('3 mon')">
                                    3개월
                                </VBtn>
                            </template>
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)">
                                엑셀 추출
                            </VBtn>
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-pdf2"
                                @click="exporter(2)">
                                PDF 추출
                            </VBtn>
                        </div>

                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField id="search" :placeholder="props.placeholder" density="compact"
                                @keyup.enter="handleEnterKey" prepend-inner-icon="tabler:search" class="search-input"
                                style="flex-grow: 3;">
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
