<script setup lang="ts">
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

const use_date_button = ref(head.path === 'transactions')
if (props.is_range_date == null) {

}
else if (props.is_range_date == true) {
    const date = new Date()
    store.params.s_dt = formatDate(new Date(date.getFullYear(), date.getMonth(), 1))
    store.params.e_dt = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0))
}
else if (props.is_range_date == false)
    store.params.dt = formatDate(new Date())

const handleEnterKey = (event: KeyboardEvent) => {
      if (event.keyCode === 13) 
        store.setTable()
}

const setDateRange = (type: string) => {
    var s_date = undefined
    var e_date = undefined
    var date = new Date();
    if(type == 'today')
    {
        s_date  = date;
        e_date  = date;
    }
    else if(type == '1 day')
    {
        s_date  = new Date(date.setDate(date.getDate() - 1));
        e_date  = s_date;
    }
    else if(type == '3 day')
    {
        e_date  = new Date(date.setDate(date.getDate() - 1));
        s_date  = new Date(date.setDate(date.getDate() - 2));
    }
    else if(type == '1 mon')
    {
        s_date  = new Date(date.getFullYear(), date.getMonth() - 1, 1);
        e_date  = new Date(date.getFullYear(), date.getMonth(), 0);
    }
    else if(type == '3 mon')
    {
        s_date  = new Date(date.getFullYear(), date.getMonth() - 3, 1);
        e_date  = new Date(date.getFullYear(), date.getMonth(), 0);
    }
    store.params.s_dt = formatDate(s_date)
    store.params.e_dt = formatDate(e_date)
}

watchEffect(() => {    
    store.setChartProcess()
    store.params.s_dt = store.params.s_dt
    store.params.e_dt = store.params.e_dt
    store.params.dt = store.params.dt
})
</script>
<template>
    <div class="d-inline-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12">
            <VCardText>
                <VRow>
                    <div class="d-flex align-center flex-wrap gap-4 justify-center" style="width: 100%;">
                        <VTextField type="date" v-model="store.params.s_dt" prepend-inner-icon="ic-baseline-calendar-today"
                            label="검색 시작일" v-if="props.is_range_date == true" class="search-date" @onchange="store.params.page=1"/>
                        <VTextField type="date" v-model="store.params.e_dt" prepend-inner-icon="ic-baseline-calendar-today"
                            label="검색 종료일" v-if="props.is_range_date == true" class="search-date" />
                        <VTextField type="date" v-model="store.params.dt" prepend-inner-icon="ic-baseline-calendar-today"
                            label="검색일" v-if="props.is_range_date == false" class="search-date" />
                        <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today" @click="setDateRange('today')"
                            style="flex-grow: 1;" v-if="use_date_button">
                            당일
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today" @click="setDateRange('1 day')"
                            style="flex-grow: 1;" v-if="use_date_button">
                            어제
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today" @click="setDateRange('3 day')"
                            style="flex-grow: 1;" v-if="use_date_button">
                            3일전
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today" @click="setDateRange('1 mon')"
                            style="flex-grow: 1;" v-if="use_date_button">
                            1개월
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="ic-baseline-calendar-today" @click="setDateRange('3 mon')"
                            style="flex-grow: 1;" v-if="use_date_button">
                            3개월
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                            @click="exporter(1)" style="flex-grow: 1;">
                            엑셀 추출
                        </VBtn>
                        <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-pdf2"
                            @click="exporter(2)" style="flex-grow: 1;">
                            PDF 추출
                        </VBtn>
                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField id="search" :placeholder="`${props.placeholder}`" density="compact" @keyup.enter="handleEnterKey"
                                prepend-inner-icon="tabler:search" class="search-input" style="flex-grow: 3;" />
                            <VBtn prepend-icon="tabler:search" @click="store.setTable()" style="flex-grow: 1;">
                                검색
                            </VBtn>
                            <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter" @click="head.filter.show()"
                                style="flex-grow: 1;">
                                검색 필터
                            </VBtn>
                            <VBtn prepend-icon="tabler-plus" @click="store.create()" v-if="props.add" style="flex-grow: 1;">
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
<style lang="scss" scoped>
.search-input {
  min-inline-size: 20.35rem;
}

@media (max-width: 600px) {
  .search-date,
  .search-input {
    min-inline-size: 100%;
  }
}
</style>
