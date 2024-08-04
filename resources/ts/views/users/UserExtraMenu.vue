<script setup lang="ts">
import { getUserLevel } from '@/plugins/axios'
import { BasePropertie } from '@/views/types'
import { axios } from '@axios'
import { getUserTypeName } from './useStore'

interface Props {
    item: BasePropertie,
    type: number, //0 == 가맹점, 1 == 영업자, 2 == 운영자
}

const props = defineProps<Props>()
const password = <any>(inject('password'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const isAbleUnlock = () => {
    if(props.item?.is_lock) {
        if(props.type === 0 && getUserLevel() >= 13)
            return true
        else if(getUserLevel() >= 35)
            return true
        else
            return false
    }
    else
        return false
}

const unlockAccount = async () => {
    const [name, path] = getUserTypeName(props.type)
    if (await alert.value.show(`정말 ${name}(${props.item.user_name})의 계정을 잠금해제 하시겠습니까?`)) {
        try {
            const r = await axios.post(`/api/v1/manager/${path}/${props.item.id}/unlock-account`)
            snackbar.value.show('성공하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }

    }
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="password" @click="password.show(props.item.id, type)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-lock" />
                    </template>
                    <VListItemTitle>패스워드변경</VListItemTitle>
                </VListItem>
                <VListItem value="unlockAccount" @click="unlockAccount()" v-if="isAbleUnlock()">
                        <template #prepend>
                            <VIcon size="24" class="me-3" icon="tabler-lock" />
                        </template>
                        <VListItemTitle>계정잠금해제</VListItemTitle>
                </VListItem>                
            </VList>
        </VMenu>
    </VBtn>
</template>

