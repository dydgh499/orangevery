<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import type { SalesSlip } from '@/views/types'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { axios } from '@axios'

const route = useRoute()
const result_cd = route.query.result_cd
const result_msg = route.query.result_msg
const pmod_id = route.query.pmod_id

const salesslip = ref()
const sale_slip = ref(<SalesSlip>({}))

sale_slip.value.acquirer = (route.query.acquirer ?? '') as string
sale_slip.value.issuer = (route.query.issuer ?? '') as string
sale_slip.value.amount = (route.query.amount ?? 0) as number
sale_slip.value.buyer_name = (route.query.buyer_name ?? '') as string
sale_slip.value.card_num = (route.query.card_num ?? '') as string
sale_slip.value.item_name = (route.query.item_nm ?? '') as string
sale_slip.value.appr_num = (route.query.appr_num ?? '') as string
sale_slip.value.installment = (route.query.instment ?? 0) as number
sale_slip.value.trx_dttm = (route.query.trx_dttm ?? new Date()) as Date
sale_slip.value.is_cancel = Boolean(route.query.is_cancel ?? false)

if(pmod_id) {
    axios.get('/api/v1/manager/pay-modules/'+pmod_id+'/sales-slip')
    .then(r => { 
        sale_slip.value.mcht = {
            id: 0,
            addr: r.data.addr,
            business_num: r.data.business_num,
            resident_num: r.data.resident_num,
            mcht_name: r.data.mcht_name,
            nick_name: r.data.nick_name,
            is_show_fee: r.data.is_show_fee,
            use_saleslip_prov: r.data.use_saleslip_prov,
            use_saleslip_sell: r.data.use_saleslip_sell,
            user_name: '',
        }
        salesslip.value.show(sale_slip.value)
    })
    .catch(e => { console.log(e) })
}
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <VRow class="match-height">
                    <VCol cols="12" md="12" class="d-flex justify-center align-center">
                        <div style="width: 700px;">
                            <br>
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
                                                <td class="padding">{{ result_msg }}</td>
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
                            </VCard>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesslip" />
    </section>
</template>
<style scoped>
.padding {
  padding: 0.5em;
}
</style>
