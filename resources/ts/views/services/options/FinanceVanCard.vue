<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/options/useStore'
import type { FinanceVan } from '@/views/types'
import { banks } from '@/views/users/useStore'
import { HistoryTargetNames } from '@core/enums'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: FinanceVan,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))

const { finance_companies } = useStore()
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
                                <template #l_name>
                                    <BaseQuestionTooltip :location="'top'" text="별칭"
                                            :content="`사이트 내에서 관리될 별칭입니다.`" />
                                </template>
                                <template #l_input>
                                    <VTextField v-model="props.item.nick_name" 
                                        variant="underlined"
                                        prepend-inner-icon="twemoji-spiral-notepad" maxlength="10"/>
                                </template>
                                <template #r_name>금융 VAN</template>
                                <template #r_input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.finance_company_num"
                                        density="compact" :items="[{id:null, title:'선택안함'}].concat(finance_companies)"
                                        variant="underlined"
                                        eager item-title="title" item-value="id" :rules="[requiredValidatorV2(props.item.finance_company_num, '금융 VAN')]" />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>API KEY</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="props.item.api_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                        variant="underlined"
                                        persistent-placeholder />
                                </template>
                                <template #r_name>SUB KEY</template>
                                <template #r_input>
                                    <VTextField type="text" v-model="props.item.sub_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="SUB KEY 입력"
                                        variant="underlined"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>ENC KEY</template>
                                <template #l_input>
                                        <VTextField type="text" v-model="props.item.enc_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="ENC KEY 입력"
                                            variant="underlined"
                                            persistent-placeholder />
                                </template>
                                <template #r_name>IV</template>
                                <template #r_input>
                                        <VTextField type="text" v-model="props.item.iv"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="Initial vector 입력"
                                            variant="underlined"
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
                                    <VTextField type="text" v-model="props.item.corp_name" prepend-inner-icon="ph-building"
                                        variant="underlined"
                                        placeholder="기관명 입력" persistent-placeholder />
                                </template>
                                <template #r_name>기관코드</template>
                                <template #r_input>
                                    <VTextField type="text" v-model="props.item.corp_code"
                                        prepend-inner-icon="ph:share-network" placeholder="기관코드 입력"
                                        variant="underlined"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>은행</template>
                                <template #l_input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.bank_code"
                                        :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                                        label="은행 선택" item-title="title" item-value="code" persistent-hint single-line
                                        variant="underlined"
                                        :hint="getAcctBankName()"
                                        :rules="[requiredValidatorV2(props.item.bank_code, '은행정보')]" />
                                </template>
                                <template #r_name>출금통장번호</template>
                                <template #r_input>
                                    <VTextField type="text" v-model="props.item.withdraw_acct_num"
                                        prepend-inner-icon="ri-bank-card-fill" placeholder="계좌번호 입력"
                                        variant="underlined"
                                        persistent-placeholder />
                                </template>
                            </CreateHalfVColV2>
                            <VRow>
                                <VCol class="pt-10" style="text-align: end;">
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: auto;"
                                        color="secondary" 
                                        variant="tonal"
                                        @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['services/finance-vans'])">
                                        이력
                                        <VIcon end size="20" icon="tabler:history" />
                                    </VBtn>  
                                    <VBtn 
                                        style="margin-left: 1em;"
                                        @click="update('/services/finance-vans', props.item, vForm, false)">
                                        {{ props.item.id == 0 ? "추가" : "수정" }}
                                        <VIcon end size="20" icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: 1em;"
                                        color="error"
                                        @click="remove('/services/finance-vans', props.item, false)">
                                        삭제
                                        <VIcon end size="20" icon="tabler-trash" />
                                    </VBtn>
                                    <VBtn v-else
                                        style="margin-left: 1em;"
                                        color="warning"
                                        @click="props.item.id = -1">
                                        입력란 제거
                                        <VIcon end size="20" icon="tabler-trash" />
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
