<script lang="ts" setup>
import { requiredValidator, nullValidator } from '@validators'
import type { FinanceVan } from '@/views/types'
import { VForm } from 'vuetify/components'
import { useRequestStore } from '@/views/request'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

interface Props {
    item: FinanceVan,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { finance_companies, fin_types } = useStore()
const { update, remove } = useRequestStore()

</script>
<template>
    <AppCardActions action-collapsed :title="props.item.nick_name">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                <VCol cols="12" md="4">
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">금융 VAN 정보</VCardTitle>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>금융 VAN</template>
                                <template #input>                                    
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.finance_company_num" density="compact" variant="outlined"
                                        :items="finance_companies" label="금융 VAN 선택" :eager="true" 
                                        item-title="title" item-value="id"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>타입</template>
                                <template #input>                                    
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_type" density="compact" variant="outlined"
                                        :items="fin_types" label="타입 선택" :eager="true" 
                                        item-title="title" item-value="id"/>
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>API KEY</template>
                                <template #input>                                    
                                    <VTextField type="text" v-model="props.item.api_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
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
                        <VCardTitle style="margin-bottom: 1em;">연동 정보</VCardTitle>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>개발사 수수료</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.dev_fee"
                                            prepend-inner-icon="ph:share-network" placeholder="0.1"
                                            suffix="%"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>법인명</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.corp_name"
                                            prepend-inner-icon="ph-buildings" placeholder="법인명 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>법인코드</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.corp_code"
                                            prepend-inner-icon="ph:share-network" placeholder="법인코드 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>은행코드</template>
                                <template #input>
                                    <VTextField type="text" v-model="props.item.bank_code"
                                            prepend-inner-icon="ph:share-network" placeholder="은행코드 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
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
                        <VCardTitle style="margin-bottom: 1em;">문자 알림 정보</VCardTitle>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>SMS KEY</template>
                                <template #input>        
                                    <VTextField type="text" v-model="props.item.sms_key"
                                            prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>SMS ID</template>
                                <template #input>                                    
                                    <VTextField type="text" v-model="props.item.sms_id"
                                            prepend-inner-icon="tabler-building-store" placeholder="SMS ID 입력"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>발신 전화번호</template>
                                <template #input>                                    
                                    <VTextField type="number" v-model="props.item.sms_sender_phone"
                                            prepend-inner-icon="tabler-device-mobile" placeholder="07012345678"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>수신자 전화번호</template>
                                <template #input>                                    
                                    <VTextField type="number" v-model="props.item.sms_receive_phone"
                                            prepend-inner-icon="tabler-device-mobile" placeholder="01012345678"
                                            persistent-placeholder />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="5" :mdr="7">
                                <template #name>
                                    <BaseQuestionTooltip :location="'top'" text="유보금미달알림 상한금" 
                                    content="보유금액이 지정 상한금 미만으로 떨어지면, 수신자 전화번호에 알림이 전송됩니다."/>
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
                                <VBtn type="button" style="margin-left: auto;" @click="update('/services/finance-vans', props.item.id as number, props.item, vForm, false)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id" @click="remove('/services/finance-vans', props.item.id, false)">
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
