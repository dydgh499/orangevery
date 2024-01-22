<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import type { SalesSlip, PayGateway } from '@/views/types'

interface Props {
    sale_slip: SalesSlip,
    result_cd: string,
    result_msg: string,
    pgs: PayGateway[],
}
const props = defineProps<Props>()
const salesslip = ref()

onMounted(() => {
    salesslip.value.show(props.sale_slip)
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <div id="pay-container">
                    <div style="text-align: center;">
                        <b v-if="props.result_cd != '0000'">
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
                                <VTable v-if="result_cd != '0000'" style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">결과코드</th>
                                        <td class="padding">{{ props.result_cd }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">에러 메세지</th>
                                        <td class="padding"><span v-html="props.result_msg"></span></td>
                                    </tr>
                                </VTable>
                                <VTable v-else style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">결과코드</th>
                                        <td class="padding">{{ props.result_cd }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">응답 메세지</th>
                                        <td class="padding">{{ props.result_msg }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">상품명</th>
                                        <td class="padding">{{ props.sale_slip.item_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">금액</th>
                                        <td class="padding">{{ props.sale_slip.amount }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">구매자명</th>
                                        <td class="padding">{{ props.sale_slip.buyer_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">카드번호</th>
                                        <td class="padding">{{ props.sale_slip.card_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">승인번호</th>
                                        <td class="padding">{{ props.sale_slip.appr_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">매입사</th>
                                        <td class="padding">{{ props.sale_slip.acquirer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">발급사</th>
                                        <td class="padding">{{ props.sale_slip.issuer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">할부기간</th>
                                        <td class="padding">{{ installments.find(obj => obj.id == props.sale_slip.installment)?.title }}</td>
                                    </tr>
                                </VTable>
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
