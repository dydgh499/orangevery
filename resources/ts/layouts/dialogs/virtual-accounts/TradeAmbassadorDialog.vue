<script setup lang="ts">
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { StatusColorSetter } from '@/views/searcher';
import { getDepositsStatusColor } from '@/views/transactions/settle-histories/SettleHistory';
import { deposit_statuses } from '@/views/transactions/settle-histories/useMerchandiseStore';
import { SettlesHistory, Transaction, VirtualAccountHistory } from '@/views/types';
import { axios } from '@axios';

const errorHandler = <any>(inject('$errorHandler'))
const snackbar = <any>(inject('snackbar'))
const visible = ref(false)

const history = ref(<VirtualAccountHistory>({}))
const transactions = ref(<Transaction>({}))
const settle = ref(<SettlesHistory>({}))
const deposit = ref(<VirtualAccountHistory>({id: 0}))
const show = async (_histories: VirtualAccountHistory) => {
    history.value = _histories
    console.log(_histories)
    try {
        const url = '/api/v1/manager/virtual-accounts/histories/trade-ambassador'
        const res = await axios.get(url, {
            params: {
                id : _histories.id,
                level : _histories.level,
                settle_id : _histories.settle_id,
                trans_type : _histories.trans_type,
                deposit_type : _histories.deposit_type,
                trx_id : _histories.trx_id,
            }
        })
        transactions.value  = res.data.trans
        settle.value        = res.data.settle
        if(res.data.history)
            deposit.value = res.data.history

        visible.value = true
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }

    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="1400">
        <DialogCloseBtn @click="visible = false" />
        <VCard :title="`#${history.id} 거래대사`">
            <br>
            <VCardSubtitle style="margin-left: 1em;">
                <template v-if="history.trans_type === 0">
                    <span>
                        <VChip color='success'>
                            {{`#${history.id}`}}
                        </VChip>
                        는 하기와 같은 이력으로 입금처리 되었습니다.
                    </span>
                    <br>
                    <br>
                    <b>
                        입금금액: {{ Number(history.trans_amount).toLocaleString() }}원
                    </b>
                </template>
                <template v-else>
                    <span>
                        <VChip color='primary'>
                            {{`#${history.id}`}}
                        </VChip>
                        는 하기 입금이력으로 인해 출금처리 되었습니다.
                    </span>
                    <br>
                    <span style='font-size: 0.9em;'>(통신비 등 상계처리로 인해 출금금액과 입금금액이 동일하지 않을 수 있습니다.)</span>
                    <br>
                    <br>
                    <template v-if="deposit.id">
                        <b>
                            입금이력 NO: 
                            <VChip color='success'>
                                {{`#${deposit.id}`}}
                            </VChip>
                        </b>
                    </template>
                    <br>
                    <br>
                    <b>
                        출금금액: 
                        {{ Number(history.trans_amount).toLocaleString() }}원
                    </b>
                </template>
            </VCardSubtitle>
            <VCardText v-if="Object.keys(transactions).length">
                <VCardTitle>거래이력</VCardTitle>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square sub-headers' :colspan="1">가맹점 정보</th>
                            <th class='list-square sub-headers' :colspan="2">정산 정보</th>
                            <th class='list-square sub-headers' :colspan="9">거래 정보</th>
                            <th class='list-square sub-headers' :colspan="6">결제 정보</th>
                        </tr>
                        <tr>
                            <th class='list-square'>가맹점 상호</th>
                            <th class='list-square'>정산번호</th>
                            <th class='list-square'>정산금</th>

                            <th class='list-square'>거래타입</th>
                            <th class='list-square'>결제모듈 별칭</th>
                            <th class='list-square'>거래시간</th>
                            <th class='list-square'>취소시간</th>
                            <th class='list-square'>승인번호</th>
                            <th class='list-square'>거래금액</th>
                            <th class='list-square'>할부</th>
                            <th class='list-square'>매입사</th>
                            <th class='list-square'>카드번호</th>
                            
                            <th class='list-square'>MID</th>
                            <th class='list-square'>TID</th>
                            <th class='list-square'>발급사</th>
                            <th class='list-square'>구매자명</th>
                            <th class='list-square'>구매자 연락처</th>
                            <th class='list-square'>상품명</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>{{ transactions.mcht_name }}</td>

                            <td class='list-square'>
                                <VChip color='success'>
                                    #{{ transactions.mcht_settle_id }}
                                </VChip>
                            </td>
                            <td class='list-square'>
                                <b>
                                    {{ Number(transactions.mcht_settle_amount).toLocaleString() }}
                                </b>
                            </td>
                            <td class='list-square'>
                                <VChip
                                    :color="StatusColorSetter().getSelectIdColor(module_types.find(obj => obj.id === transactions.module_type)?.id)">
                                    {{ module_types.find(obj => obj.id === transactions.module_type)?.title }}
                                </VChip>
                            </td>
                            <td class='list-square'>{{ transactions.note }}</td>
                            <td class='list-square'>{{ transactions.trx_dttm }}</td>
                            <td class='list-square'>{{ transactions.cxl_dttm }}</td>
                            <td class='list-square'>{{ transactions.appr_num }}</td>
                            <td class='list-square'>{{ Number(transactions.amount).toLocaleString() }}</td>
                            <td class='list-square'>
                                {{ installments.find(inst => inst['id'] === transactions.installment)?.title }}
                            </td>
                            <td class='list-square'>{{ transactions.acquirer }}</td>
                            <td class='list-square'>{{ transactions.card_num }}</td>

                            <td class='list-square'>{{ transactions.mid }}</td>
                            <td class='list-square'>{{ transactions.tid }}</td>
                            <td class='list-square'>{{ transactions.issuer }}</td>
                            <td class='list-square'>{{ transactions.buyer_name }}</td>
                            <td class='list-square'>{{ transactions.buyer_phone }}</td>
                            <td class='list-square'>{{ transactions.item_name }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
            <VDivider  v-if="Object.keys(transactions).length"/>
            <VCardText v-if="Object.keys(settle).length">
                <VCardTitle>정산이력</VCardTitle>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square sub-headers' :colspan="1">가맹점 정보</th>
                            <th class='list-square sub-headers' :colspan="6">정산 정보</th>
                            <th class='list-square sub-headers' :colspan="12">거래 정보</th>
                        </tr>
                        <tr>
                            <th class='list-square'>가맹점 상호</th>

                            <th class='list-square'>NO.</th>
                            <th class='list-square'>정산액</th>
                            <th class='list-square'>이체금액</th>                            
                            <th class='list-square'>정산일</th>
                            <th class='list-square'>입금일</th>
                            <th class='list-square'>입금상태</th>

                            <th class='list-square'>승인액</th>
                            <th class='list-square'>승인건수</th>
                            <th class='list-square'>취소액</th>
                            <th class='list-square'>취소건수</th>
                            <th class='list-square'>매출액</th>
                            <th class='list-square'>거래 수수료</th>
                            <th class='list-square'>통신비</th>
                            <th class='list-square'>취소입금액</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>{{ settle.mcht_name }}</td>

                            <td class='list-square'>
                                <VChip color='success'>
                                    #{{ settle.id }}
                                </VChip>
                            </td>
                            <td class='list-square'><b>{{ Number(settle.settle_amount).toLocaleString() }}</b></td>
                            <td class='list-square'><b>{{ Number(settle.deposit_amount).toLocaleString() }}</b></td>
                            <td class='list-square'>{{ settle.settle_dt }}</td>
                            <td class='list-square'>{{ settle.deposit_dt }}</td>
                            <td class='list-square'>
                                <VChip :color="getDepositsStatusColor(settle.deposit_status)">
                                    {{ deposit_statuses.find(obj => obj.id === settle.deposit_status)?.title }}
                                </VChip>
                            </td>

                            <td class='list-square'>{{ Number(settle.appr_amount).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.appr_count).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.cxl_amount).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.cxl_count).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.total_amount).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.trx_amount).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.comm_settle_amount).toLocaleString() }}</td>
                            <td class='list-square'>{{ Number(settle.cancel_deposit_amount).toLocaleString() }}</td>

                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
