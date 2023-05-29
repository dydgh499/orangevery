<script setup lang="ts">
interface Props {
    placeholder: string,
    add: boolean,
    add_name: string,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
</script>
<template>
    <VCardText>
        <VRow>
            <VCol cols="12" sm="2">
                <VSelect v-model="store.params.page_size" density="compact" variant="outlined" :items="[10, 20, 30, 50]"
                    label="표시 개수" />
            </VCol>
            <VCol cols="12" sm="2">
                <AppDateTimePicker v-model="store.params.s_dt" prepend-inner-icon="ic-baseline-calendar-today"
                    label="검색 시작일" />
            </VCol>
            <VCol cols="12" sm="2">
                <AppDateTimePicker v-model="store.params.e_dt" prepend-inner-icon="ic-baseline-calendar-today"
                    label="검색 종료일" />
            </VCol>
            <VSpacer />
            <div class="d-flex align-center flex-wrap gap-4">
                <!-- 👉 Search  -->
                <div style="width: 13.35rem;">
                    <VTextField v-model="store.params.search" :placeholder="`${props.placeholder}`" density="compact" />
                </div>

                <VBtn variant="tonal" color="secondary" prepend-icon="tabler-filter" @click="store.filter.show()">
                    검색 필터
                </VBtn>
                <!-- 👉 Export button -->
                <VBtn variant="tonal" color="secondary" prepend-icon="tabler-screen-share" @click="store.excel()">
                    엑셀 추출
                </VBtn>
                <!-- 👉 Add user button -->
                <VBtn prepend-icon="tabler-plus" @click="store.create()" v-if="props.add">{{ props.add_name }} 추가</VBtn>
            </div>
        </VRow>
    </VCardText>
</template>
