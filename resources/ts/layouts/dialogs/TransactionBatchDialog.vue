

<script lang="ts" setup>
import ChangeSettleDateDialog from '@/layouts/dialogs/transactions/ChangeSettleDateDialog.vue'
import { useRequestStore } from '@/views/request'
import { getUserLevel } from '@axios'

interface Props {
    selected_idxs: number[],
    store: any
    is_mcht: boolean,
}

const props = defineProps<Props>()
const changeSettleDateDialog = ref()
const visible = ref(false)

const { post } = useRequestStore()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))



const batchRetry = async (url: string) => {
    if (await alert.value.show('정말 일괄 재발송하시겠습니까?')) {
        const params = { selected: props.selected_idxs }
        const r = await post(url, params)
        if (r.status == 201)
            snackbar.value.show('성공하였습니다.', 'success')
        else
            snackbar.value.show(r.data.message, 'error')
        props.store.setTable()
    }
}

const changeSettleDay = () => {
    for (let i = 0; i < props.selected_idxs.length; i++) 
    {
        let trans = props.store.getItems.find(obj => obj.id === props.selected_idxs[i])
        if(trans?.mcht_settle_id) {
            snackbar.value.show('이미 정산이된 거래건을 선택하셨습니다.<br>정산이 완료된 건을 해제한 후 다시시도해주세요.', 'warning')
            return
        }
    }
    changeSettleDateDialog.value.show(props.selected_idxs)
}

const show = () => {
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <div>
        <VDialog v-model="visible" persistent style="max-width: 600px;">
            <DialogCloseBtn @click="visible = !visible" />
            <VCard title="매출 일괄작업">
                <VCardText>
                    <b>선택된 매출 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol cols="12" style="display: flex; justify-content: space-evenly;">
                            <VBtn prepend-icon="tabler-calculator" @click="changeSettleDay()" size="small" color="warning">
                                정산일 변경
                            </VBtn>
                            <template v-if="corp.pv_options.paid.use_noti">
                                <span style="margin: 0.25em 0;"></span>
                                <VBtn prepend-icon="tabler-calculator" @click="batchRetry('/api/v1/manager/transactions/batch-retry')" size="small">
                                    노티 재발송
                                </VBtn>
                            </template>
                            <span style="margin: 0.25em 0;"></span>
                            <VBtn prepend-icon="tabler-calculator" @click="batchRetry('/api/v1/manager/transactions/batch-self-retry')" v-if="getUserLevel() >= 50" size="small" color="error">
                                노티 자체 재발송
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VDialog>
        <ChangeSettleDateDialog ref="changeSettleDateDialog" />
    </div>
</template>
<style scoped>
.batch-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
