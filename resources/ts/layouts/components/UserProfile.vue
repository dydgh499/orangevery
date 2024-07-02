<script setup lang="ts">
import Google2FACreateDialog from '@/layouts/dialogs/users/Google2FACreateDialog.vue'
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue'
import { initialAbility } from '@/plugins/casl/ability'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import router from '@/router'
import { avatars } from '@/views/users/useStore'
import { allLevels, axios, getUserLevel, getUserType, pay_token, user_info } from '@axios'
import corp from '@corp'

const ability = useAppAbility()
const password = ref()
const all_levels = allLevels()
const snackbar = <any>(inject('snackbar'))
const imageDialog = ref()
const google2FACreateDialog = ref()

const notice_mark = ref({
    dot: true,
    location: 'bottom right',
    color: 'success',
    content: '',
})
const require_2fa = ref(false)
const mytype = getUserType()

// 개발사는 이동할 수 없음
const profile = () => {
    if(mytype.id < 3)
        router.push(mytype.link)
    else   
        snackbar.value.show(`${corp.pv_options.auth.levels.dev_name}는 프로필로 이동할 수 없습니다.`, 'warning')
}

const logout = async () => {
    await axios.get('/api/v1/auth/sign-out', {})
    localStorage.removeItem('abilities')
    pay_token.value = ''
    user_info.value = {}
    ability.update(initialAbility)
    location.href = '/'
}
const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}

const show2FAAuthDialog = () => {
    if(getUserLevel() >= 35 && user_info.is_2fa_use && corp.pv_options.paid.use_head_office_withdraw) {
        // 휴대폰 인증 후 재설정
    }
    else
        google2FACreateDialog.value.show()
}

const noticeOperator2FaStatus = () => {
    if(getUserLevel() > 10) {
        if(user_info.value.is_2fa_use === false) {
            notice_mark.value.dot = false
            notice_mark.value.location = 'top right'
            notice_mark.value.color = 'error'
            notice_mark.value.content = '!'
            return true
        }
    }
    return false
}

user_info.value.profile_img = user_info.value.profile_img ? user_info.value.profile_img : avatars[Math.floor(Math.random() * avatars.length)]
require_2fa.value = noticeOperator2FaStatus()
</script>

<template>
    <VBadge :dot="notice_mark.dot" :location="notice_mark.location" offset-x="3" offset-y="3" bordered :color="notice_mark.color" :content="notice_mark.content">
        <VTooltip v-if="require_2fa" activator="parent" location="top" transition="scale-transition" style="max-width: 15em; margin-left: auto;">
            <span>2차인증 설정이 필요합니다.</span>
        </VTooltip>
        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal">
            <VImg :src="user_info.profile_img" />
            <!-- SECTION Menu -->
            <VMenu activator="parent" width="230" location="bottom end" offset="14px">
                <VList>
                    <!-- 👉 User Avatar & Name -->
                    <VListItem>
                        <template #prepend>
                            <VListItemAction start>
                                <VBadge :dot="notice_mark.dot" :location="notice_mark.location" offset-x="3" offset-y="3" bordered :color="notice_mark.color" :content="notice_mark.content">
                                    <VAvatar color="primary" variant="tonal" @click="showAvatar(user_info.profile_img)" class="preview">
                                        <VImg :src="user_info.profile_img" />
                                    </VAvatar>
                                </VBadge>
                            </VListItemAction>
                        </template>
                        <VListItemTitle class="font-weight-semibold">
                            {{ user_info.user_name }}
                        </VListItemTitle>
                        <VListItemSubtitle>{{ all_levels.find(level => level['id'] === getUserLevel())?.title }}</VListItemSubtitle>
                    </VListItem>
                    <VDivider class="my-2" v-if="getUserLevel() > 10" />
                    <VListItem @click="profile()" class="custom-link" v-if="getUserLevel() > 10">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-user" size="22" />
                        </template>
                        <VListItemTitle>프로필</VListItemTitle>
                    </VListItem>
                    <VDivider class="my-2" />
                    <VListItem @click="password.show(user_info.id, mytype.id)">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-lock" size="22" />
                        </template>
                        <VListItemTitle>패스워드 변경</VListItemTitle>
                    </VListItem>
                    <template v-if="getUserLevel() > 10">
                        <VDivider class="my-2" />
                        <VListItem value="2fa" @click="show2FAAuthDialog()" 
                        :class="require_2fa ? 'pg-cancel' : ''">
                            <template #prepend>
                                <VIcon size="22" class="me-2" icon="carbon:two-factor-authentication" />
                            </template>
                            <VListItemTitle v-if="user_info.is_2fa_use">2차인증 재설정</VListItemTitle>
                            <VListItemTitle v-else>2차인증 설정</VListItemTitle>
                        </VListItem>
                    </template>
                    <VDivider class="my-2" />
                    <VListItem link @click="logout">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-logout" size="22" />
                        </template>
                        <VListItemTitle>로그아웃</VListItemTitle>
                    </VListItem>
                </VList>
            </VMenu>
            <!-- !SECTION -->
        </VAvatar>
        <PasswordChangeDialog ref="password" />
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
        <Google2FACreateDialog ref="google2FACreateDialog"/>
    </VBadge>
</template>
<style scoped>
.custom-link {
  color: inherit;
  text-decoration: none;
}

:deep(.v-overlay__content) {
  inset-inline-end: 1.5em !important;
  inset-inline-start: 0 !important;
  margin-inline-start: auto !important;
}
</style>
