<script lang="ts" setup>
import ProfileDialog from '@/layouts/dialogs/users/ProfileDialog.vue';
import { useRequestStore } from '@/views/request';
import type { GMID } from '@/views/types';
import { avatars } from '@/views/users/useStore';
import { axios, getUserLevel } from '@axios';
import { HistoryTargetNames } from '@core/enums';
import { lengthValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

const phone_num_format = ref('')
const visible = ref(false)
const is_show = ref(false)
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const profileDlg = ref()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))

const vForm = ref<VForm>()
const gmid = ref(<GMID>({id:0}))
const { formRequest, remove } = useRequestStore()

const show = (_gmid: GMID) => {
    gmid.value = _gmid
    phone_num_format.value = gmid.value.phone_num ?? ''
    visible.value = true
}

const gmidCreate = async() => {
    if(await alert.value.show('정말 GMID를 신규 발급하시겠습니까?')) {
        const r = await axios.post('/api/v1/manager/merchandises/pay-modules/mid-create', {mid_code: "G"})
        if(r.status == 200)
            gmid.value.g_mid = r.data.mid
        else
            snackbar.value.error(r.data.message, 'error')
    }
}

const formatPhoneNum = computed(() => {
    let raw_value = phone_num_format.value.replace(/\D/g, '');
    gmid.value.phone_num = raw_value
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
        <VCard :title="'GMID 계정 ' + (getUserLevel() >= 35 ? (gmid.id ? '수정' : '추가') : '정보')">
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;" v-if="getUserLevel() >= 35">
                                <VCol :md="5" cols="6">
                                    <label>* 아이디
                                        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal"
                                            @click="profileDlg.show()">
                                            <VImg
                                                :src="gmid.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]" />
                                        </VAvatar>
                                    </label>
                                </VCol>
                                <VCol :md="7">
                                    <VTextField v-model="gmid.user_name" prepend-inner-icon="tabler-mail"
                                        placeholder="ID로 사용됩니다." persistent-placeholder
                                        :rules="[requiredValidatorV2(gmid.user_name, '아이디'), lengthValidator(gmid.user_name, 8)]"
                                        maxlength="30"/>
                                </VCol>
                            </VRow>
                            <VRow style="align-items: center;" v-else>
                                <VCol :md="6" cols="6" class="font-weight-bold">
                                    <label>
                                        아이디
                                        <VAvatar class="cursor-pointer" color="primary preview" variant="tonal">
                                            <VImg :src="gmid.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]" />
                                        </VAvatar>
                                    </label>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        {{ gmid.user_name }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12 v-if="gmid.id == 0">
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">패스워드</VCol>
                                <VCol :md="7" cols="6">
                                    <VTextField v-model="gmid.user_pw" counter prepend-inner-icon="tabler-lock"
                                        :rules="[requiredValidatorV2(gmid.user_pw, '패스워드'), passwordValidatorV2]"
                                        :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                        :type="is_show ? 'text' : 'password'"
                                        placeholder="소문자,대문자,특수문자로 이루어진 10자 이상 문자열" persistent-placeholder
                                        @click:append-inner="is_show = !is_show" autocomplete />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>

                    <VRow v-if="getUserLevel() >= 35">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">대표자명</VCol>
                                <VCol :md="7">
                                    <VTextField v-model="gmid.nick_name" prepend-inner-icon="tabler-user"
                                        placeholder="대표자명 입력"
                                        :rules="[requiredValidatorV2(gmid.nick_name, '대표자명')]"
                                        persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">
                                    휴대폰번호
                                </VCol>
                                <VCol :md="7">
                                    <VTextField
                                        v-model="phone_num_format" 
                                        @input="formatPhoneNum"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="010-0000-0000"
                                        :rules="[requiredValidatorV2(gmid.phone_num, '휴대폰번호')]"
                                        :disabled="(gmid.id !== 0 && getUserLevel() !== 40) || (gmid.id !== 0 && getUserLevel() === 40 && gmid.level === 40)"
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
                                        {{ gmid.nick_name }}
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
                                        {{ gmid.phone_num }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-if="getUserLevel() >= 35">
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="5" cols="6">
                                    <div style="display: inline-block;">
                                        <span>GMID</span>
                                        <VBtn type="button" variant="tonal" v-if="getUserLevel() >= 35" 
                                            @click="gmidCreate()" style="margin-left: 0.5em;" size="small" color="info">
                                            {{ "발급" }}
                                        </VBtn>
                                    </div>
                                    </VCol>
                                <VCol :md="7">
                                    <VTextField
                                        variant="underlined"
                                        v-model="gmid.g_mid" 
                                        :rules="[requiredValidatorV2(gmid.phone_num, 'GMID')]"
                                        :readonly="true"
                                    />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow v-else>
                        <VCol md="6" cols=12>
                            <VRow style="align-items: center;">
                                <VCol :md="6" cols="6">
                                    <b>GMID</b>
                                </VCol>
                                <VCol :md="6">
                                    <span>
                                        {{ gmid.g_mid }}
                                    </span>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VForm>
                <br>
                <VDivider />
                <br>
                <VRow v-if="getUserLevel() >= 35">
                    <VCol cols="12" class="d-flex gap-4">
                        <VBtn v-if="gmid.id"
                            style="margin-left: auto;"
                            color="secondary" 
                            variant="tonal"
                            @click="activityHistoryTargetDialog.show(gmid.id, HistoryTargetNames['gmids'])">
                            이력
                            <VIcon end size="20" icon="tabler:history" />
                        </VBtn>
                        <VBtn 
                            style="margin-left: 1em;"
                            @click="formRequest('/gmids', gmid, vForm, false)">
                            {{ gmid.id == 0 ? "추가" : "수정" }}
                            <VIcon end size="20" icon="tabler-pencil" />
                        </VBtn>
                        <VBtn v-if="gmid.id"
                            style="margin-left: 1em;"
                            color="error"
                            @click="remove('/gmids', gmid)">
                            삭제
                            <VIcon end size="20" icon="tabler-trash" />
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <ProfileDialog ref="profileDlg" :item="gmid" :key="gmid.profile_img"/>
    </VDialog>
</template>
