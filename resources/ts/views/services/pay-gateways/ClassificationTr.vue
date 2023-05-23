<script setup lang="ts">
import { axios } from '@axios';
import { VForm } from 'vuetify/components';
import type { Classification } from '@/views/types'
import { requiredValidator } from '@validators';

interface Props {
    item: Classification,
    index: number,
}
const props = defineProps<Props>()
const vForm = ref<VForm>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const update = async () => {
    const is_valid = await vForm.value?.validate();
    let up_type = props.item.id != 0 ? '수정' : '생성';

    if (is_valid?.valid && await alert.value.show('정말 ' + up_type + '하시겠습니까?')) {
        let url = '/api/v1/classifications'
        url += props.item.id ? "/" + props.item.id : ""
        axios.post(url, props.item)
            .then(r => { snackbar.value.show('성공하였습니다', 'primary') })
            .catch(e => { snackbar.value.show(e.response.data.message, 'error') })
    }
}
const remove = async () => {
    if (await alert.value.show('정말 삭제하시겠습니까?')) {
        let url = '/api/v1/classifications/' + props.item.id
        axios.delete(url)
            .then(r => { snackbar.value.show('성공하였습니다', 'primary') })
            .catch(e => { snackbar.value.show(e.response.data.message, 'error') })
    }
}
</script>
<template>
    <tr scope="col">
        <td style="width: 10%;">{{ index + 1 }}</td>
        <td style="width: 40%;">
            <VForm ref='vForm'>
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.name" prepend-inner-icon="mdi-vector-intersection"
                            placeholder="구간명 입력" persistent-placeholder :rules="[requiredValidator]"
                            style="display: inline-block;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td style="width: 40%;">
            <VForm ref='vForm'>
                <VCol cols="12">
                    <VRow no-gutters>
                        <VTextField v-model="props.item.trx_fee" prepend-inner-icon="tabler-currency-won"
                            placeholder="수수료 입력" persistent-placeholder :rules="[requiredValidator]"
                            style="display: inline-block;" />
                    </VRow>
                </VCol>
            </VForm>
        </td>
        <td class="text-center" style="width: 30%;" v-show="Boolean(props.item.id != 0)">
            <VCol class="d-flex gap-4">
                <VBtn icon size="x-small" color="default" variant="text" @click="update()">
                    수정
                    <VIcon size="22" icon="tabler-checkbox" />
                </VBtn>

                <VBtn icon size="x-small" color="default" variant="text">
                    삭제
                    <VIcon size="22" icon="tabler-trash" @click="remove()" />
                </VBtn>
            </VCol>
        </td>
        <td class="text-center" style="width: 30%;" v-show="Boolean(props.item.id == 0)">
            <VBtn icon size="x-small" color="default" variant="text" @click="update()">
                추가
                <VIcon size="22" icon="tabler-square-plus" />
            </VBtn>
        </td>
    </tr>
</template>
