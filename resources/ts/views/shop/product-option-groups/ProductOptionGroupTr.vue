<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import { ProductOptionGroup } from '@/views/types';
import { VForm } from 'vuetify/components';

interface Props {
    option_group: ProductOptionGroup,
}
const vForm = ref<VForm>()
const props = withDefaults(defineProps<Props>(), {})
const productOptionDialog = <any>inject('productOptionDialog')

const { formRequest, remove } = useRequestStore()

const productOptionGroupUpdate = async () => {
    await formRequest('/merchandises/shopping-mall/product-option-groups', props.option_group, vForm.value, false)
}

const productOptionGroupDelete = async () => {
    await remove('/merchandises/shopping-mall/product-option-groups', props.option_group, false)
}

</script>
<template>
    <tr>
        <td style="width: 30%;">
            <VForm ref="vForm">
                <VTextField 
                    v-model="props.option_group.group_name"
                    variant="underlined"
                    prepend-icon="tabler:category"
                    label="상품그룹명"
                    />
            </VForm>
        </td>
        <td style="width: 20%;">
            <div style="display: flex; justify-content: center;">
                <VSwitch hide-details 
                    :false-value=0 :true-value=1 
                    v-model="props.option_group.is_able_count"
                    color="primary"
                />
            </div>
        </td>
        <td style="width: 20%;">
            <div style="display: flex; justify-content: center;">
                <VSwitch hide-details 
                    :false-value=0 :true-value=1 
                    v-model="props.option_group.is_able_duplicate"
                    color="primary"
                />
            </div>
        </td>
        <td style="width: 20%;">
            <VBtn size="small" color="secondary" @click="productOptionDialog.show(props.option_group)">
                옵션관리
            </VBtn>
        </td>
        <td style="width: 30%;">
            <VBtn type="button" size="small" style="margin-left: auto;" @click="productOptionGroupUpdate()">
                {{ props.option_group.id == 0 ? "추가" : "수정" }}
                <VIcon end icon="tabler-pencil" />
            </VBtn>
            <VBtn type="button" size="small" color="error" style="margin-top: 0.25em;" v-if="props.option_group.id" @click="productOptionGroupDelete()">
                삭제
                <VIcon end icon="tabler-trash" />
            </VBtn>
            <VBtn type="button" size="small" color="warning" style="margin-top: 0.25em;" v-else @click="props.option_group.id = -1">
                제거
                <VIcon end icon="tabler-trash" />
            </VBtn>
        </td>
    </tr>
</template>
