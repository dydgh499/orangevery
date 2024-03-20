<script lang="ts" setup>

import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import { operator_levels } from '@/views/services/operators/useStore'
import type { Operator } from '@/views/types'
import { avatars } from '@/views/users/useStore'
import { passwordValidator, requiredValidator } from '@validators'

interface Props {
    item: Operator,
    id: number | string,
}
const props = defineProps<Props>()
//--
const is_show = ref(false)
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>기본정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 Email -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>아이디</template>
                            <template #input>
                                <VTextField v-model="props.item.user_name" prepend-inner-icon="tabler-mail"
                                    placeholder="ID로 사용됩니다." persistent-placeholder :rules="[requiredValidator]"
                                    maxlength="30" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Password -->
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="props.id == 0">
                            <template #name>패스워드</template>
                            <template #input>
                                <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="[requiredValidator, passwordValidator]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" placeholder="소문자,대문자,특수문자로 이루어진 8자 이상 문자열"
                                    persistent-placeholder @click:append-inner="is_show = !is_show" autocomplete />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 대표자명 -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField v-model="props.item.nick_name" prepend-inner-icon="tabler-user"
                                    placeholder="사용자명으로 사용됩니다." :rules="[requiredValidator]" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Mobile -->
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>휴대폰번호</template>
                            <template #input>
                                <VTextField v-model="props.item.phone_num" type="number"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                    :rules="[requiredValidator]" persistent-placeholder />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>관리자 등급</template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                    :items="operator_levels" prepend-inner-icon="tabler-adjustments-up" label="등급 선택"
                                    item-title="title" item-value="id" single-line :rules="[requiredValidator]"
                                    :readonly="props.id != 0" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>프로필 이미지</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <SwiperPreview :items="avatars"
                                    :preview="props.item.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]"
                                    :label="'프로필'" :lmd="10" :rmd="2" @update:file="props.item.profile_file = $event"
                                    @update:path="props.item.profile_img = $event">
                                </SwiperPreview>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
  
