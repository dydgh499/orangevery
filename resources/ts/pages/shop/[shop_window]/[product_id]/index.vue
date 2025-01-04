<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import router from '@/router';
import { shoppingMallStore } from '@/views/shop/shoppingMallStore';

const route = useRoute()
const store = shoppingMallStore()

const is_skeleton = ref(true)
const product = ref(<any>({ product_option_groups: [] }))

const movePayView = async () => {
    router.push(`${route.fullPath}/${product.value.window_code}`)
}

onMounted(async () => {
    const [_code, _message, _data] = await store.getShoppingMallProduct(route.params.product_id)
    product.value = _data
    is_skeleton.value = false
})
</script>
<template>
    <VCardText class="product-list-wrapper">
        <VCard>
            <VRow no-gutters
                style="align-items: flex-start; border-block-end: 1px solid rgb(var(--v-border-color), var(--v-border-opacity));">
                <VCol md="6" cols="12">
                    <SkeletonBox v-if="is_skeleton" :width="'100%'" :height="'20em'" />
                    <VImg v-else :src="product.product_img" :style="'width:100%; height: 20em;'" />
                </VCol>
                <VCol md="6" cols="12"
                    :class="`product-sub-info-wrapper-${$vuetify.display.smAndDown ? 'mobile' : 'pc'}`">
                    <div class="product-sub-info" style="margin-top: 1em;">
                        <SkeletonBox v-if="is_skeleton" :width="'100%'" :height="'2em'" />
                        <h3 v-else>{{ product.product_name }}</h3>
                        <VDivider style="margin-top: 1em;" />
                    </div>

                    <div v-if="is_skeleton" 
                        class="product-sub-info"
                        v-for="(index) in 2"
                    >
                        <VRow no-gutters style="align-items: center;">
                                <VCol md="12" cols="12">
                                    <SkeletonBox :width="'5em'" :height="'1.5em'" />
                                </VCol>
                                <template v-for="(_index) in 2">
                                    <VCol md="5" cols="7">
                                        <span style="margin: 0 1em;">-</span>
                                        <SkeletonBox :width="'4em'" :height="'1em'" />
                                    </VCol>
                                    <VCol md="7" cols="5">
                                        <SkeletonBox :width="'4em'" :height="'1em'" />
                                    </VCol>
                                </template>
                            </VRow>
                            <VDivider v-if="index === 2" style="margin-top: 1em;" />
                    </div>
                    <div v-else
                        class="product-sub-info" 
                        v-for="(option_group, index) in product.product_option_groups"
                    >
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="12" cols="12">
                                <h4>
                                    {{ option_group.group_name }}
                                    <VChip color="success" style="float: inline-end;" v-if="option_group.is_able_duplicate">
                                        중복선택가능
                                    </VChip>
                                </h4>
                            </VCol>
                            <template v-for="(option, index) in option_group.product_options">
                                <VCol md="5" cols="7">
                                    <span style="margin: 0 1em;">-</span>
                                    <span>{{ option.option_name }}</span>
                                </VCol>
                                <VCol md="7" cols="5">
                                    <span>{{ option.option_price.toLocaleString() }}원</span>
                                </VCol>
                            </template>
                        </VRow>
                        <VDivider v-if="product.product_option_groups.length -1 === index" style="margin-top: 1em;" />
                    </div>


                    <div class="product-sub-info product-pay-warpper">
                        <span style="margin: 0 1em;">
                            <SkeletonBox v-if="is_skeleton" :width="'5em'" :height="'1.5em'" />
                            <span v-else>
                                <h3 style="display: inline-block;">{{ product.product_amount.toLocaleString() }}</h3>
                            </span>원
                        </span>
                        <VBtn @click="movePayView()" size="small">
                            <VIcon icon="tabler:credit-card-pay" />결제하기
                        </VBtn>
                    </div>
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <VCardSubtitle>
                        <h4>상품설명</h4>
                    </VCardSubtitle>
                    <br>
                    <div v-if="is_skeleton" class="ql-editor">
                        <SkeletonBox :width="'20em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'10em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'12em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'15em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'10em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'17em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'20em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                        <SkeletonBox :width="'15em'" :height="'1.5em'" style="margin-bottom: 0.5em;" />
                        <br>
                    </div>
                    <div v-else v-html="product.content" class="ql-editor">
                    </div>
                </VCol>
            </VRow>
        </VCard>
    </VCardText>
</template>
<style scoped>
.ql-editor {
  box-sizing: border-box;
  border: 1px solid white;
  block-size: 100%;
  line-height: 1.42;
  outline: none;
  overflow-y: auto;
  padding-block: 12px;
  padding-inline: 15px;
  tab-size: 4;
  text-align: start;
  white-space: pre-wrap;
  word-wrap: break-word;
}

:deep(.ql-editor img) {
  inline-size: 100% !important;
}

.product-option-wrapper {
  display: flex;
  margin-inline-start: 1em;
}
</style>
