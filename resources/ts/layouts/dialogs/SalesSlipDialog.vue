<script lang="ts" setup>
import DialogHalfVCol from '@/layouts/utils/DialogHalfVCol.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import type { SalesSlip, PayGateway } from '@/views/types'
import html2canvas from "html2canvas"
import cancel from '@images/salesslip/cancel.png'




//이미지로 복사기능
const { pgs } = useStore()

const snackbar  = <any>(inject('snackbar'))
const visible   = ref(false)
const trans     = ref<SalesSlip>()
const pg    = ref<PayGateway>()
const card  = ref(null)
const thickness = ref(3);

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
    if (card.value) {
        html2canvas(card.value).then(canvas => {
            canvas.toBlob(blob => navigator.clipboard.write([new ClipboardItem({ "image/png": blob })]))
            snackbar.value.show('영수증이 클립보드에 복사되었습니다.')
        })
    }
}
const show = (item: SalesSlip) => {
    trans.value = item
    pg.value = pgs.find(pg => pg['id'] === item.pg_id)
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
    <VDialog v-model="visible" class="v-dialog-sm">
        <div class="button-container">
            <VBtn size="small" @click="copySalesSlip()" class="copy-btn">
                영수증 복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <!-- Dialog close btn -->
            <DialogCloseBtn @click="visible = !visible" />
        </div>
        <!-- Dialog Content -->
        <VCard>
            <div ref="card">
                <VCardText class="sales-slip-rect">
                    <VCol class="font-weight-bold v-col-custom big-font text-center">
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
                        <template #input>{{ trans?.is_cancel ? "승인" : '취소' }}</template>
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
                        <template #input>{{ installments.find(inst => inst['id'] === trans?.installment)?.title
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
                        <template #input>{{ getVat().toLocaleString() }} 원</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>부가세</template>
                        <template #input>{{ (trans?.amount as number - getVat()).toLocaleString() }} 원</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell font-weight-bold">
                        <template #name>총결제금액</template>
                        <template #input>
                            <span class="text-primary big-font" :style="cancelColor">
                                {{ trans?.amount.toLocaleString() }} 원
                            </span>
                        </template>
                    </DialogHalfVCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        판매자(가맹점) 정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>상호</template>
                        <template #input>{{ trans?.mcht.use_saleslip_sell ?  pg?.company_nm : trans?.mcht_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자번호</template>
                        <template #input>{{ trans?.mcht.use_saleslip_sell ? pg?.business_num : trans?.business_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>대표자명</template>
                        <template #input>{{ trans?.mcht.use_saleslip_sell ? pg?.rep_nm : trans?.nick_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>주소</template>
                        <template #input>{{ trans?.mcht.use_saleslip_sell ? pg?.addr : trans?.addr}}</template>
                    </DialogHalfVCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        공급자(결제대행사) 정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>상호</template>
                        <template #input>{{ trans?.mcht.use_saleslip_prov ? pg?.company_nm : trans?.mcht_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자번호</template>
                        <template #input>{{ trans?.mcht.use_saleslip_prov ? pg?.business_num : trans?.business_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>대표자명</template>
                        <template #input>{{ trans?.mcht.use_saleslip_prov ? pg?.rep_nm : trans?.nick_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell mb-2">
                        <template #name>주소</template>
                        <template #input>{{trans?.mcht.use_saleslip_prov ?  pg?.addr : trans?.addr }}</template>
                    </DialogHalfVCol>
                    <VDivider :thickness="1" />
                    <VCol style="font-size: 0.9em;">
                        신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의거하여 발행되었으며 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.
                    </VCol>
                    <VDivider :thickness="1" class="mb-2" />
                    <img :src="cancel" class="cancel-img" v-show="trans?.is_cancel">
                </VCardText>
            </div>
        </VCard>
    </VDialog>
</template>
<style scoped>
.cancel-img {
  position: absolute;
  inline-size: 55%;
  inset-block-start: 55%;
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
