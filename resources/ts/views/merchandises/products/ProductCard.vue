<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import ProductTr from '@/views/merchandises/products/ProductCardTr.vue';
import { useRequestStore } from '@/views/request';
import type { Merchandise, Product } from '@/views/types';

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const products = reactive<Product[]>([])
const addNewProduct = () => {
    const product = <Product>({
        id: 0,
        mcht_id: props.item.id,
        product_name: '',
    })
    products.push(product)
}

watchEffect(() => {
    if(props.item.products != undefined)
        Object.assign(products, props.item.products)
})
watchEffect(() => {
    setNullRemove(products)
})
</script>
<template>
    <VCardTitle style="margin-bottom: 1em;">
        <BaseQuestionTooltip :location="'top'" :text="'상품정보 세팅'" :content="'수기단말기에 표기되는 상품정보입니다.'"/>
    </VCardTitle>
    <VTable style="width: 100%;margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style="text-align: center;">No.</th>
                <th scope="col" style="text-align: center;">상품명</th>
                <th scope="col" style="text-align: center;"></th>
            </tr>
        </thead>
        <tbody>
            <ProductTr v-for="(item, index) in products"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    가맹점을 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewProduct()">
                상품정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
  </style>
  