<script lang="ts" setup>
import DialogHalfVCol from '@/layouts/utils/DialogHalfVCol.vue'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import type { SalesSlip, PayGateway, BeforeBrandInfo } from '@/views/types'
import html2canvas from "html2canvas"
import cancel from '@images/salesslip/cancel.png'
import background from '@images/salesslip/background.jpg'
import corp from '@corp'

interface Props {
    pgs: PayGateway[],
}
const props = defineProps<Props>()

const snackbar = <any>(inject('snackbar'))
const visible = ref(false)
const provider_info = ref<BeforeBrandInfo>()
const trans = ref<SalesSlip>()
const pg = ref<PayGateway>()
const card = ref(null)
const thickness = ref(3)

const supply_amount = ref(0)
const vat = ref(0)
const tax_free = ref(0)
const total_amount = ref(0)


const updateThickness = () => {
    if (window.innerWidth <= 500)
        thickness.value = 2;
    else
        thickness.value = 3;
};

const getVat = () => {
    return Math.round(trans.value?.amount as number / 1.1)
}

const copySalesSlip = () => {
    snackbar.value.show('영수증을 복사하고있습니다..', 'success')
    if (card.value) {
        html2canvas(document.getElementsByClassName('sales-slip-rect')[0], { useCORS: true, removeContainer: true }).then(canvas => {
            canvas.toBlob(blob => navigator.clipboard.write([new ClipboardItem({ "image/png": blob as Blob })]))
            snackbar.value.show('영수증이 클립보드에 복사되었습니다.', 'success')
        })
    }
}

const getProviderInfo = (): BeforeBrandInfo => {
    if (corp.pv_options.paid.use_before_brand_info) {
        const trx_dt = new Date(trans.value?.trx_dt as string)
        const before_brand_info = corp.before_brand_infos.find(obj => new Date(obj.apply_e_dt) >= trx_dt && new Date(obj.apply_s_dt) <= trx_dt)
        if(before_brand_info) {
            return <BeforeBrandInfo>({
                company_name: before_brand_info?.company_name,
                business_num: before_brand_info?.business_num,
                rep_name: before_brand_info?.rep_name,
                addr: before_brand_info?.addr,
            })
        }
    }
    if (Number(trans.value?.use_saleslip_prov)) {
        return <BeforeBrandInfo>({
            company_name: pg.value?.company_name,
            business_num: pg.value?.business_num,
            rep_name: pg.value?.rep_name,
            addr: pg.value?.addr,
        })
    }
    else {
        return <BeforeBrandInfo>({
            company_name: corp.company_name,
            business_num: corp.business_num,
            rep_name: corp.ceo_name,
            addr: corp.addr,
        })
    }
}

const show = (item: SalesSlip) => {
    trans.value = item
    pg.value = props.pgs.find(pg => pg['id'] === item.pg_id)
    if (trans.value.tax_category_type == 1) {
        supply_amount.value = 0
        vat.value = 0
        tax_free.value = trans.value.amount
        total_amount.value = trans.value.amount
    }
    else {
        supply_amount.value = getVat()
        vat.value = trans.value.amount as number - getVat()
        tax_free.value = 0
        total_amount.value = trans.value.amount
    }
    provider_info.value = getProviderInfo()
    visible.value = true
}
const cancelColor = computed(() => {
    return trans.value?.is_cancel ? 'text-decoration: line-through;' : ''
})

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
    <VDialog v-model="visible" class="v-dialog-sm" style="box-shadow: 0 !important;">
        <div class="button-container">
            <VBtn size="small" @click="copySalesSlip()" class="copy-btn">
                영수증 복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <!-- Dialog close btn -->
            <DialogCloseBtn @click="visible = !visible" />
        </div>
        <!-- Dialog Content -->
        <div ref="card">
            <VCard class="sales-slip-rect-container">
                <VCardText class="sales-slip-rect" :style="`background-image: url(${background});`">
                    <VCol class="font-weight-bold v-col-custom big-font text-center" style="padding-top: 24px;">
                        신용카드 영수증
                    </VCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        결제정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>결제수단</template>
                        <template #input>{{ trans?.acquirer }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>거래상태</template>
                        <template #input>{{ trans?.is_cancel ? "취소" : '승인' }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>거래일시</template>
                        <template #input>{{ trans?.trx_dttm }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>발급사</template>
                        <template #input>{{ trans?.issuer }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>매입사</template>
                        <template #input>{{ trans?.acquirer }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>카드번호</template>
                        <template #input>{{ trans?.card_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>할부개월</template>
                        <template #input>{{ installments.find(inst => inst['id'] === parseInt(trans?.installment as
                            string))?.title
                        }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>구매자명</template>
                        <template #input>{{ trans?.buyer_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>상품명</template>
                        <template #input>{{ trans?.item_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>승인번호</template>
                        <template #input>{{ trans?.appr_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>과세금액</template>
                        <template #input>{{ supply_amount.toLocaleString() }} 원</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>부가세액</template>
                        <template #input>{{ vat.toLocaleString() }} 원</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell" v-if="trans?.tax_category_type === 1">
                        <template #name>면세액</template>
                        <template #input> {{ tax_free.toLocaleString() }} 원</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell font-weight-bold">
                        <template #name>총결제금액</template>
                        <template #input>
                            <span class="text-primary big-font" :style="cancelColor">
                                {{ total_amount.toLocaleString() }} 원
                            </span>
                        </template>
                    </DialogHalfVCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        판매자(가맹점) 정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>상호</template>
                        <template #input>{{ trans?.use_saleslip_sell ?
                            corp.pv_options.free.sales_slip.merchandise.company_name : trans?.mcht_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자등록번호</template>
                        <template #input>{{ trans?.use_saleslip_sell ?
                            corp.pv_options.free.sales_slip.merchandise.business_num : trans?.business_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>대표자명</template>
                        <template #input>{{ trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.rep_name
                            : trans?.nick_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>주소</template>
                        <template #input>{{ trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.addr :
                            trans?.addr }}</template>
                    </DialogHalfVCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        공급자(결제대행사) 정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>상호</template>
                        <template #input>{{ provider_info?.company_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자등록번호</template>
                        <template #input>{{ provider_info?.business_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>대표자명</template>
                        <template #input>{{ provider_info?.rep_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell mb-2">
                        <template #name>주소</template>
                        <template #input>{{ provider_info?.addr }}</template>
                    </DialogHalfVCol>
                    <VDivider :thickness="1" />
                    <VCol style="font-size: 0.9em;">
                        신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의거하여 발행되었으며 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.
                    </VCol>
                    <VDivider :thickness="1" class="mb-2" />
                    <img :src="cancel" class="cancel-img" v-show="trans?.is_cancel">
                </VCardText>
            </VCard>
        </div>
    </VDialog>
</template>
<style scoped>
div {
  color: rgba(51, 48, 60, 68%) !important;
}

.sales-slip-rect-container {
  padding: 0.3em;
  background: rgb(255, 255, 255, 0%) !important;
  box-shadow: 0 0 0 0;
}

.sales-slip-rect {
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  min-block-size: 67em;
}

.cancel-img {
  position: absolute;
  inline-size: 55%;
  inset-block-start: 56%;
  inset-inline-start: 23%;
}

.big-font {
  font-size: 1.3em;
}

.cell {
  padding-block: 3px;
}

.button-container {
  display: flex;
  justify-content: flex-end;
}

.copy-btn {
  position: absolute;
  z-index: 9999;
  block-size: calc(var(--v-btn-height) + 3px);
  inset-block-start: -0.75em;
  inset-inline-end: 3em;
}

.v-col-custom {
  padding: 12px;
}

@media (max-width: 500px) {
  .sales-slip-rect {
    font-size: 0.5em;
  }

  .v-col-custom {
    padding: 8px;
  }

  .big-font {
    font-size: 1.5em;
  }

  .cancel-img {
    inset-block-start: 67%;
  }
}
</style>
