<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import ProductOptionDialog from '@/layouts/dialogs/shopping-mall/ProductOptionDialog.vue';
import Editor from '@/layouts/utils/Editor.vue';
import Preview from '@/layouts/utils/Preview.vue';
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore';
import { useCategoryStore } from '@/views/merchandises/shopping-mall/categories/useStore';
import { useRequestStore } from '@/views/request';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import ProductOptionGroupCard from '@/views/shop/product-option-groups/ProductOptionGroupCard.vue';
import type { PayModule, Product } from '@/views/types';
import { getUserLevel, user_info } from '@axios';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

const vForm = ref<VForm>()
const product = ref(<Product>{})
const visible = ref(false)
const is_add = ref(false)
const store = <any>(inject('store'))

const mcht_id = ref()

const files = ref(<File[]>([]))
const ext = ref<string>('')
const preview_style = `
    border: 1px solid rgb(130, 130, 130);
    border-radius: 0.5em;
    margin: 0.5em;
    height: 160px;
`;

const productOptionDialog = ref()
const { mchts } = useSalesFilterStore()
const pay_modules = ref<PayModule[]>([])
const { categories } = useCategoryStore()
const { formRequest, remove } = useRequestStore()
const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()

provide('productOptionDialog', productOptionDialog)

const show = (_product: Product, _is_add: boolean) => {
    mcht_id.value = null
    if (_product.category_id)
        mcht_id.value = categories.find(item => item.id === _product.category_id)?.mcht_id
    if (_product.product_amount) {
        amount_format.value = _product.product_amount.toString()
        amount.value = _product.product_amount
    }
    product.value = _product
    is_add.value = _is_add
    visible.value = true
}

const getFileExtension = (file_name: string) => {
    if (file_name.length > 0) {
        const file_names = file_name.split('?')
        const dot = file_names[0].lastIndexOf('.') + 1
        return file_names[0].substring(dot, file_names[0].length).toLowerCase()
    }
    else
        return 'png'
}

const upload = () => {
    if (files.value.length) {
        ext.value = getFileExtension(files.value[0].name)
        product.value.product_file = files.value ? files.value[0] : files.value
        product.value.product_img = URL.createObjectURL(files.value[0])
    }
}

const productUpdate = async () => {
    await formRequest('/merchandises/shopping-mall/products', product.value, vForm.value, false)
    store.setTable()
}

const productDelete = async () => {
    await remove('/merchandises/shopping-mall/products', product.value, false)
    store.setTable()
}

const filterPayModule = computed(() => {
    return pay_modules.value.filter(item => {
        return item.mcht_id === mcht_id.value && item.module_type > 0 && item.pay_window_secure_level > 0
    })
})

const filterCategories = computed(() => {
    return categories.filter(item => { return item.mcht_id === mcht_id.value })
})

watchEffect(() => {
    product.value.product_amount = amount.value
})

onMounted(async () => {
    pay_modules.value = getUserLevel() === 10 ? user_info.value.online_pays : await getAllPayModules()
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = false" />
        <VCard>
            <VForm ref="vForm">
                <VCardItem>
                    <VCardTitle>상품 {{ is_add ? '추가' : '수정' }}</VCardTitle>
                    <VDivider style="margin: 1em 0;" />
                    <VCardSubtitle>상품정보</VCardSubtitle>
                    <br>
                    <VRow style="align-items: flex-start;">
                        <VCol cols="12" :md="4">
                            <VRow no-gutters>
                                <VCol>
                                    <template v-if="getUserLevel() >= 10">
                                        <VFileInput accept=".jpg,.bmp,.png,.jpeg,.webp" prepend-icon="tabler-camera"
                                            style="margin: 0.5em;" v-model="files" label="상품대표 이미지" @change="upload()">
                                            <template #selection="{ fileNames }">
                                                <template v-for="fileName in fileNames" :key="fileName">
                                                    <VChip label size="small" variant="outlined" color="primary"
                                                        class="me-2">
                                                        {{ fileName }}
                                                    </VChip>
                                                </template>
                                            </template>
                                        </VFileInput>
                                        <Preview
                                            :preview="product.product_img ? product.product_img : '/utils/icons/img-preview.svg'"
                                            :style="''" :preview-style="preview_style" class="preview" :ext="ext" />
                                    </template>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" :md="8">
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="3">
                                    <label>가맹점 선택</label>
                                </VCol>
                                <VCol cols="8" :md="9">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id" :items="mchts"
                                        variant="underlined" prepend-icon="tabler-building-store" item-title="mcht_name"
                                        item-value="id" single-line :rules="[requiredValidatorV2(mcht_id, '가맹점')]"
                                        placeholder="가맹점 선택" />
                                </VCol>
                            </VRow>
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="3">
                                    <label>결제창 모듈</label>
                                </VCol>
                                <VCol cols="8" :md="9">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="product.pmod_id"
                                        :items="filterPayModule" variant="underlined" prepend-icon="tabler:paywall"
                                        item-title="note" item-value="id" single-line
                                        :rules="[requiredValidatorV2(mcht_id, '결제창')]" placeholder="결제창 선택" />
                                </VCol>
                            </VRow>
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="3">
                                    <label>카테고리 선택</label>
                                </VCol>
                                <VCol cols="8" :md="9">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="product.category_id"
                                        :items="filterCategories" variant="underlined" prepend-icon="tabler:category"
                                        item-title="category_name" item-value="id" single-line
                                        :rules="[requiredValidatorV2(product.category_id, '카테고리')]"
                                        placeholder="카테고리 선택" />
                                </VCol>
                            </VRow>
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="3">
                                    <label>상품명</label>
                                </VCol>
                                <VCol cols="8" :md="9">
                                    <VTextField v-model="product.product_name" prepend-icon="tabler:shopping-bag"
                                        maxlength="100" variant="underlined"
                                        :rules="[requiredValidatorV2(product.product_name, '상품명')]"
                                        placeholder="상품명을 입력해주세요" />
                                </VCol>
                            </VRow>
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="3">
                                    <label>상품금액</label>
                                </VCol>
                                <VCol cols="8" :md="9">
                                    <VTextField v-model="amount_format" suffix="₩" @input="formatAmount"
                                        variant="underlined" placeholder="상품금액을 입력해주세요"
                                        prepend-icon="ic:outline-price-change"
                                        :rules="[requiredValidatorV2(product.product_amount, '상품금액')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <ProductOptionGroupCard 
                        :option_groups="product.product_option_groups" 
                        :product_id="product.id" 
                        :key="product.product_option_groups?.length"
                    />
                    <VDivider style="margin: 1em 0;" />
                    <VCardSubtitle>상품내용</VCardSubtitle>
                    <br>
                    <VRow>
                        <VCol>
                            <Editor :content="product.content" @update:content="product.content = $event" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol cols="12" class="d-flex gap-4">
                            <VBtn type="button" color="default" variant="tonal" v-if="product.id" @click="shopPreview()">
                                쇼핑몰 미리보기
                                <VIcon end icon="tabler:shopping-cart" />
                            </VBtn>
                            <VBtn type="button" style="margin-left: auto;" @click="productUpdate()">
                                {{ product.id == 0 ? "추가" : "수정" }}
                                <VIcon end icon="tabler-pencil" />
                            </VBtn>
                            <VBtn type="button" color="error" v-if="product.id" @click="productDelete()">
                                삭제
                                <VIcon end icon="tabler-trash" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VForm>
        </VCard>
    </VDialog>
    <ProductOptionDialog ref="productOptionDialog" />
</template>

<style scoped>
:deep(.v-row) {
  align-items: center;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
