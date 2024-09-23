<script setup lang="ts">
import { getUserLevel } from '@/plugins/axios'
import { BasePropertie } from '@/views/types'
import { axios, user_info } from '@axios'
import corp from '@corp'
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
const phoneNum2FAVertifyDialog = <any>(inject('phoneNum2FAVertifyDialog'))
const initPayVerficationDialog = <any>(inject('initPayVerficationDialog'))
    
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

const isAble2FAInit = () => {
    if(getUserLevel() >= 35) {
        if(props.type === 1 && props.item.is_2fa_use)
            return true
        else if(props.type === 2 && props.item.is_2fa_use && props.item.level === 35 && getUserLevel() >= 40)
            return true            
    }
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

const init2FA = async () => {
    const [name, path] = getUserTypeName(props.type)
    if (await alert.value.show(`정말 ${name}(${props.item.user_name})의 계정의 2차인증을 초기화 하시겠습니까?`)) {
        try {
            let token = ''
            if(corp.pv_options.paid.use_head_office_withdraw) {
                const res = await axios.post('/api/v1/bonaejas/mobile-code-head-office-issuence', {
                    user_name: user_info.value.user_name
                })
                snackbar.value.show(res.data.message, 'success')
                token = await phoneNum2FAVertifyDialog.value.show(res.data.data.phone_num)                
                if(token === '')
                    return
            }
            const r = await axios.post(`/api/v1/manager/${path}/${props.item.id}/2fa-qrcode/init`, {
                token: token
            })
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
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem value="password" @click="password.show(props.item.id, type)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-lock" />
                    </template>
                    <VListItemTitle>패스워드변경</VListItemTitle>
                </VListItem>
                <VListItem value="unlockAccount" @click="unlockAccount()" v-if="isAbleUnlock()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:lock-off" />
                    </template>
                    <VListItemTitle>계정잠금해제</VListItemTitle>
                </VListItem>                
                <VListItem value="init2FA" @click="init2FA()" v-if="isAble2FAInit()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:infinity-off" />
                    </template>
                    <VListItemTitle>2FA 초기화</VListItemTitle>
                </VListItem>
                <VListItem value="initPayVerfication" @click="initPayVerficationDialog.show(props.item.id)" v-if="corp.pv_options.paid.use_pay_verification_mobile && props.type === 0 && getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-device-mobile" />
                    </template>
                    <VListItemTitle>휴대폰인증회수 초기화</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>

