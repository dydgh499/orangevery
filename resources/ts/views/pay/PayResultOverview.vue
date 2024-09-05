<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { SalesSlip } from '@/views/types';

interface Props {
    sales_slip: SalesSlip,
}
const props = defineProps<Props>()
const route = useRoute()
const salesSlipDialog = <any>(inject('salesSlipDialog'))

const home = () => {
    location.href = '/pay/' + route.params.window + '/window'
}
</script>
<template>
    <div id="pay-container">
        <div style="text-align: center;">
            <b v-if="props.sales_slip?.result_cd !== '0000'">
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
                    <VTable v-if="props.sales_slip?.result_cd !== '0000'" style="margin: 3em 0; text-align: center;">
                        <tr>
                            <th class="padding">결과코드</th>
                            <td class="padding">{{ props.sales_slip?.result_cd }}</td>
                        </tr>
                        <tr>
                            <th class="padding">에러 메세지</th>
                            <td class="padding"><span v-html="props.sales_slip?.result_msg"></span></td>
                        </tr>
                    </VTable>
                    <VTable v-else style="margin: 3em 0; text-align: center;">
                        <tr>
                            <th class="padding">상품명</th>
                            <td class="padding">{{ props.sales_slip?.item_name }}</td>
                        </tr>
                        <tr>
                            <th class="padding">금액</th>
                            <td class="padding">{{ props.sales_slip?.amount.toLocaleString() }} 원</td>
                        </tr>
                        <tr>
                            <th class="padding">구매자명</th>
                            <td class="padding">{{ props.sales_slip?.buyer_name }}</td>
                        </tr>
                        <tr>
                            <th class="padding">거래일시</th>
                            <td class="padding">{{ props.sales_slip?.trx_dttm }}</td>
                        </tr>
                        <tr>
                            <th class="padding">카드번호</th>
                            <td class="padding">{{ props.sales_slip?.card_num }}</td>
                        </tr>
                        <tr>
                            <th class="padding">승인번호</th>
                            <td class="padding">{{ props.sales_slip?.appr_num }}</td>
                        </tr>
                        <tr>
                            <th class="padding">매입사</th>
                            <td class="padding">{{ props.sales_slip?.acquirer }}</td>
                        </tr>
                        <tr>
                            <th class="padding">발급사</th>
                            <td class="padding">{{ props.sales_slip?.issuer }}</td>
                        </tr>
                        <tr>
                            <th class="padding">할부기간</th>
                            <td class="padding">{{ installments.find(obj => obj.id == props.sales_slip?.installment)?.title }}</td>
                        </tr>
                    </VTable>
                </div>
                <VDivider />
            </VCardText>
            <VRow no-gutters style="display: flex; flex-direction: row; justify-content: space-evenly;">
                <VCol cols="5" style="padding: 0;" v-if="props.sales_slip?.module_type !== 0">
                    <VBtn block @click="home()" color="warning">
                        결제창으로
                    </VBtn>
                </VCol>
                <VCol cols="5" style="padding: 0;" v-if="props.sales_slip?.result_cd === '0000'">
                    <VBtn block @click="salesSlipDialog.show(sales_slip)">
                        영수증 보기
                    </VBtn>
                </VCol>
            </VRow>
        </VCard>
    </div>
</template>
<style scoped>
.padding {
  padding: 0.5em;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
