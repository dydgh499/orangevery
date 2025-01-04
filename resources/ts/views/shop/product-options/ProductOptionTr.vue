<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import { ProductOption } from '@/views/types';
import { VForm } from 'vuetify/components';

interface Props {
    product_option: ProductOption,
}
const props = withDefaults(defineProps<Props>(), {})
const vForm = ref<VForm>()
const store = <any>(inject('store'))
const { formRequest, remove } = useRequestStore()

const productOptionUpdate = async () => {
    await formRequest('/merchandises/shopping-mall/product-options', props.product_option, vForm.value, false)
    store.setTable()
}

const productOptionDelete = async () => {
    await remove('/merchandises/shopping-mall/product-options', props.product_option, false)
    store.setTable()
}

</script>
<template>
    <tr>
        <td style="width: 50%;">
            <VForm ref="vForm">
                <VTextField 
                    v-model="product_option.option_name"
                    variant="underlined"
                    placeholder="옵션명"
                />
            </VForm>
        </td>
        <td style="width: 20%;">
            <VTextField 
                v-model="product_option.option_price"
                variant="underlined"
                placeholder="옵션가격"
            />
        </td>
        <td style="width: 30%;">
            <VBtn type="button" size="small" @click="productOptionUpdate()">
                {{ props.product_option.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-pencil" />
            </VBtn>
            <VBtn type="button" size="small" style="margin-left: 1em;" color="error" v-if="props.product_option.id" @click="productOptionDelete()">
                삭제
                <VIcon end icon="tabler-trash" />
            </VBtn>
            <VBtn type="button" size="small" style="margin-left: 1em;" color="warning" v-else @click="props.product_option.id = -1">
                제거
                <VIcon end icon="tabler-trash" />
            </VBtn>
        </td>
    </tr>
</template>
