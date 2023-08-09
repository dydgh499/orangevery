<script setup lang="ts">
import { NotiFormat } from '@/views/types'

const visible = ref(false)
const noti = ref(<NotiFormat>({}))
const show = (_noti: NotiFormat) => {
    noti.value = _noti
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="노티 발송이력">
            <VCardText>
                <VRow no-gutters>
                    <VCol>
                        <VCol md="3">전송 URL</VCol>
                        <VCol md="9" style="font-weight: bold;">{{ noti.send_url }}</VCol>
                    </VCol>
                </VRow>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>명칭</th>
                            <th class='list-square'>Key</th>
                            <th class='list-square'>전송 값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>MID</td>
                            <td class='list-square'>mid</td>
                            <td class='list-square'>{{ noti.mid }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>TID</td>
                            <td class='list-square'>tid</td>
                            <td class='list-square'>{{ noti.tid }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>상위 PG사 거래번호</td>
                            <td class='list-square'>trx_id</td>
                            <td class='list-square'>{{ noti.trx_id }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>거래금액</td>
                            <td class='list-square'>amount</td>
                            <td class='list-square'>{{ noti.amount }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>주문번호</td>
                            <td class='list-square'>ord_num</td>
                            <td class='list-square'>{{ noti.ord_num }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>승인번호</td>
                            <td class='list-square'>appr_num</td>
                            <td class='list-square'>{{ noti.appr_num }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>상품명</td>
                            <td class='list-square'>item_name</td>
                            <td class='list-square'>{{ noti.item_name }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>구매자명</td>
                            <td class='list-square'>buyer_name</td>
                            <td class='list-square'>{{ noti.buyer_name }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>구매자 연락처</td>
                            <td class='list-square'>buyer_phone</td>
                            <td class='list-square'>{{ noti.buyer_phone }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>매입사</td>
                            <td class='list-square'>acquirer</td>
                            <td class='list-square'>{{ noti.acquirer }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>발급사</td>
                            <td class='list-square'>issuer</td>
                            <td class='list-square'>{{ noti.issuer }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>카드번호</td>
                            <td class='list-square'>card_num</td>
                            <td class='list-square'>{{ noti.card_num }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>할부</td>
                            <td class='list-square'>installment</td>
                            <td class='list-square'>{{ noti.installment }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>거래시간</td>
                            <td class='list-square'>pay_dttm</td>
                            <td class='list-square'>{{ noti.pay_dttm }}</td>
                        </tr>
                        <tr v-if="noti.is_cancel">
                            <td class='list-square'>취소시간</td>
                            <td class='list-square'>cxl_dttm</td>
                            <td class='list-square'>{{ noti.cxl_dttm }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>취소여부</td>
                            <td class='list-square'>is_cancel</td>
                            <td class='list-square'>{{ noti.is_cancel }}</td>
                        </tr>
                        <tr>
                            <td class='list-square'>예약필드</td>
                            <td class='list-square'>temp</td>
                            <td class='list-square'>{{ noti.temp }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
