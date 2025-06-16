<script lang="ts" setup>
import ProfileDialog from '@/layouts/dialogs/users/ProfileDialog.vue';
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import { useRequestStore } from '@/views/request';
import { operator_levels, operatorActionAuthStore } from '@/views/services/operators/useStore';
import type { Operator } from '@/views/types';
import { avatars } from '@/views/users/useStore';
import { getUserLevel, user_info } from '@axios';
import corp from '@corp';
import { lengthValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

const phone_num_format = ref('')
const visible = ref(false)
const is_show = ref(false)
const profileDlg = ref()

const vForm = ref<VForm>()
const operator = ref(<Operator>({id:0, level:35}))
const { formRequest, remove } = useRequestStore()
const { headOfficeAuthValidate } = operatorActionAuthStore()

const show = (_operator: Operator) => {
    operator.value = _operator
    phone_num_format.value = operator.value.phone_num ?? ''
    visible.value = true
}

const isHeadOffice = () => {
    return getUserLevel() >= 40
}

const isAbleModiy = () => {
    return (isHeadOffice() || (getUserLevel() >= 35 && operator.value.id === user_info.value.id))
}

const operatorUpdate = async() => {
    /*
    const [result, token] = await headOfficeAuthValidate('휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        operator.value.token = token
        */
        await formRequest('/services/operators', operator.value, vForm.value, false)
    //}
}

const operatorDelete = async() => {
    const [result, token] = await headOfficeAuthValidate('휴대폰번호 인증이 필요합니다.<br>계속하시겠습니까?')
    if(result) {
        operator.value.token = token
        await remove('/services/operators', operator.value)
    }
}

const formatPhoneNum = computed(() => {
    let raw_value = phone_num_format.value.replace(/\D/g, '');
    operator.value.phone_num = raw_value
    // 휴대폰 번호 마스킹
    if(raw_value.length === 8)
        phone_num_format.value = raw_value.replace(/(\d{4})(\d{4})/, '$1-$2')
    else if(raw_value.startsWith("02") && (raw_value.length === 9 || raw_value.length === 10))
        phone_num_format.value = raw_value.replace(/(\d{2})(\d{3,4})(\d{4})/, '$1-$2-$3')
    else if(!raw_value.startsWith("02") && (raw_value.length === 10 || raw_value.length === 11))
        phone_num_format.value = raw_value.replace(/(\d{3})(\d{3,4})(\d{4})/, '$1-$2-$3')
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent style="max-width: 800px;">
        <DialogCloseBtn @click="visible = !visible" />
        <VCard :title="'운영자 정보 ' + (operator.id ? '수정' : '추가')">
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;" v-if="isAbleModiy()">
                                <VCol :md="5" cols="6">
                                    <label>* 아이디
                                        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal"
                                            @click="profileDlg.show()">
                                            <VImg
                                                :src="operator.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]" />
                                        </VAvatar>
                                    </label>
                                </VCol>
                                <VCol :md="7">
                                    <VTextField v-model="operator.user_name" prepend-inner-icon="tabler-mail"
                                        placeholder="ID로 사용됩니다." persistent-placeholder
                                        :rules="[requiredValidatorV2(operator.user_name, '아이디'), lengthValidator(operator.user_name, 8)]"
                                        maxlength="30"/>
                                </VCol>
                            </VRow>
                            <VRow style="align-items: center;" v-else>
                                <VCol :md="6" cols="6" class="font-weight-bold">
                                    <label>
                                        아이디
                                        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal">
                                            <VImg :src="operator.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]" />
                                        </VAvatar>
                                    </label>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        {{ operator.user_name }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12 v-if="operator.id == 0">
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">패스워드</VCol>
                                <VCol :md="7" cols="6">
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

                    <VRow v-if="isAbleModiy()">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">* 성명</VCol>
                                <VCol :md="7">
                                    <VTextField v-model="operator.nick_name" prepend-inner-icon="tabler-user"
                                        placeholder="사용자명으로 사용됩니다."
                                        :rules="[requiredValidatorV2(operator.nick_name, '성명')]"
                                        persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">
                                    <BaseQuestionTooltip :location="'top'"
                                        :text="`* 휴대폰번호`"
                                        :content="
                                        getUserLevel() === 35 ? 
                                        '직원 휴대폰번호 변경의 경우 본사등급만 인증 후 수정 가능합니다.' : 
                                        '본사 휴대폰번호 변경의 경우 개발사에 요청바랍니다.'" 
                                    />
                                </VCol>
                                <VCol :md="7">
                                    <VTextField
                                        v-model="phone_num_format" 
                                        @input="formatPhoneNum"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                        :rules="[requiredValidatorV2(operator.phone_num, '휴대폰번호')]"
                                        :disabled="(operator.id !== 0 && getUserLevel() !== 40) || (operator.id !== 0 && getUserLevel() === 40 && operator.level === 40)"
                                        persistent-placeholder/>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-else>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="6" cols="6">
                                    <b>성명</b>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        {{ operator.nick_name }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="6" cols="6">
                                    <b>휴대폰번호</b>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        {{ operator.phone_num }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>

                    <VRow v-if="isAbleModiy()">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">
                                    <BaseQuestionTooltip :location="'top'"
                                        :text="`* 등급`"
                                        :content=" '운영자 등급은 운영자 생성 후 변경이 불가합니다.'" 
                                    />
                                </VCol>
                                <VCol :md="7">
                                    <VSelect :menu-props="{ maxHeight: 400 }" 
                                        v-model="operator.level"
                                        :items="operator_levels" 
                                        prepend-inner-icon="tabler-adjustments-up"
                                        label="등급 선택" 
                                        item-title="title" item-value="id" 
                                        single-line
                                        :rules="[requiredValidatorV2(operator.level, '등급')]" 
                                        :disabled="operator.id != 0"
                                     />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-else>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol cols="6" md="6" class="font-weight-bold">
                                    <label>
                                        등급
                                    </label>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        <VChip :color="operator.level === 35 ? 'default' : 'primary'">
                                            {{ operator_levels.find(obj => obj.id === operator.level)?.title }}
                                        </VChip>
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-if="isAbleModiy()">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;float: inline-end;">
                                <VCol>
                                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="operator.is_notice_realtime_warning" label="송금 경고사항 알림"
                                        color="warning" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;float: inline-end;">
                                <VCol>
                                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="operator.is_active" label="활성화 여부"
                                        color="success" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-else>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;" v-if="corp.pv_options.paid.use_finance_van_deposit || corp.pv_options.paid.use_head_office_withdraw">
                                <VCol :md="6" cols="6">
                                    <b>송금 경고사항 알림</b>
                                </VCol>
                                <VCol :md="6">
                                    <VChip :color="operator.is_notice_realtime_warning ? 'success' : 'default'">
                                        {{ operator.is_notice_realtime_warning ? 'ON' : 'OFF' }}
                                    </VChip>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="6" cols="6">
                                    <b>활성화 여부</b>
                                </VCol>
                                <VCol :md="6">
                                    <VChip :color="operator.is_active ? 'success' : 'default'">
                                        {{ operator.is_active ? '활성화' : '비활성화' }}
                                    </VChip>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VForm>
                <template v-if="isAbleModiy()">
                    <br>
                    <VDivider />
                    <br>
                </template>
                <VRow v-if="isAbleModiy()">
                    <VCol cols="12" class="d-flex gap-4">
                        <VBtn type="button" style="margin-left: auto;" @click="operatorUpdate()">
                            {{ operator.id == 0 ? "추가" : "수정" }}
                            <VIcon end icon="tabler-pencil" />
                        </VBtn>
                        <VBtn type="button" color="error" v-if="operator.id && isHeadOffice()" @click="operatorDelete()">
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
