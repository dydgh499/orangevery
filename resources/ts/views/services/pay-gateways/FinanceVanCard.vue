<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue'
import { getUserLevel } from '@/plugins/axios'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FinanceVan } from '@/views/types'
import { banks } from '@/views/users/useStore'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: FinanceVan,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { finance_companies, is_agency_vans } = useStore()
const { update, remove } = useRequestStore()

const bank = ref(<any>({ code: null, title: '선택안함' }))

const getAcctBankName = () => {
    const bank = banks.find(obj => obj.code == props.item.bank_code)
    return bank ? `${bank.title}, 은행 코드: ${bank.code}` : '선택안함'
}

onMounted(async () => {
    watchEffect(() => {
        if (props.item.bank_code !== null && props.item.bank_code != "000") {
            bank.value = banks.find(obj => obj.code == props.item.bank_code)
        }
    })
    watchEffect(() => {
        if (bank.value) {
            props.item.bank_code = bank.value?.code || null
        }
    })
})
</script>
<template>
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed :title="props.item.nick_name">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                금융 VAN 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>금융 VAN</template>
                                <template #l_input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.finance_company_num"
                                        density="compact" variant="outlined" :items="finance_companies"
                                        eager item-title="title" item-value="id" :rules="[requiredValidatorV2(props.item.finance_company_num, '금융 VAN')]" />
                                </template>
                                <template #r_name>
                                    <BaseQuestionTooltip :location="'top'" :text="'지급대행 모듈'" :content="'영업일 기준 13:00 ~ 17:00 사이 가맹점 정산관리에서 해당 결제모듈로<br>입금된 입금액과 정산할 금액을 검증하는 팝업창이 발생합니다.'"/>
                                </template>
                                <template #r_input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.is_agency_van"
                                        density="compact" variant="outlined" :items="is_agency_vans" :eager="true"
                                        item-title="title" item-value="id" :rules="[requiredValidatorV2(props.item.is_agency_van, '지급대행 모듈')]" />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>API KEY</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="props.item.api_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                            persistent-placeholder />
                                </template>
                                <template #r_name>SUB KEY</template>
                                <template #r_input>
                                        <VTextField type="text" v-model="props.item.sub_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="SUB KEY 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>ENC KEY</template>
                                <template #l_input>
                                        <VTextField type="text" v-model="props.item.enc_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="ENC KEY 입력"
                                            persistent-placeholder />
                                </template>
                                <template #r_name>IV</template>
                                <template #r_input>
                                        <VTextField type="text" v-model="props.item.iv"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="Initial vector 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVColV2>
                        </VCardItem>
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                연동 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>기관명</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="props.item.corp_name" prepend-inner-icon="ph-buildings"
                                        placeholder="기관명 입력" persistent-placeholder />
                                </template>
                                <template #r_name>
                                    <BaseQuestionTooltip :location="'top'" :text="corp.pv_options.auth.levels.dev_name+' 수수료'" :content="'해당 정보는 보안상 '+corp.pv_options.auth.levels.dev_name+'만 보여집니다.'" v-if="getUserLevel() == 50"/>
                                </template>
                                <template #r_input>
                                    <VTextField type="text" v-model="props.item.dev_fee"
                                            prepend-inner-icon="ph:share-network" placeholder="0.1" suffix="%"
                                            persistent-placeholder v-if="getUserLevel() == 50"/>
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>기관코드(GUID)</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="props.item.corp_code"
                                        prepend-inner-icon="ph:share-network" placeholder="기관코드 입력"
                                        persistent-placeholder />
                                </template>
                                <template #r_name>은행</template>
                                <template #r_input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.bank_code"
                                        :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                        label="은행 선택" item-title="title" item-value="code" persistent-hint single-line
                                        :hint="getAcctBankName()"
                                        :rules="[requiredValidatorV2(props.item.bank_code, '은행정보')]" />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>출금통장번호</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="props.item.withdraw_acct_num"
                                        prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력"
                                        persistent-placeholder />
                                </template>
                                <template #r_name>
                                    <BaseQuestionTooltip :location="'top'" text="유보금미달알림 하한금"
                                            :content="`보유금액이 지정 하한금 미만으로 떨어지면 수신자 번호에 알림문자가 발송됩니다.<br>ex)<br>[${props.item.nick_name}] 실시간 이체 잔액이 부족하오니 충전부탁드립니다.<br><br>(현재: 9,870,000원)`" />
                                </template>
                                <template #r_input>
                                    <VTextField type="number" v-model="props.item.min_balance_limit"
                                            prepend-inner-icon="tabler-currency-won" placeholder="유보금미달 알림금"
                                            persistent-placeholder suffix="만원" />
                                </template>
                            </CreateHalfVColV2>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>
                                    <BaseQuestionTooltip :location="'top'" text="입금자 타입"
                                            :content="`별칭 선택일 경우 해당 금융 VAN의 별칭으로 입금되며<br>예금주 선택일 경우 각 대상의 예금주(가맹점, 영업점)으로 입금됩니다.`" />
                                </template>
                                <template #l_input>
                                    <BooleanRadio :radio="props.item.deposit_type" @update:radio="props.item.deposit_type = $event">
                                        <template #true>예금주</template>
                                        <template #false>별칭</template>
                                    </BooleanRadio>
                                </template>
                                <template #r_name>
                                    <BaseQuestionTooltip :location="'top'" text="별칭"
                                            :content="`사이트 내에서 관리될 별칭입니다.`" />
                                </template>
                                <template #r_input>
                                    <VTextField v-model="props.item.nick_name" 
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="10"/>
                                </template>
                            </CreateHalfVColV2>
                            
                            <VRow>
                                <VCol class="d-flex gap-4 pt-10">
                                    <VBtn type="button" style="margin-left: auto;"
                                        @click="update('/services/finance-vans', props.item, vForm, false)">
                                        {{ props.item.id == 0 ? "추가" : "수정" }}
                                        <VIcon end icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn type="button" color="error" v-if="props.item.id"
                                        @click="remove('/services/finance-vans', props.item, false)">
                                        삭제
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                    <VBtn type="button" color="warning" v-else @click="props.item.id = -1">
                                        입력란 제거
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCardItem>
                    </VCol>
                </div>
            </VForm>
        </AppCardActions>
    </VCol>
</template>
