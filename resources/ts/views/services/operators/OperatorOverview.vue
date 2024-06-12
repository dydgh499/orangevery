<script lang="ts" setup>

import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import type { Operator, Options } from '@/views/types'
import { avatars } from '@/views/users/useStore'
import { axios, getUserLevel } from '@axios'
import { lengthValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators'

interface Props {
    item: Operator,
    id: number | string,
}
const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))
const is_show = ref(false)
const operator_levels:Options[] = []

if(getUserLevel() >= 35)
    operator_levels.push({id:35, title:'직원'})
if(getUserLevel() >= 40)
    operator_levels.push({id:40, title:'본사'})

const verification = async () => {
    try {
        const r = await axios.post('/api/v1/bonaejas/mobile-code-auth', { phone_num: props.item.above_phone_num, verification_number: props.item.appr_num })
        props.item.token = r.data.token
        snackbar.value.show('인증에 성공하였습니다.<br>이어서 수정을 진행해주세요.', 'success')
    }
    catch(e:any) {
        snackbar.value.show(e.response.data.message, 'warning')
    }
}

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
                                    placeholder="ID로 사용됩니다." persistent-placeholder :rules="[requiredValidatorV2(props.item.user_name, '아이디'), lengthValidator(props.item.user_name, 8)]"
                                    maxlength="30" v-if="getUserLevel() >= 40"/>
                                <span v-else>{{ props.item.user_name }}</span>
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 Password -->
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="props.id == 0">
                            <template #name>패스워드</template>
                            <template #input>
                                <VTextField v-model="props.item.user_pw" counter prepend-inner-icon="tabler-lock"
                                    :rules="[requiredValidatorV2(props.item.user_pw, '패스워드'), passwordValidatorV2]"
                                    :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                    :type="is_show ? 'text' : 'password'" placeholder="소문자,대문자,특수문자로 이루어진 10자 이상 문자열"
                                    persistent-placeholder @click:append-inner="is_show = !is_show" autocomplete />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>대표자명</template>
                            <template #input>
                                <VTextField v-model="props.item.nick_name" prepend-inner-icon="tabler-user"
                                    placeholder="사용자명으로 사용됩니다." :rules="[requiredValidatorV2(props.item.nick_name, '대표자명')]" persistent-placeholder 
                                    v-if="getUserLevel() >= 40"/>
                                <span v-else>{{ props.item.nick_name }}</span>

                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9" v-if="props.item.level === 35 || props.item.id === 0">
                            <template #name>휴대폰번호</template>
                            <template #input>
                                <VTextField v-model="props.item.phone_num" type="number"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                    :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호')]" persistent-placeholder 
                                    v-if="getUserLevel() >= 40"/>
                                <span v-else>{{ props.item.phone_num }}</span>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>관리자 등급</template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.level"
                                    :items="operator_levels" prepend-inner-icon="tabler-adjustments-up" label="등급 선택"
                                    item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.item.level, '등급')]"
                                    :readonly="props.id != 0" v-if="getUserLevel() >= 40"/>
                                <span v-else>{{ operator_levels.find(obj => obj.id === props.item.level)?.title }}</span>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6" v-if="props.item.result === 956">
                            <template #name>인증번호</template>
                            <template #input>
                                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VTextField v-model="props.item.appr_num" type="number"
                                        prepend-inner-icon="arcticons:2fas-auth" placeholder="인증번호 입력"
                                        persistent-placeholder />
                                    <VBtn end @click="verification()" style="margin-left: 1em;">
                                        휴대폰 인증하기
                                    </VBtn>
                                </div>
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
