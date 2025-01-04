<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { shoppingMallStore } from '@/views/shop/shoppingMallStore';

const route = useRoute()
const store = shoppingMallStore()

</script>
<template>
    <VCardText class="product-list-wrapper">
        <template v-if="store.is_skeleton">
            <VCard v-for="(product, _index) in 10" class="product-wrapper" :key="`product-${_index}`" >
                <VRow>
                    <VCol md="3" cols="3">
                        <SkeletonBox :borderRadius="'1em'" :width="'140px'" :height="'90px'" :style="'margin: 0.5em;'"/>
                    </VCol>
                    <VCol md="9" cols="9">
                        <VCol cols="12">
                            <SkeletonBox :width="'20em'" :height="'1em'"/>
                        </VCol>
                        <VCol cols="12">
                            <span class="product-amount">
                                <b>
                                    <SkeletonBox :width="'6em'" :height="'1em'"/>
                                </b>원
                            </span>
                        </VCol>
                    </VCol>
                </VRow>
            </VCard>
        </template>
        <template v-else>
            <VWindow v-model="store.caetegory_tab" :touch="false" v-if="store.main_tab === 0">
                <VWindowItem v-for="(category, index) in store.categories" :key="`category-window-${index}`">
                    <template v-if="store.filterProducts.length">
                        <VCard v-for="(product, _index) in store.filterProducts" class="product-wrapper" :key="`product-${_index}`" >
                            <VRow @click="store.moveProductDetail(route.params.shop_window, product.id)">
                                <VCol md="3" cols="3">
                                    <VImg rounded :src="product.product_img" class="product-img"/>
                                </VCol>
                                <VCol md="9" cols="9">
                                    <VCol cols="12">
                                        <span class="product-name">{{ product.product_name }}</span>                                    
                                    </VCol>
                                    <VCol cols="12">
                                        <span class="product-amount">
                                            <b>{{ product.product_amount.toLocaleString() }}</b>원
                                        </span>
                                    </VCol>
                                </VCol>
                            </VRow>
                        </VCard>
                    </template>
                    <VRow v-else style="text-align: center;">
                        <VCol md="12" cols="12">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <span>
                                    상품정보가 없습니다.
                                </span>
                                <VIcon size="24" icon='line-md:emoji-frown-twotone' style="margin-left: 0.25em;" color="error"/>
                            </div>
                        </VCol>
                    </VRow>
                </VWindowItem>
            </VWindow>
        </template>
    </VCardText>
</template>
<style scoped>
.product-list-wrapper {
  inline-size: 100%;
  max-inline-size: 700px;
  padding-block: 24px;
  padding-inline: 0;
}

.product-wrapper {
  margin-block-end: 1em;
}

.product-wrapper:hover {
  border-width: 2px;
  cursor: pointer;
}

.product-img {
  border: 1px solid rgb(var(--v-border-color), var(--v-border-opacity));
  margin: 0.5em;
  block-size: 90px;
  inline-size: 140px;
}

</style>
