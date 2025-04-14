<script setup lang="ts">


const visible = ref(false)
const errors = ref(<any>([]))
const search = ref('')

const item_per_page = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const headers = [
    { title: '에러코드', key: 'code' },
    { title: '에러 메세지', key: 'message' },
]
const show = (_errors: any) => {
    page.value = 1
    visible.value = true
    errors.value = _errors
}
const updateOptions = (options: any) => {
    page.value = options.page
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="500">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="">
            <VCardText class="d-flex flex-wrap py-4 gap-4">
                <VCardTitle>출금 에러코드</VCardTitle>
                <div class="app-user-search-filter d-flex flex-wrap gap-4" style="margin-left: auto;">
                    <div style="inline-size: 15rem;">
                        <AppTextField
                            v-model="search"
                            placeholder="검색"
                            variant="underlined"
                            density="compact"
                            prepend-inner-icon="tabler:search"
                        >
                        </AppTextField>
                    </div>
                </div>
                
                <VDivider/>
                <VDataTable v-model:items-per-page="item_per_page" v-model:page="page" :items="errors"
                    :items-length="errors.length" :headers="headers" class="text-no-wrap"
                    :search="search"
                    @update:options="updateOptions">
                    <template v-slot:items="props">
                        <td><b>{{ props.item.code }}</b></td>
                        <td>{{ props.item.message }}</td>
                    </template>
                </VDataTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
