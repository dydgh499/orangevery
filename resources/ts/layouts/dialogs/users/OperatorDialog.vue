<script lang="ts" setup>
import ProfileDialog from '@/layouts/dialogs/users/ProfileDialog.vue'
import { useRequestStore } from '@/views/request'
import type { Operator, Options } from '@/views/types'
import { avatars } from '@/views/users/useStore'
import { axios, getUserLevel } from '@axios'
import { lengthValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'


const snackbar = <any>(inject('snackbar'))
const visible = ref(false)
const is_show = ref(false)
const profileDlg = ref()

const vForm = ref<VForm>()
const operator = ref(<Operator>({id:0}))
const operator_levels = ref(<Options[]>([]))
const { formRequest, remove } = useRequestStore()


const verification = async () => {
    try {
        const r = await axios.post('/api/v1/bonaejas/mobile-code-auth', { 
            phone_num: operator.value.above_phone_num, 
            verification_number: operator.value.appr_num 
        })
        operator.value.token = r.data.token
        snackbar.value.show('인증에 성공하였습니다.<br>이어서 진행해주세요.', 'success')
    }
    catch(e:any) {
        snackbar.value.show(e.response.data.message, 'warning')
    }
}

const show = (_operator: Operator) => {
    operator_levels.value = []
    if(getUserLevel() >= 35)
        operator_levels.value.push({id:35, title:'직원'})
    if(getUserLevel() >= 40 && _operator.id !== 0)
        operator_levels.value.push({id:40, title:'본사'})

    operator.value = _operator
    visible.value = true
}

const modifyProfleimage = () => {
    if(getUserLevel() >= 40)
        profileDlg.value.show()
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent style="max-width: 900px;">
        <DialogCloseBtn @click="visible = !visible" />
        <VCard>
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="4" cols="5">
                                    <label>* 아이디
                                        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal"
                                            @click="modifyProfleimage()">
                                            <VImg
                                                :src="operator.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]" />
                                        </VAvatar>
                                    </label>
                                </VCol>
                                <VCol :md="8">
                                    <VTextField v-model="operator.user_name" prepend-inner-icon="tabler-mail"
                                        placeholder="ID로 사용됩니다." persistent-placeholder
                                        :rules="[requiredValidatorV2(operator.user_name, '아이디'), lengthValidator(operator.user_name, 8)]"
                                        maxlength="30" v-if="getUserLevel() >= 40" />
                                    <span v-else>{{ operator.user_name }}</span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12 v-if="operator.id == 0">
                            <VRow style="align-items: center;">
                                <VCol :md="4" cols="5">패스워드</VCol>
                                <VCol :md="8">
                                    <VTextField v-model="operator.user_pw" counter prepend-inner-icon="tabler-lock"
                                        :rules="[requiredValidatorV2(operator.user_pw, '패스워드'), passwordValidatorV2]"
                                        :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                        :type="is_show ? 'text' : 'password'"
                                        placeholder="소문자,대문자,특수문자로 이루어진 10자 이상 문자열" persistent-placeholder
                                        @click:append-inner="is_show = !is_show" autocomplete />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="4" cols="5">* 대표자명</VCol>
                                <VCol :md="8">
                                    <VTextField v-model="operator.nick_name" prepend-inner-icon="tabler-user"
                                        placeholder="사용자명으로 사용됩니다."
                                        :rules="[requiredValidatorV2(operator.nick_name, '대표자명')]"
                                        persistent-placeholder v-if="getUserLevel() >= 40" />
                                    <span v-else>{{ operator.nick_name }}</span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12 v-if="operator.level === 35 || operator.id === 0">
                            <VRow style="align-items: center;">
                                <VCol :md="4" cols="5">휴대폰번호</VCol>
                                <VCol :md="8">
                                    <VTextField v-model="operator.phone_num" type="number"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                        :rules="[requiredValidatorV2(operator.phone_num, '휴대폰번호')]"
                                        persistent-placeholder v-if="getUserLevel() >= 40" />
                                    <span v-else>{{ operator.phone_num }}</span>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="4" cols="5">* 등급</VCol>
                                <VCol :md="8">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="operator.level"
                                        :items="operator_levels" prepend-inner-icon="tabler-adjustments-up"
                                        label="등급 선택" item-title="title" item-value="id" single-line
                                        :rules="[requiredValidatorV2(operator.level, '등급')]" :readonly="operator.id != 0"
                                        v-if="getUserLevel() >= 40" />
                                        <span v-else>
                                            {{ operator_levels.find(obj => obj.id === operator.level)?.title}}
                                        </span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12 v-if="operator.result === 956">
                            <VRow style="align-items: center;">
                                <VCol :md="6" cols="7">
                                    <span>인증번호</span>
                                    <VBtn end @click="verification()" size="small" style="margin-left: 0.5em;">
                                        인증하기
                                    </VBtn>
                                </VCol>
                                <VCol :md="6">
                                    <VTextField v-model="operator.appr_num" type="number"
                                        prepend-inner-icon="arcticons:2fas-auth" placeholder="인증번호 입력"
                                        persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VForm>
                <br>
                <VDivider />
                <br>
                <VRow v-if=" getUserLevel() >= 40">
                    <VCol cols="12" class="d-flex gap-4">
                        <VBtn type="button" style="margin-left: auto;" @click="formRequest('/services/operators', operator, vForm)">
                            {{ operator.id == 0 ? "추가" : "수정" }}
                            <VIcon end icon="tabler-pencil" />
                        </VBtn>
                        <VBtn type="button" color="error" v-if="operator.id" @click="remove('/services/operators', operator)">
                            삭제
                            <VIcon end icon="tabler-trash" />
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <ProfileDialog ref="profileDlg" :item="operator" :key="operator.profile_img"/>
    </VDialog>
</template>
