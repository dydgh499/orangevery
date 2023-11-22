<script setup lang="ts">
import PasswordChangeDialog from '@/layouts/dialogs/PasswordChangeDialog.vue'
import { initialAbility } from '@/plugins/casl/ability'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { axios, pay_token, user_info, allLevels } from '@axios'
import { avatars } from '@/views/users/useStore'
import router from '@/router'
import corp from '@corp'

const ability = useAppAbility()
const password = ref()
const all_levels = allLevels()
const snackbar = <any>(inject('snackbar'))

let mylink = ''
let mytype = 0
if (user_info.value.level == 10) {
    mylink = '/merchandises/edit/' + user_info.value.id
    mytype = 0
}
else if (user_info.value.level <= 30) {
    mylink = '/salesforces/edit/' + user_info.value.id
    mytype = 1
}
else if (user_info.value.level <= 45) {
    mylink = '/services/operators/edit/' + user_info.value.id
    mytype = 2
}
else
    mytype = 3

// 개발사는 이동할 수 없음
const profile = () => {
    if(mytype < 3)
        router.push(mylink)
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
user_info.value.profile_img = user_info.value.profile_img ? user_info.value.profile_img : avatars[Math.floor(Math.random() * avatars.length)]
</script>

<template>
    <VBadge dot location="bottom right" offset-x="3" offset-y="3" bordered color="success">
        <VAvatar class="cursor-pointer" color="primary" variant="tonal">
            <VImg :src="user_info.profile_img" />

            <!-- SECTION Menu -->
            <VMenu activator="parent" width="230" location="bottom end" offset="14px">
                <VList>
                    <!-- 👉 User Avatar & Name -->
                    <VListItem>
                        <template #prepend>
                            <VListItemAction start>
                                <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success">
                                    <VAvatar color="primary" variant="tonal">
                                        <VImg :src="user_info.profile_img" />
                                    </VAvatar>
                                </VBadge>
                            </VListItemAction>
                        </template>

                        <VListItemTitle class="font-weight-semibold">
                            {{ user_info.user_name }}
                        </VListItemTitle>
                        <VListItemSubtitle>{{ all_levels.find(level => level['id'] === user_info.level)?.title }}</VListItemSubtitle>
                    </VListItem>

                    <VDivider class="my-2" v-if="user_info.level > 10" />
                    <VListItem @click="profile()" class="custom-link" v-if="user_info.level > 10">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-user" size="22" />
                        </template>
                        <VListItemTitle>프로필</VListItemTitle>
                    </VListItem>
                    <VDivider class="my-2" />
                    <VListItem @click="password.show(user_info.id, mytype)">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-lock" size="22" />
                        </template>
                        <VListItemTitle>패스워드 변경</VListItemTitle>
                    </VListItem>
                    <!-- Divider -->
                    <VDivider class="my-2" />

                    <!-- 👉 Logout -->
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
