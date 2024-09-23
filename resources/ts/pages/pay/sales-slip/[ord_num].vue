<script setup lang="ts">
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { salesSlip } from '@/views/pay/sales-slip';
import type { PayGateway, SalesSlip } from '@/views/types';


const route = useRoute()

const sales_slip = ref(<SalesSlip>({}))
const payment_gateways = ref(<PayGateway[]>[])
const window_code = ref(200)
const window_message = ref('')
const is_load = ref(false)
const card = ref()

const salesSlipDialog = ref()
const snackbar = <any>(inject('snackbar'))
    const { 
    provider_info, merchandise_info,
    supply_amount, vat, tax_free, total_amount,
    init, copySalesSlip, copyLink, downloadSalesSlip, getSalesSlip
} = salesSlip()

provide('salesSlipDialog', salesSlipDialog)

onMounted(async () => {
    const [_code, _message, _data] = await getSalesSlip(route.params.ord_num, Number(route.query.is_cancel))
    if(_code === 200) {
        sales_slip.value = {
            ..._data.merchandise,
            ..._data.transactions,
        }
        payment_gateways.value = [_data.payment_gateway]
        init(sales_slip.value, payment_gateways.value)
    }
    else {
        window_code.value = _code
        window_message.value = _message
        snackbar.value.show(window_message.value, 'error')
    }
    is_load.value = true
})
</script>
<template>
    <section class="result-wrapper" ref="card">
        <template v-if="is_load">
            <template v-if="window_code === 200">
                <div class="sales-slip-rect">
                    <VCard rounded>
                        <VCardText>
                            <VCardTitle style="text-align: center;">
                                <b>매출전표 영수증</b>
                            </VCardTitle>
                            <VCol class="text-primary font-weight-bold v-col-custom">
                                결제 정보
                            </VCol>
                            <VTable class="text-no-wrap" style="width: 700px;">
                                <tbody>
                                    <tr>
                                        <th class='list-square'>
                                            <span>결제수단</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ module_types.find(obj => obj.id === sales_slip?.module_type)?.title }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>거래상태</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ sales_slip?.is_cancel ? "취소" : '승인' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>승인일시</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ sales_slip?.trx_dttm }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>취소일시</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square text-error'>
                                            <span>
                                                {{ sales_slip?.cxl_dttm }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>발급사</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ sales_slip?.issuer }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>매입사</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ sales_slip?.acquirer }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>할부개월</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ installments.find(inst => inst['id'] === parseInt(sales_slip?.installment as string))?.title }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>구매자명</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ sales_slip?.buyer_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>카드번호</span>
                                        </th>
                                        <td colspan="3" class='list-square'>
                                            <span>
                                                {{ sales_slip?.card_num }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>상품명</span>
                                        </th>
                                        <td colspan="3" class='list-square'>
                                            <span>
                                                {{ sales_slip?.item_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>과세금액</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ supply_amount.toLocaleString() }}원
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>부가세액</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ vat.toLocaleString() }}원
                                            </span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class='list-square'>
                                            <span>면세액</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ tax_free.toLocaleString() }}원
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>총결제금액</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <b :class="sales_slip?.is_cancel ? 'text-error big-font' : 'text-primary big-font'" :style="sales_slip?.is_cancel ? 'text-decoration: line-through;' : ''">
                                                {{ total_amount.toLocaleString() }}원
                                            </b>
                                        </td>
                                    </tr>
                                </tbody>
                            </VTable>
                            <br>
                            <VCol class="text-primary font-weight-bold v-col-custom">
                                판매자(가맹점) 정보
                            </VCol>
                            <VTable class="text-no-wrap" style="width: 700px;">
                                <tbody>
                                    <tr>
                                        <th class='list-square'>
                                            <span>상호</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ merchandise_info?.company_name }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>
                                                대표자명
                                            </span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ merchandise_info?.rep_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>
                                                사업자번호
                                            </span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ merchandise_info?.business_num }}
                                            </span>
                                        </td>                            
                                        <th class='list-square'>
                                            <span>전화번호</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ merchandise_info?.phone_num }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>주소</span>
                                        </th>
                                        <td colspan="3" class='list-square'>
                                            <span>
                                                {{ merchandise_info?.addr }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </VTable>
                            <br>
                            <VCol class="text-primary font-weight-bold v-col-custom">
                                공급자(결제대행사) 정보
                            </VCol>
                            <VTable class="text-no-wrap" style="width: 700px;">
                                <tbody>
                                    <tr>
                                        <th class='list-square'>
                                            <span>상호</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ provider_info?.company_name }}
                                            </span>
                                        </td>
                                        <th class='list-square'>
                                            <span>
                                                대표자명
                                            </span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ provider_info?.rep_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>사업자번호</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ provider_info?.business_num }}
                                            </span>
                                        </td>                            
                                        <th class='list-square'>
                                            <span>전화번호</span>
                                        </th>
                                        <td style="width: 5em;" class='list-square'>
                                            <span>
                                                {{ provider_info?.phone_num }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class='list-square'>
                                            <span>주소</span>
                                        </th>
                                        <td colspan="3" class='list-square'>
                                            <span>{{ provider_info?.addr }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </VTable>
                            <br>
                            <VCol style="font-size: 0.9em;">
                                신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의거하여 발행되었으며 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.
                            </VCol>
                        </VCardText>
                    </VCard>
                </div>
                <VCard rounded>
                    <VCardText>
                        <div class="button-container action-container">
                            <VBtn size="small" @click="copyLink(sales_slip)" color="warning" 
                                :style="`margin-right: 1em;`">
                                링크복사
                                <VIcon end icon="tabler:copy" />
                            </VBtn>
                            <VBtn size="small" @click="copySalesSlip(sales_slip, card)"
                                :style="`margin-right: 1em;`">
                                영수증복사
                                <VIcon end icon="tabler:copy" />
                            </VBtn>
                            <VBtn size="small" @click="downloadSalesSlip(sales_slip, card)" color="secondary">
                                다운로드
                                <VIcon end icon="material-symbols:download" />
                            </VBtn>
                        </div>
                    </VCardText>
                </VCard>
            </template>
            <VCard rounded v-else>
                <VCardText>
                    <VRow class="match-height" >
                        <VCol style="padding: 3em; text-align: center;">
                            <div style="text-align: center;">
                                <VIcon size="40" icon="line-md:emoji-frown-twotone" color="error"/>                        
                            </div>
                            <br>
                            <h3 style="text-align: center;">매출전표창을 사용할 수 없습니다. </h3>
                            <div style=" padding: 1em;text-align: center;">
                                <h4>- {{ window_message }} -</h4>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </template>
        <VCard rounded v-else>
            <VCardText>
                <VRow class="match-height">
                    <VCol style="padding: 3em; text-align: center;">
                        <b style="text-align: center;">매출전표을 로딩하고 있습니다...</b>
                        <div style=" padding: 1em;text-align: center;">
                            <VIcon size="40" icon="svg-spinners:3-dots-move" style="margin-right: 0.5em;"/>
                            <VIcon size="40" icon="svg-spinners:3-dots-move" />
                        </div>
                    </VCol>
                </VRow>                
            </VCardText>
        </VCard>
    </section>
</template>
<style scoped>
.result-wrapper {
  inline-size: 700px;
  margin-inline: auto;
}

@media (max-width: 700px) {
  .result-wrapper {
    inline-size: 100%;
  }

  th {
    inline-size: 3em;
  }

  th > span {
    font-size: 0.9em;
  }
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
  border-block-end: 3px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-block-start: 3px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-inline-end: 3px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

:deep(.v-table__wrapper > table) {
  table-layout: fixed;
}

th {
  background-color: rgb(var(--v-theme-primary)) !important;
  color: white !important;
  inline-size: 4em;
}

.list-square span {
  overflow-wrap: break-word;
  white-space: normal;
}

</style>
