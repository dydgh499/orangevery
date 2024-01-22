<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { payResult } from '@/views/pay/pay'

const { sale_slip, pgs, result_cd, result_msg, getData } = payResult()
const salesslip = ref()

onMounted( async () => {
    await getData()
    salesslip.value.show(sale_slip)
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <div id="pay-container">
                    <div style="text-align: center;">
                        <b v-if="result_cd != '0000'">
                            결제를 실패하였습니다. 
                            <br>
                            하단 실패사유를 확인해주세요.
                        </b>
                        <b v-else>
                            결제를 성공하였습니다 !
                            <br>
                            하단 결제정보를 확인해주세요.
                        </b>
                    </div>
                    <VCard flat rounded>
                        <VCardText>
                            <VDivider />
                            <div>
                                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; margin-top: 24px;">
                                    <template #name>결과코드</template>
                                    <template #input>{{ result_cd }}</template>
                                </CreateHalfVCol>
                                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                    <template #name>
                                            <span v-if="result_cd != '0000'">에러 메세지</span>   
                                            <span v-else>응답 메세지</span> 
                                    </template>
                                    <template #input>{{ result_msg }}</template>
                                </CreateHalfVCol>
                                <template v-if="result_cd == '0000'">
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>상품명</template>
                                        <template #input>{{ sale_slip.item_name  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>금액</template>
                                        <template #input>{{ sale_slip.amount  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>구매자명</template>
                                        <template #input>{{ sale_slip.buyer_name  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>카드번호</template>
                                        <template #input>{{ sale_slip.card_num  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>승인번호</template>
                                        <template #input>{{ sale_slip.appr_num  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>매입사</template>
                                        <template #input>{{ sale_slip.acquirer  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>발급사</template>
                                        <template #input>{{ sale_slip.issuer  }}</template>
                                    </CreateHalfVCol>
                                    <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                        <template #name>할부기간</template>
                                        <template #input>{{ installments.find(obj => obj.id == sale_slip.installment)?.title  }}</template>
                                    </CreateHalfVCol>
                                </template>
                            </div>
                        </VCardText>
                    </VCard>
                </div>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesslip" :pgs="pgs"/>
    </section>
</template>
<style scoped>
.padding {
  padding: 0.5em;
}
</style>
