<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import type { OperatorIp } from '@/views/types';
import { VCol, VForm } from 'vuetify/components';
import { operatorActionAuthStore } from '../operators/useStore';

interface Props {
    item: OperatorIp,
    index: number,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const { headOfficeAuthValidate } = operatorActionAuthStore()

const operatorIpupdate = async () => {
    const [result, token] = await headOfficeAuthValidate('휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        props.item.token = token
        await update('/services/operator-ips', props.item, vForm.value, false)
    }
}

const operatorIpDelete = async() => {
    const [result, token] = await headOfficeAuthValidate('휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        props.item.token = token
        await remove('/services/operator-ips', props.item, false)
    }
}
</script>
<template>
    <tr>
        <td class='list-square'>{{ index + 1 }}</td>
        <td class='list-square'>
            <VForm ref="vForm">
                <VCol class="d-flex" style="text-align: center;">
                    <VTextField v-model="props.item.enable_ip" type="text" placeholder="123.123.123.123" style="width: 200px;" 
                        prepend-inner-icon="material-symbols:bring-your-own-ip" />
                </VCol>
            </VForm>
        </td>
        <td class='list-square'>
            <VCol class="d-flex" style="text-align: center;">
                <VBtn type="button" color="default" variant="text"
                    @click="operatorIpupdate()">
                    {{ props.item.id == 0 ? "추가" : "수정" }}
                    <VIcon end icon="tabler-pencil" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-if="props.item.id"
                    @click="operatorIpDelete()">
                    삭제
                    <VIcon end icon="tabler-trash" />
                </VBtn>
                <VBtn type="button" color="default" variant="text" v-else @click="props.item.id = -1">
                    입력란 제거
                    <VIcon end icon="tabler-trash" />
                </VBtn>
            </VCol>
        </td>
    </tr>
</template>
