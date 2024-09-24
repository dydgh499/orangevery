<script lang="ts" setup>
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { salesSlip } from '@/views/pay/sales-slip';
import type { PayGateway, SalesSlip } from '@/views/types';
import background from '@images/salesslip/background.png';
import cancel from '@images/salesslip/cancel.png';

interface Props {
    pgs: PayGateway[],
}

const reload_mode = ref(false)
const props = defineProps<Props>()

const { 
    provider_info, merchandise_info,
    supply_amount, vat, tax_free, total_amount,
    init, copySalesSlip, copyLink, downloadSalesSlip 
} = salesSlip()

const visible = ref(false)
const trans = ref<SalesSlip>()

const card = ref()
const thickness = ref(3)

const updateThickness = () => {
    thickness.value = window.innerWidth <= 500 ? 2 : 3;
};

const show = (item: SalesSlip, _reload_mode:boolean=false) => {
    reload_mode.value = _reload_mode
    trans.value = item
    init(trans.value, props.pgs)
    visible.value = true
}

const close = () => {
    visible.value = !visible.value
    if(reload_mode.value)
        location.reload()
}

onMounted(() => {
    window.addEventListener('resize', updateThickness);
    updateThickness(); // 초기 상태 설정
});

onUnmounted(() => {
    window.removeEventListener('resize', updateThickness);
});
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" style="box-shadow: 0 !important;" max-width="450">
        <section class="result-wrapper" ref="card">
            <div class="sales-slip-rect">
                <VCard class="sales-slip-background" rounded :style="`background-image: url(${background});`">
                    <VCardText>
                        <br>
                        <h3 style="text-align: center;">매출전표 영수증</h3>
                        <h3 :style="`padding: 12px ${$vuetify.display.smAndDown ? '12px' : '0px'};`">결제정보</h3>
                        <VDivider :thickness="thickness" class="mb-2" />
                        <table class='sales-slip-table text-no-wrap'>
                            <tbody>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>결제수단</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ module_types.find(obj => obj.id === trans?.module_type)?.title }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>거래상태</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.is_cancel ? "취소" : '승인' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>승인일시</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.trx_dttm }}</span>
                                    </td>
                                </tr>
                                <tr v-if="trans?.is_cancel">
                                    <td class="sales-slip-title">
                                        <span>취소일시</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.cxl_dttm }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>발급사</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.issuer }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>매입사</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.acquirer }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>카드번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.card_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>할부개월</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>
                                            {{ installments.find(inst => inst['id'] === parseInt(trans?.installment as
                                            string))?.title
                                            }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>구매자명</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.buyer_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>상품명</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.item_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>승인번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ trans?.appr_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>과세금액</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ supply_amount.toLocaleString() }} 원</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>부가세액</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ vat.toLocaleString() }} 원</span>
                                    </td>
                                </tr>
                                <tr v-if="trans?.tax_category_type === 1">
                                    <td class="sales-slip-title">
                                        <span>면세액</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ tax_free.toLocaleString() }} 원</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>총결제금액</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <h3 class="text-primary"
                                            :style="trans?.is_cancel ? 'text-decoration: line-through;' : ''"
                                        >
                                            {{ total_amount.toLocaleString() }} 원
                                        </h3>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <h3 :style="`padding: 12px ${$vuetify.display.smAndDown ? '12px' : '0px'};`">판매자(가맹점) 정보</h3>
                        <VDivider :thickness="thickness" class="mb-2" />
                        <table class='sales-slip-table'>
                            <tbody>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>상호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ merchandise_info?.company_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>대표자명</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ merchandise_info?.rep_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>사업자번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ merchandise_info?.business_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>전화번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ merchandise_info?.phone_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>주소</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ merchandise_info?.addr }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <h3 :style="`padding: 12px ${$vuetify.display.smAndDown ? '12px' : '0px'};`">공급자(결제대행사) 정보</h3>
                        <VDivider :thickness="thickness" class="mb-2" />
                        <table class='sales-slip-table'>
                            <tbody>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>상호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ provider_info?.company_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>대표자명</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ provider_info?.rep_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>사업자번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ provider_info?.business_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>전화번호</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ provider_info?.phone_num }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sales-slip-title">
                                        <span>주소</span>
                                    </td>
                                    <td class="sales-slip-content">
                                        <span>{{ provider_info?.addr }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <VDivider :thickness="1" />
                        <VCol style="font-size: 0.9em;">
                            신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의거하여 발행되었으며 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.
                        </VCol>
                        <VDivider :thickness="1" class="mb-2" />
                        <img :src="cancel" class="cancel-img" v-show="trans?.is_cancel">
                    </VCardText>
                </VCard>
            </div>
        </section>
        <div class="action-container" :style="`width: ${$vuetify.display.smAndDown ? '100%' : '90%'}`">
            <VBtn size="small" @click="copyLink(trans)" color="warning">
                링크복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <VBtn size="small" @click="copySalesSlip(trans, card)">
                영수증복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <VBtn size="small" @click="downloadSalesSlip(trans, card)" color="secondary">
                다운로드
                <VIcon end icon="material-symbols:download" />
            </VBtn>
        </div>
        <DialogCloseBtn @click="close()" 
            v-if="$vuetify.display.smAndDown === false"
            style="inset-block-start: -0.5em;"/>
    </VDialog>
</template>
<style scoped>
* {
  -webkit-font-variant: normal !important;
  font-variant: normal !important;
  font-variant-ligatures: none !important;
  word-break: normal !important;
}

section::-webkit-scrollbar { block-size: 0; inline-size: 0; }

.result-wrapper {
  padding: 0.3em;
  background: rgb(255, 255, 255, 0%) !important;
  color: rgba(51, 48, 60, 68%) !important;
  inline-size: 450px;
  margin-inline: auto;
  overflow-y: auto;
}

.sales-slip-background {
  border: 0 !important;
  background-color: rgba(0, 0, 0, 0%);
  background-repeat: no-repeat;
  background-size: cover;
  min-block-size: 71em;
}

.sales-slip-table {
  border-collapse: separate;
  border-spacing: 8px 5px;
  inline-size: 100%;
}

.sales-slip-title {
  inline-size: 30%;
}

.cancel-img {
  position: absolute;
  inline-size: 55%;
  inset-block-start: 48%;
  inset-inline-start: 23%;
}

.action-container {
  position: absolute;
  z-index: 9999;
  display: flex;
  justify-content: space-around;
  inset-block-start: -1em;
}

@media (max-width: 500px) {
  :deep(.v-card-text) {
    padding: 12px;
  }

  .result-wrapper {
    inline-size: 100%;
    padding-block-start: 1.5em;
  }

  .action-container {
    justify-content: space-between;
    font-size: 0.9em !important;
  }

  .sales-slip-title {
    padding-inline: 8px;
  }

  .cancel-img {
    inset-block-start: 45%;
  }

  * {
    font-size: 14px;
  }
}
</style>
