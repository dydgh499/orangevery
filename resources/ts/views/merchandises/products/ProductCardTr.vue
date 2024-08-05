<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import type { Product } from '@/views/types'
import { VForm } from 'vuetify/components'

interface Props {
    item: Product,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()

</script>
<template>
    <tr>
        <td style="width: 10%;">{{ index + 1 }}</td>
        <td style="width: 30%;">
            <VForm ref="vForm">
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.product_name" type="text" placeholder="상품명 입력"
                            prepend-inner-icon="icon-park-outline:ad-product" 
                            style="min-width: 10em;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 10%;">
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="default" variant="text"
                    @click="update('/merchandises/products', props.item, vForm, false)">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="remove('/merchandises/products', props.item, false)">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-else @click="props.item.id = -1">
                    입력란 제거
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
<style scoped>
td {
  padding: 0 !important;
}
</style>
