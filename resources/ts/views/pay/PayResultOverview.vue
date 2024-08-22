<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { PayGateway, SalesSlip } from '@/views/types';
import { axios } from '@axios';

const route = useRoute()

const sales_slip = ref(<SalesSlip>({}))
const payment_gateways = ref(<PayGateway[]>[])

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesSlipDialog = ref()

const home = () => {
    location.href = '/pay/' + route.params.window + '/window'
}
const businessNumMasking = () => {
    if(sales_slip.value.business_num?.length as number > 9) {
        const bsin_num = sales_slip.value.business_num?.replace(/\D/g, '') as string
        sales_slip.value.business_num = bsin_num.slice(0, 3) + "-" + bsin_num.slice(3, 5) + "-" + bsin_num.slice(5)
    }
}

onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/pay/' + route.params.window, {
            params : {
                pc: route.query.pc
            }
        })
        payment_gateways.value = [res.data.payment_gateway]
        sales_slip.value = {
            ...res.data.merchandise,
            ...route.query,
        }
        sales_slip.value.pg_id       = res.data.payment_gateway.id
        sales_slip.value.is_cancel   = Number(route.query.is_cancel ?? false)
        sales_slip.value.trx_dttm    = (route.query.trx_dttm ?? new Date()) as string
        sales_slip.value.amount      = Number(sales_slip.value.amount) 
        sales_slip.value.module_type = res.data.payment_module.module_type 
        businessNumMasking()
    }
    catch (e: any) {
        sales_slip.value.result_cd = e.response.data.code
        sales_slip.value.result_msg = e.response.data.message
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }

    if(sales_slip.value?.result_cd === "0000")
        salesSlipDialog.value.show(sales_slip.value)
})
</script>
<template>
    <section class="result-wrapper">
        <VCard rounded>
            <VCardText>
                <div id="pay-container">
                    <div style="text-align: center;">
                        <b v-if="sales_slip?.result_cd !== '0000'">
                            결제를 실패하였습니다 
                            <VIcon size="24" icon='line-md:emoji-frown-twotone' color='error'/>
                            <br>
                            하단 실패사유를 확인해주세요.
                        </b>
                        <b v-else>
                            결제를 성공하였습니다! 
                            <VIcon size="24" icon='line-md:check-all' color='success'/>
                            <br>
                            하단 결제정보를 확인해주세요.
                        </b>
                    </div>
                    <VCard flat rounded>
                        <VCardText>
                            <VDivider />
                            <div>
                                <VTable v-if="sales_slip?.result_cd !== '0000'" style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">결과코드</th>
                                        <td class="padding">{{ sales_slip?.result_cd }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">에러 메세지</th>
                                        <td class="padding"><span v-html="sales_slip?.result_msg"></span></td>
                                    </tr>
                                </VTable>
                                <VTable v-else style="margin: 3em 0; text-align: center;">
                                    <tr>
                                        <th class="padding">상품명</th>
                                        <td class="padding">{{ sales_slip.item_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">금액</th>
                                        <td class="padding">{{ sales_slip.amount }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">구매자명</th>
                                        <td class="padding">{{ sales_slip.buyer_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">거래일시</th>
                                        <td class="padding">{{ sales_slip.trx_dttm }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">카드번호</th>
                                        <td class="padding">{{ sales_slip.card_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">승인번호</th>
                                        <td class="padding">{{ sales_slip.appr_num }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">매입사</th>
                                        <td class="padding">{{ sales_slip.acquirer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">발급사</th>
                                        <td class="padding">{{ sales_slip.issuer }}</td>
                                    </tr>
                                    <tr>
                                        <th class="padding">할부기간</th>
                                        <td class="padding">{{ installments.find(obj => obj.id == sales_slip.installment)?.title }}</td>
                                    </tr>
                                </VTable>
                            </div>
                            <VDivider />
                        </VCardText>
                        <VRow no-gutters style="display: flex; flex-direction: row; justify-content: space-evenly;">
                            <VCol cols="5" style="padding: 0;">
                                <VBtn block @click="home()" color="warning">
                                    결제창으로
                                </VBtn>
                            </VCol>
                            <VCol cols="5" style="padding: 0;" v-if="sales_slip?.result_cd === '0000'">
                                <VBtn block @click="salesSlipDialog.show(sales_slip)">
                                    영수증 보기
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VCard>
                </div>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesSlipDialog" :pgs="payment_gateways" :key="payment_gateways.length"/>
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
