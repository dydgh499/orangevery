<script lang="ts" setup>
import DialogHalfVCol from '@/layouts/utils/DialogHalfVCol.vue';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { salesSlip } from '@/views/pay/sales-slip';
import type { PayGateway, SalesSlip } from '@/views/types';
import background from '@images/salesslip/background.jpg';
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
    <VDialog v-model="visible" class="v-dialog-sm" style="max-width: 36em;box-shadow: 0 !important;">
        <div class="button-container action-container" 
            :style="`${$vuetify.display.smAndDown ? 'width: 105%; inset-block-start: -1em': 'width: 90%'}`">
            <VBtn size="small" @click="copyLink(trans)" color="warning" 
                :style="`margin-right: 1em;`">
                링크복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <VBtn size="small" @click="copySalesSlip(trans, card)"
                :style="`margin-right: 1em;`">
                영수증복사
                <VIcon end icon="tabler:copy" />
            </VBtn>
            <VBtn size="small" @click="downloadSalesSlip(trans, card)" color="secondary"
                :style="`margin-right: 1em;`">
                다운로드
                <VIcon end icon="material-symbols:download" />
            </VBtn>
        </div>
        <DialogCloseBtn @click="close()" 
                v-if="$vuetify.display.smAndDown === false"
                style="inset-block-start: 0.5em; inset-inline-end: 1.5em;"/>

        <div ref="card" style="max-width: 36em;overflow-y: auto;">
            <VCard class="sales-slip-rect-container" style="border: 0 !important;">
                <VCardText class="sales-slip-rect" :style="`background-image: url(${background});`">
                    <VCol class="font-weight-bold v-col-custom big-font text-center" :style="$vuetify.display.smAndDown ? '' : 'padding-top: 24px;'">
                        신용카드 영수증
                    </VCol>
                    <VCol class="text-primary font-weight-bold v-col-custom">
                        결제정보
                    </VCol>
                    <VDivider :thickness="thickness" class="mb-2" />
                    <DialogHalfVCol class="cell">
                        <template #name>결제수단</template>
                        <template #input>{{ module_types.find(obj => obj.id === trans?.module_type)?.title }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>거래상태</template>
                        <template #input>{{ trans?.is_cancel ? "취소" : '승인' }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>승인일시</template>
                        <template #input>{{ trans?.trx_dttm }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell" v-if="trans?.is_cancel">
                        <template #name>취소일시</template>
                        <template #input>{{ trans?.cxl_dttm }}</template>
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
                        <template #name>
                            <span style="margin-top: 0.25em;">총결제금액</span>
                        </template>
                        <template #input>
                            <span class="text-primary big-font" :style="trans?.is_cancel ? 'text-decoration: line-through;' : ''">
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
                        <template #input>
                            {{ merchandise_info?.company_name }}
                        </template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>대표자명</template>
                        <template #input>
                            {{ merchandise_info?.rep_name }}                            
                        </template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자번호</template>
                        <template #input>
                            {{ merchandise_info?.business_num }}
                        </template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>전화번호</template>
                        <template #input>
                            {{ merchandise_info?.phone_num }}
                        </template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>주소</template>
                        <template #input>
                            {{ merchandise_info?.addr }}                            
                        </template>
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
                        <template #name>대표자명</template>
                        <template #input>{{ provider_info?.rep_name }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>사업자번호</template>
                        <template #input>{{ provider_info?.business_num }}</template>
                    </DialogHalfVCol>
                    <DialogHalfVCol class="cell">
                        <template #name>전화번호</template>
                        <template #input>{{ provider_info?.phone_num }}</template>
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

.action-container {
  position: absolute;
  z-index: 9999;
  font-size: 0.8em;
}

.v-col-custom {
  padding: 12px;
}

@media (max-height: 900px) {
  .sales-slip-rect {
    font-size: 0.8em;
  }
}

@media (max-width: 500px) {
  .v-col-custom {
    padding: 8px;
  }

  .cancel-img {
    inset-block-start: 67%;
  }
}
</style>
