<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { useStore } from '@/views/services/pay-gateways/useStore'
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { Transaction, PayGateway } from '@/views/types'
import corp from '@corp'

//이미지로 복사기능
const { pgs, pss, settle_types } = useStore()

const visible = ref(false)
const trans = ref<Transaction>()
const pg = ref<PayGateway>()
const show = (item: Transaction) => {
    trans.value = item
    pg.value = pgs.find(pg => pg['id'] === item.pg_id)
    visible.value = true
}
defineExpose({
    show
});
const getVat = () => {
    return Math.round(trans.value?.amount as number/1.1)
}
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <!-- Dialog close btn --> 
        <VBtn type="secondary"  @click="">
            영수증 이미지 복사
            <VIcon end icon="tabler-plus" />
        </VBtn>
        <DialogCloseBtn @click="visible = !visible" />       
        <!-- Dialog Content -->
        <VCard>
            <VCardText>
                <VCol style="font-size: 1.3em;text-align: center;" class="font-weight-bold">
                    신용카드 영수증
                </VCol>
                <VCol class="text-primary font-weight-bold">
                    결제정보
                </VCol>
                <VDivider :thickness="3" class="mb-2"/>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>결제수단</template>
                    <template #input>{{ trans?.acquirer }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>거래상태</template>
                    <template #input>{{ trans?.is_cancel ? "승인" : '취소' }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>거래일시</template>
                    <template #input>{{ trans?.trx_dttm }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>발급사</template>
                    <template #input>{{ trans?.issuer }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>매입사</template>
                    <template #input>{{ trans?.acquirer }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>카드번호</template>
                    <template #input>{{ trans?.card_num }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>할부개월</template>
                    <template #input>{{ installments.find(inst => inst['id'] === trans?.installment)?.title }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>구매자명</template>
                    <template #input>{{ trans?.buyer_name }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>상품명</template>
                    <template #input>{{ trans?.item_name }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>승인번호</template>
                    <template #input>{{ trans?.appr_num }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>과세금액</template>
                    <template #input>{{ getVat().toLocaleString() }} 원</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>부가세</template>
                    <template #input>{{ (trans?.amount as number - getVat()).toLocaleString() }} 원</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell font-weight-bold">
                    <template #name>총결제금액</template>
                    <template #input>{{ trans?.amount.toLocaleString() }} 원</template>
                </CreateHalfVCol>
                <VCol class="text-primary font-weight-bold">
                    공급자(가맹점) 정보
                </VCol>
                <VDivider :thickness="3" class="mb-2"/>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>상호</template>
                    <template #input>{{ trans?.mcht_name }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>사업자번호</template>
                    <template #input>{{ trans?.business_num }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>대표자명</template>
                    <template #input>{{ trans?.nick_name }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>주소</template>
                    <template #input>{{ trans?.addr+" "+trans?.detail_addr as string }}</template>
                </CreateHalfVCol>
                <VCol class="text-primary font-weight-bold">
                    공급자(결제대행사) 정보
                </VCol>
                <VDivider :thickness="3" class="mb-2"/>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>상호</template>
                    <template #input>{{ pg?.company_nm }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>사업자번호</template>
                    <template #input>{{ pg?.business_num }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell">
                    <template #name>대표자명</template>
                    <template #input>{{ pg?.rep_nm }}</template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" class="cell mb-2">
                    <template #name>주소</template>
                    <template #input>{{ pg?.addr}}</template>
                </CreateHalfVCol>
                <VDivider :thickness="1" class="mb-2"/>
                <VCol>
                    신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의하여 발행되었으며, 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.
                </VCol>
                <VDivider :thickness="1" class="mb-2"/>
            </VCardText>

        </VCard>
    </VDialog>
</template>
<style scoped>
.cell {
  padding-block: 3px;
}
</style>
