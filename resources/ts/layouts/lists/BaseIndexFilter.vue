<script setup lang="ts">
interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    is_range_date: boolean | null,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
const head  = <any>(inject('head'))
const exporter = <any>(inject('exporter'))
const formatDate = <any>(inject('$formatDate'))

if(props.is_range_date == true)
{
    const date = new Date()
    store.params.s_dt = formatDate(new Date(date.getFullYear(), date.getMonth(), 1))
    store.params.e_dt = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0))
}
else if(props.is_range_date == false)
    store.params.dt = formatDate(new Date())
else
{
    
}
</script>
<template>
    <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12" md="6">
            <VCardText style="padding: 1em;">
                <VRow>
                    <template v-if="props.is_range_date">
                        <VCol cols="12" sm="3">
                            <AppDateTimePicker v-model="store.params.s_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색 시작일" />
                        </VCol>
                        <VCol cols="12" sm="3">
                            <AppDateTimePicker v-model="store.params.e_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색 종료일" />
                        </VCol>
                    </template>
                    <template v-else>
                        <VCol cols="12" sm="3">
                            <AppDateTimePicker v-model="store.params.dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색일" />
                        </VCol>                        
                    </template>
                    <VCol cols="12" sm="6">
                        <div class="d-inline-flex align-center flex-wrap gap-4 justify-content-evenly">
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)">
                                엑셀 추출
                            </VBtn>                        
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-pdf2"
                                @click="exporter(2)">
                                PDF 추출
                            </VBtn>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCol>
        <VCol cols="12" md="6">
            <VCardText style="padding: 1em;">
                <VRow no-gutters justify="end">
                    <VCol>
                        <div class="d-inline-flex align-center flex-wrap gap-4 justify-content-evenly float-right">
                            <VTextField v-model="store.search" :placeholder="`${props.placeholder}`" density="compact"
                                prepend-inner-icon="tabler:search" class="search-input" />
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
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCol>
    </div>
</template>
<style lang="scss">
.search-input {
  min-inline-size: 13.35rem;
}

.justify-content-evenly {
  justify-content: space-evenly;
}

@media (min-width: 2399px) {
  /* xs breakpoint in Vuetify */
  .search-input {
    min-inline-size: 20.35rem;
  }
}
</style>
