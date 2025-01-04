<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import ProductOptionGroupTr from '@/views/shop/product-option-groups/ProductOptionGroupTr.vue';
import { ProductOptionGroup } from '@/views/types';

interface Props {
    option_groups: ProductOptionGroup[],
    product_id: number,
}
const props = withDefaults(defineProps<Props>(), {})
const { setNullRemove } = useRequestStore()
const productOptionGroupAdd = async () => {
    props.option_groups.push({
        id: 0,
        product_id: props.product_id,
        group_name: '',
        is_able_duplicate: 0,
        is_able_count: 0,
        product_options: [],
    })
}
watchEffect(() => {
    setNullRemove(props.option_groups)
})
</script>
<template>
    <VCardSubtitle style="display: flex; align-items: center; justify-content: space-between;">
        <span>옵션그룹</span>
        <VBtn type="button" size="small" color="info" :disabled="props.product_id ? false : true"
            @click="productOptionGroupAdd()">
            <VIcon icon="tabler:code-variable-plus" />
            {{ props.product_id ? "옵션그룹추가" : "상품정보를 먼저 추가해주세요." }}
        </VBtn>
    </VCardSubtitle>
    <br>
    <VTable style="margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" style="width: 30%; text-align: center;">상품그룹명</th>
                <th scope="col" style="width: 20%; text-align: center;">수량선택가능</th>
                <th scope="col" style="width: 20%; text-align: center;">중복추가가능</th>
                <th scope="col" style="width: 20%; text-align: center;">옵션관리</th>
                <th scope="col" style="width: 30%; text-align: center;">수정/삭제</th>
            </tr>
        </thead>
        <tbody>
            <ProductOptionGroupTr v-for="(option_group, index) in props.option_groups"
                :key="index" :option_group="option_group"/>
        </tbody>
        <tfoot v-if="Boolean(props.option_groups?.length === 0)">
            <tr>
                <td colspan="5" class="text-center">
                    옵션그룹이 존재하지 않습니다.
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
