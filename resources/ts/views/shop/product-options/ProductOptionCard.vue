<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import ProductOptionTr from '@/views/shop/product-options/ProductOptionTr.vue';
import { ProductOption } from '@/views/types';

interface Props {
    product_options: ProductOption[],
    group_id: number,
}
const props = withDefaults(defineProps<Props>(), {})
const { setNullRemove } = useRequestStore()
const productOptionGroupAdd = async () => {
    props.product_options.push({
        id: 0,
        group_id: props.group_id,
        option_name: '',
        option_price: 0,
    })
}
watchEffect(() => {
    setNullRemove(props.product_options)
})
</script>
<template>
    <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
        <span>옵션관리</span>
        <VBtn type="button" size="small" color="info" :disabled="props.group_id ? false : true"
            @click="productOptionGroupAdd()">
            <VIcon icon="tabler:code-variable-plus" />
            {{ props.group_id ? "옵션추가" : "옵션그룹을 먼저 추가해주세요." }}
            
        </VBtn>
    </VCardSubtitle>
    <br>
    <VTable style="margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style=" width: 50%;text-align: center;">옵션명</th>
                <th scope="col" style=" width: 20%;text-align: center;">옵션가</th>
                <th scope="col" style=" width: 30%;text-align: center;">수정/삭제</th>
            </tr>
        </thead>
        <tbody>
            <ProductOptionTr v-for="(product_option, index) in props.product_options"
                :key="index" :product_option="product_option"/>
        </tbody>
        <tfoot v-if="Boolean(props.product_options?.length === 0)">
            <tr>
                <td colspan="3" class="text-center">
                    옵션이 존재하지 않습니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
