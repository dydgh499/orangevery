<script lang="ts" setup>
import corp from '@/plugins/corp';
import type { Popup } from '@/views/types';
import { axios, getUserLevel } from '@axios';
import { PopupEvent } from '@core/utils/popup';

const popup = ref(<Popup>({
    id: 0,
    visible: false,
    is_hide: false,
}))
const { setOpenStatus, init } = PopupEvent('collect-withdraw-danger/hide/')
const dangers = ref(<any[]>([]))

const getDangerCollectWithdraws = async (page: number, page_size: number) => {
    const _dangers:any[] = []
    const res = await axios.get('/api/v1/manager/transactions/settle/collect-withdraws/dangers', {
        params: {
            page: page, 
            page_size: page_size,
            search: ''
        }
    })
    if(res.data.count > 0) {
        _dangers.push(...res.data.data)
        const total_count = res.data.count

        const least = (total_count/page_size) - parseInt(total_count/page_size)
        const total_page = parseInt(total_count/page_size) + (least > 0 ? 1 : 0)
        const promises = []
        for (let i = 0; i < total_page-1; i++) {
            promises.push(getDangerCollectWithdraws(i+2, page_size));
        }

        const results = await Promise.all(promises)
        for (let i = 0; i < results.length; i++) {
            _dangers.push(...results[i].data.data)
        }
    }
    return _dangers
}
const setDangerCollectWithdraws = async () => {
    const page_size = 999
    if(corp.pv_options.paid.use_collect_withdraw && getUserLevel() >= 35) {
        init(popup.value)
        if(popup.value.visible) {
            popup.value.visible = false
            dangers.value = await getDangerCollectWithdraws(1, page_size);
            if( dangers.value.length)
                popup.value.visible = true
        }
    }
}
setDangerCollectWithdraws()

</script>
<template>
    <VDialog v-model="popup.visible" persistent max-width="900">
        <div class="button-container">
            <VCheckbox v-model="popup.is_hide" class="check-label not-open-today" label="오늘 안보기" />
            <DialogCloseBtn @click="setOpenStatus(popup)" />
        </div>
        <VCard title="모아서출금 이상 가맹점 발견">
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>가맹점 상호</th>
                            <th class='list-square'>총 매출액</th>
                            <th class='list-square'>총 정산액</th>
                            <th class='list-square'>총 취소수기입금</th>
                            <th class='list-square'>총 출금가능액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(danger, key) in dangers" :key="key">
                            <td class='list-square'>{{ danger.mcht_name }}</td>
                            <td class='list-square'>{{ danger.total_amount.toLocaleString() }}</td>
                            <td class='list-square'>{{ danger.settle_amount.toLocaleString() }}</td>
                            <td class='list-square'>{{ danger.cancel_deposit.toLocaleString() }}</td>
                            <td class='list-square text-error'>{{ danger.withdraw_able_amount.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
