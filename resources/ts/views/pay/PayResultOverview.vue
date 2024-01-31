<script setup lang="ts">
import { useQuickViewStore } from '@/views/quick-view/useStore'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { payResult } from '@/views/pay/pay'

const { getEncryptParams, auths, simples } = useQuickViewStore()
const { sale_slip, pgs, result_cd, result_msg, getData, pmod_id } = payResult()
const salesslip = ref()
const pay_url = ref()

const home = () => {
    location.href = pay_url.value
}
const setPayUrl = () => {
    let pay_module = auths.find(obj => obj.id == Number(pmod_id))
    let type = 'auth'
    if(pay_module == undefined) {
        pay_module = simples.find(obj => obj.id == Number(pmod_id))
        type = 'simple'
    }
    if(pay_module) {
        const params = getEncryptParams(pay_module)
        pay_url.value = '/pay/' + type + "?e=" + params
    }
}

onMounted( async () => {
    setPayUrl()
    await getData()
    if(result_cd == "0000")
        salesslip.value.show(sale_slip.value)
})
</script>
<template>
    <section class="result-wrapper">
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
                                <VTable v-if="result_cd != '0000'" style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">결과코드</th>
                                        <td class="padding">{{ result_cd }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">에러 메세지</th>
                                        <td class="padding"><span v-html="result_msg"></span></td>
                                    </tr>
                                </VTable>
                                <VTable v-else style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">결과코드</th>
                                        <td class="padding">{{ result_cd }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">응답 메세지</th>
                                        <td class="padding">{{ result_msg }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">상품명</th>
                                        <td class="padding">{{ sale_slip.item_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">금액</th>
                                        <td class="padding">{{ sale_slip.amount }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">구매자명</th>
                                        <td class="padding">{{ sale_slip.buyer_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">카드번호</th>
                                        <td class="padding">{{ sale_slip.card_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">승인번호</th>
                                        <td class="padding">{{ sale_slip.appr_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">매입사</th>
                                        <td class="padding">{{ sale_slip.acquirer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">발급사</th>
                                        <td class="padding">{{ sale_slip.issuer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">할부기간</th>
                                        <td class="padding">{{ installments.find(obj => obj.id == sale_slip.installment)?.title }}</td>
                                    </tr>
                                </VTable>
                            </div>
                            <VDivider />
                        </VCardText>
                        <VRow no-gutters style="display: flex; flex-direction: row; justify-content: space-evenly;">
                            <VCol cols="5" style="padding: 0;" v-if="pay_url">
                                <VBtn block @click="home()">
                                    결제화면으로
                                </VBtn>
                            </VCol>
                            <VCol cols="5" style="padding: 0;" v-if="result_cd == '0000'">
                                <VBtn block @click="salesslip.show(sale_slip)">
                                    영수증 보기
                                </VBtn>
                            </VCol>
                        </VRow>
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

.result-wrapper {
  inline-size: 600px;
  margin-inline: auto;
}

@media (max-width: 700px) {
  .result-wrapper {
    inline-size: 100%;
  }
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
