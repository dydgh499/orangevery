<script setup lang="ts">
import { initialAbility } from '@/plugins/casl/ability'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { axios, pay_token, user_info } from '@axios'
import { getRating } from '@layouts/utils'

const router = useRouter()
const ability = useAppAbility()

let mylink = ''
if (user_info.value.level == 10)
    mylink = '/merchandises/edit/' + user_info.value.id;
else if (user_info.value.level <= 30)
    mylink = '/salesforces/edit/' + user_info.value.id;
else if (user_info.value.level <= 45)
    mylink = '/operators/edit/' + user_info.value.id;

const avartar_num = Math.floor(Math.random() * 25) + 1;

const logout = async () => {
    await axios.get('/api/v1/auth/sign-out', {})
    localStorage.removeItem('abilities')
    pay_token.value = ''
    user_info.value = {}
    // Redirect to login page
    router.replace('/login')
        .then(() => {
            ability.update(initialAbility)
        })
}
</script>

<template>
    <VBadge dot location="bottom right" offset-x="3" offset-y="3" bordered color="success">
        <VAvatar class="cursor-pointer" color="primary" variant="tonal">
            <VImg :src="'/images/avatars/avatar_' + avartar_num + '.jpg'" />

            <!-- SECTION Menu -->
            <VMenu activator="parent" width="230" location="bottom end" offset="14px">
                <VList>
                    <!-- 👉 User Avatar & Name -->
                    <VListItem>
                        <template #prepend>
                            <VListItemAction start>
                                <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success">
                                    <VAvatar color="primary" variant="tonal">
                                        <VImg :src="'/images/avatars/avatar_' + avartar_num + '.jpg'" />
                                    </VAvatar>
                                </VBadge>
                            </VListItemAction>
                        </template>

                        <VListItemTitle class="font-weight-semibold">
                            {{ user_info.user_name }}
                        </VListItemTitle>
                        <VListItemSubtitle>{{ getRating(user_info.level) }}</VListItemSubtitle>
                    </VListItem>

                    <VDivider class="my-2" />

                    <router-link :to="mylink" class="custom-link">
                        <VListItem>
                            <template #prepend>
                                <VIcon class="me-2" icon="tabler-user" size="22" />
                            </template>

                            <VListItemTitle>Profile</VListItemTitle>
                        </VListItem>
                    </router-link>
                        <!-- Divider -->
                        <VDivider class="my-2" />

                    <!-- 👉 Logout -->
                    <VListItem link @click="logout">
                        <template #prepend>
                            <VIcon class="me-2" icon="tabler-logout" size="22" />
                        </template>

                        <VListItemTitle>Logout</VListItemTitle>
                    </VListItem>
                </VList>
            </VMenu>
            <!-- !SECTION -->
        </VAvatar>
    </VBadge>
</template>
<style lang="scss" scoped>
.custom-link {
  color: inherit;
  text-decoration: none;
}
</style>
