<script lang="ts" setup>
import { requiredValidator, nullValidator } from '@validators'
import type { FinanceVan } from '@/views/types'
import { VForm } from 'vuetify/components'
import { useRequestStore } from '@/views/request'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { banks } from '@/views/users/useStore'
import { getUserLevel } from '@/plugins/axios';
import corp from '@corp';

interface Props {
    item: FinanceVan,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { finance_companies, fin_types } = useStore()
const { update, remove } = useRequestStore()

const bank = ref(<any>({ code: null, title: '선택안함' }))

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
    <AppCardActions action-collapsed :title="props.item.nick_name">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12" md="4">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">
                            <BaseQuestionTooltip :location="'top'" text="금융 VAN 정보" :content="'해당 정보는 보안상 '+corp.pv_options.auth.levels.dev_name+'만 보여집니다.'" />
                        </VCardTitle>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>금융 VAN</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.finance_company_num"
                                        density="compact" variant="outlined" :items="finance_companies" label="금융 VAN 선택"
                                        :eager="true" item-title="title" item-value="id" :rules="[requiredValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>타입</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_type"
                                        density="compact" variant="outlined" :items="fin_types" label="타입 선택" :eager="true"
                                        item-title="title" item-value="id" :rules="[requiredValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>API KEY</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.api_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>SUB KEY(HASH KEY)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.sub_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="SUB KEY 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>ENC KEY(암호화 키)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.enc_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="ENC KEY 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>Initial vector</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.iv"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="Initial vector 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                            <VCol>
                                <VTextarea v-model="props.item.nick_name" counter label="별칭(비고)"
                                    prepend-inner-icon="twemoji-spiral-notepad" />
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>


                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" md="4">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">
                            <BaseQuestionTooltip :location="'top'" text="연동 정보" :content="'해당 정보는 보안상 '+corp.pv_options.auth.levels.dev_name+'만 보여집니다.'"/>
                        </VCardTitle>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>{{ corp.pv_options.auth.levels.dev_name }} 수수료</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.dev_fee"
                                        prepend-inner-icon="ph:share-network" placeholder="0.1" suffix="%"
                                        persistent-placeholder :rules="[nullValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>기관명</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.corp_name" prepend-inner-icon="ph-buildings"
                                        placeholder="기관명 입력" persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7" v-if="getUserLevel() == 50">
                                <template #name>기관코드(GUID)</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.corp_code"
                                        prepend-inner-icon="ph:share-network" placeholder="기관코드 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>은행</template>
                                <template #input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="bank"
                                        :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                        prepend-inner-icon="ph-buildings" label="은행 선택"
                                        :hint="`${bank.title}, 은행 코드: ${bank.code ? bank.code : '000'} `" item-title="title"
                                        item-value="code" persistent-hint return-object single-line :rules="[nullValidator]"
                                        create />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3" v-if="getUserLevel() == 50">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>출금 통장번호</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.withdraw_acct_num"
                                        prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                    </VCardItem>
                </VCol>
                <VDivider :vertical="$vuetify.display.mdAndUp" />
                <VCol cols="12" md="4">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">
                            <BaseQuestionTooltip :location="'top'" text="문자 알림 정보"
                                :content="'예시)<br>[' + props.item.nick_name + '] 실시간 이체 잔액이 부족하오니 충전부탁드립니다.<br>(현재: 9,870,000원)'" />
                        </VCardTitle>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" text="유보금미달알림 하한금"
                                        content="보유금액이 지정 하한금 미만으로 떨어지면, 수신자 번호에 알림문자가 발송됩니다." />
                                </template>
                                <template #input>
                                    <VTextField type="number" v-model="props.item.min_balance_limit"
                                        prepend-inner-icon="tabler-currency-won" placeholder="유보금미달 알림금"
                                        persistent-placeholder suffix="만원" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
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
                            </VCol>
                        </VRow>
                    </VCardItem>
                </VCol>
            </div>
        </VForm>
    </AppCardActions>
</template>
