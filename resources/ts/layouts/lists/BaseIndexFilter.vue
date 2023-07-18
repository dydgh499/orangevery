<script setup lang="ts">
interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
    is_range_date: boolean | null,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
const head = <any>(inject('head'))
const exporter = <any>(inject('exporter'))
const formatDate = <any>(inject('$formatDate'))

if (props.is_range_date == null) {

}
else if (props.is_range_date == true) {
    const date = new Date()
    store.params.s_dt = formatDate(new Date(date.getFullYear(), date.getMonth(), 1))
    store.params.e_dt = formatDate(new Date(date.getFullYear(), date.getMonth() + 1, 0))
}
else if (props.is_range_date == false)
    store.params.dt = formatDate(new Date())
</script>
<template>
    <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12">
            <VCardText style="padding: 0.5em;">
                <VRow>
                    <template v-if="props.is_range_date == true">
                        <VCol cols="12" sm="3" md="1">
                            <AppDateTimePicker v-model="store.params.s_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색 시작일" />
                        </VCol>
                        <VCol cols="12" sm="3" md="1">
                            <AppDateTimePicker v-model="store.params.e_dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색 종료일" />
                        </VCol>
                    </template>
                    <template v-else-if="props.is_range_date == false">
                        <VCol cols="12" sm="3" md="2">
                            <AppDateTimePicker v-model="store.params.dt" prepend-inner-icon="ic-baseline-calendar-today"
                                label="검색일" />
                        </VCol>
                    </template>
                    <VCol cols="12" sm="3" md="2" class="export-container">
                        <div class="d-inline-flex align-center gap-4 justify-content-evenly">
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-excel"
                                @click="exporter(1)" style="flex-grow: 1;">
                                엑셀 추출
                            </VBtn>
                            <VBtn variant="tonal" color="secondary" prepend-icon="vscode-icons:file-type-pdf2"
                                @click="exporter(2)" style="flex-grow: 1;">
                                PDF 추출
                            </VBtn>
                        </div>
                    </VCol>
                    <VCol>
                        <div class="d-inline-flex align-center flex-wrap gap-4 float-right justify-center">
                            <VTextField v-model="store.search" :placeholder="`${props.placeholder}`" density="compact"
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
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCol>
    </div>
</template>
<style lang="scss" scoped>
.search-input {
  min-inline-size: 17.35rem;
}

@media (min-width: 2399px) {
  /* xs breakpoint in Vuetify */
  .search-input {
    min-inline-size: 20.35rem;
  }
}

@media (max-width: 1500px) {
  /* xs breakpoint in Vuetify */
  .search-input {
    min-inline-size: 10.35rem;
  }
}

@media (max-width: 960px) {
  /* xs breakpoint in Vuetify */

  .export-container {
    text-align: center;
  }
}
</style>
