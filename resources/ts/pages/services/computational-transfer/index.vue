
<script setup lang="ts">
import { settleCycles, settleDays } from '@/views/salesforces/useStore'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'

import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import ProgressDialog from '@/layouts/dialogs/ProgressDialog.vue'
import { requiredValidator } from '@validators'
import { reactive } from 'vue';
import { axios } from '@axios';
import { cloneDeep } from 'lodash'
import { VForm } from 'vuetify/components'
import corp from '@corp'

const {  settle_types } = useStore()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const vForm = ref<VForm>()
const is_hidden = ref(false)
const is_loading = ref(false)
const is_disabled = ref(true)
const is_transfer = ref(corp.is_transfer)
const process = ref()

const login_info = reactive({
    domain: '',
    user_name: '',
    user_pw: '',
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
    if (await alert.value.show('정말 연동 하시겠습니까? 대량의 정보가 연동되므로 시간이 소요됩니다.<br><b>해당 기능은 전산당 1번만 할 수 있습니다.</b>')) {
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
                eventSource.close()
                login_info.token = ''
                login_info.domain = ''
                login_info.user_name = ''
                login_info.user_pw = ''
                is_loading.value = false
                is_disabled.value = true
                process.value.show(false, 0, '')
                snackbar.value.show('환영합니다! 🎉 연동정보를 확인해주세요.', 'success');
                setTimeout(function () { location.reload() }, 1000)
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
            <CreateHalfVCol :mdl="6" :mdr="6">
                <template #name>
                    <VCol class="d-flex justify-center align-center">
                        <VCard flat class="d-flex flex-column align-items-center mt-12 mt-sm-0 pa-4">
                            <VCol style=" max-width: 500px; line-height: 2.5em; text-align: center;">
                                <b>
                                업그레이드 이전 전산의 도메인과 본사 계정정보를 입력후 로그인 버튼을 클릭해주세요.
                                <br>
                                로그인에 성공하면 
                                <VBtn size="small" :disabled="is_disabled">
                                    이전 전산내용 추가하기
                                </VBtn>    
                                이 활성화 됩니다.
                                </b>
                                <br>
                                <b>연동정보: 
                                    <VChip color="primary" style="margin: 0.5em;">본사 및 PG사 정보</VChip>
                                    <VChip color="primary" style="margin: 0.5em;">가맹점</VChip>
                                    <VChip color="primary" style="margin: 0.5em;">영업점</VChip>
                                    <VChip color="primary" style="margin: 0.5em;">결제모듈</VChip>
                                </b>
                            </VCol>
                            <VDivider/>
                            <VCardText>
                                <VForm ref="vForm" @submit.prevent="login" style="max-width: 500px;">
                                    <VRow>
                                        <!-- domain -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.domain" label="도메인 입력" type="domain"
                                                :rules="[requiredValidator]" :disabled="is_transfer"/>
                                        </VCol>
                                        <!-- user_name -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.user_name" label="아이디 입력" type="user_name"
                                                :rules="[requiredValidator]" :disabled="is_transfer" />
                                        </VCol>
                                        <!-- password -->
                                        <VCol cols="12">
                                            <VTextField v-model="login_info.user_pw" label="패스워드 입력"
                                                :rules="[requiredValidator]" :type="is_hidden ? 'text' : 'password'"
                                                :append-inner-icon="is_hidden ? 'tabler-eye-off' : 'tabler-eye'"
                                                @click:append-inner="is_hidden = !is_hidden" class="mb-6" :disabled="is_transfer"/>

                                            <VBtn block type="submit" :disabled="is_transfer">
                                                로그인
                                            </VBtn>
                                            <br>
                                            <VBtn :loading="is_loading" :disabled="is_disabled" block @click="register()">
                                                이전 전산내용 추가하기
                                            </VBtn>                                            
                                        </VCol>
                                        <VCol class="text-center text-primary" style="font-weight: bold;" v-if="is_transfer">
                                            이미 이전 전산을 추가하셨습니다.
                                        </VCol>
                                    </VRow>
                                </VForm>
                            </VCardText>
                        </VCard>
                    </VCol>
                </template>
                <template #input>
                    <VCol class="d-flex justify-center align-center">
                        <VCard flat class="d-flex flex-column align-items-center mt-12 mt-sm-0 pa-4">
                            <h3>주의사항</h3>
                            <VCol style="line-height: 2.5em;">
                                <h4>하단 정보들은 이전전산에서 구현되었지 않았거나, 연동이 불가한 정보들이므로 연동시 기본값으로 세팅됩니다.</h4>
                                <h4>추가설정이 필요하오니 참고 부탁드립니다.</h4>
                                <h4>영업자 기본 값</h4>
                                <b style="margin-left: 1em;">- 정산일 </b>
                                <VChip>{{ settleDays().find(item => item.id === null)?.title }}</VChip>
                                <br>
                                <b style="margin-left: 1em;">- 정산주기 </b>
                                <VChip>{{ settleCycles().find(item => item.id === 0)?.title }}</VChip>
                                <br>
                                <h4>결제모듈 기본 값</h4>
                                <b style="margin-left: 1em;">- 정산타입 </b>                                
                                <VChip>{{ settle_types.find(item => item.id === 0)?.name }}</VChip>                                
                                <br>
                                <b style="margin-left: 1em;">- 입금 수수료 </b>                                
                                <VChip>0원</VChip>                                
                                <br>
                                <b style="margin-left: 1em;">- 비인증 단말기 모듈타입 </b>
                                <VChip>{{ module_types.find(item => item.id === 1)?.title }}</VChip>
                                <br>
                                <b style="margin-left: 1em;">- 구간타입(단말기 이외만 적용) </b>
                                <VChip>{{ '기본 값 없음' }}</VChip>
                            </VCol>
                        </VCard>
                    </VCol>
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
