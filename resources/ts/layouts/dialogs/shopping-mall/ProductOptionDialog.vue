<script lang="ts" setup>
import ProductOptionCard from '@/views/shop/product-options/ProductOptionCard.vue';
import { ProductOptionGroup } from '@/views/types';

const product_group = ref(<ProductOptionGroup>{})
const visible = ref(false)

const show = (_product_group: ProductOptionGroup) => {
    product_group.value = _product_group
    visible.value = true
}

defineExpose({
    show
});

</script>
<template>
        <VDialog v-model="visible" persistent max-width="800">
            <DialogCloseBtn @click="visible = false" />
            <VCard>
                <VCardItem>
                    <ProductOptionCard :product_options="product_group.product_options" :group_id="product_group.id"/>
                </VCardItem>
            </VCard>
        </VDialog>
</template>
