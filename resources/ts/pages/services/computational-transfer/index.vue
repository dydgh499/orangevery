
<script setup lang="ts">
import { requiredValidator } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import ProgressDialog from '@/layouts/dialogs/ProgressDialog.vue'
import { reactive } from 'vue';
import { axios } from '@axios';
import { cloneDeep } from 'lodash'
import { VForm } from 'vuetify/components'
import corp from '@corp'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const vForm = ref<VForm>()
const is_hidden = ref(false)
const is_loading = ref(false)
const is_disabled = ref(true)
const is_success = ref(false)
const process = ref()

const login_info = reactive({
    domain: 'vivapay.co.kr',
    user_name: 'master',
    user_pw: 'master1234@',
    token: '',
})

const login = async() => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 로그인 하시겠습니까?')) {
        try {
            const r = await axios.post('/api/v1/computational-transfer/login', cloneDeep(login_info))
            snackbar.value.show('성공하였습니다.', 'success')
            login_info.token = r.data.token
            is_disabled.value = false
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const register = async() => {
    if (await alert.value.show('정말 연동 하시겠습니까? 모든정보가 연동되므로 시간이 소요됩니다.')) {
        process.value.show(true, 0, '연동을 시작합니다.')
        is_loading.value = true
        try {
            const token = login_info.token
            const brand_id = corp.id
            var eventSource = new EventSource(`/api/v1/computational-transfer/register?token=${token}&brand_id=${brand_id}`);
            eventSource.onmessage = function(event) {
                const json = JSON.parse(event.data)
                snackbar.value.show(json.message, 'success');
                process.value.show(true, json.per, json.message)
            };
            
            eventSource.onerror = function(error) {
                if (eventSource.readyState === EventSource.CLOSED) {
                    is_success.value = true
                } else {
                    snackbar.value.show('무언가 에러가 발견해 취소되었습니다<br>주의사항을 확인해주세요.', 'error');
                    process.value.show(false, 0, '')
                    console.error('EventSource failed:', error)
                    eventSource.close()
                }
                is_loading.value = false
                process.value.show(false, 0, '')
            };
        }
        catch (e) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
</script>
<template>
    <section>
        <VCard>
            <CreateHalfVCol :mdl="12" :mdr="0">
                <template #name>
                    <VCol class="d-flex justify-center align-center">
                        <VCard flat class="d-flex flex-column align-items-center mt-12 mt-sm-0 pa-4">
                            <VCol style=" max-width: 500px; line-height: 2.5em; text-align: center;">
                                업그레이드 이전 전산의 도메인과 본사 계정정보를 입력후 로그인 버튼을 클릭해주세요.
                                <br>
                                로그인에 성공하면 전산 연동버튼이 활성화 됩니다.
                            </VCol>
                            <VCardText>
                                <VForm ref="vForm" @submit.prevent="login" style="max-width: 500px;">
                                    <VRow>
                                        <!-- domain -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.domain" label="도메인 입력" type="domain"
                                                :rules="[requiredValidator]" />
                                        </VCol>
                                        <!-- user_name -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.user_name" label="아이디 입력" type="user_name"
                                                :rules="[requiredValidator]" />
                                        </VCol>
                                        <!-- password -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.user_pw" label="패스워드 입력"
                                                :rules="[requiredValidator]" :type="is_hidden ? 'text' : 'password'"
                                                :append-inner-icon="is_hidden ? 'tabler-eye-off' : 'tabler-eye'"
                                                @click:append-inner="is_hidden = !is_hidden" class="mb-6" />

                                            <VBtn block type="submit">
                                                로그인
                                            </VBtn>
                                            <br>
                                            <VBtn :loading="is_loading" :disabled="is_disabled" block @click="register()">
                                                전산 연동
                                            </VBtn>                                            
                                        </VCol>
                                        <VCol class="text-center text-primary" style="font-weight: bold;" v-if="is_success">
                                            환영합니다! 🎉 새로고침 후 연동정보를 확인해주세요.
                                        </VCol>
                                    </VRow>
                                </VForm>
                            </VCardText>
                        </VCard>
                    </VCol>
                </template>
                <template #input>
                </template>
            </CreateHalfVCol>
        </VCard>
        <ProgressDialog ref="process"/>
    </section>
</template>
<style lang="scss">
.list-square {
  padding-block: 0;
  padding-inline: 6px !important;
  text-align: center !important;
}
</style>
