<script setup lang="ts">
import { axios, getLevelByIndex } from '@axios'
import type { Merchandise } from '@/views/types'

interface Props {
    level: number,
    item: Merchandise,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const formatDate = <any>(inject('$formatDate'))
const errorHandler = <any>(inject('$errorHandler'))
const feeBookDialog = <any>(inject('feeBookDialog'))

const getSalesByClass = () => {
    const idx = getLevelByIndex(props.level)
    return {
        sales_id: props.item['sales' + idx + '_id'],
        sales_fee: parseFloat(props.item['sales' + idx + '_fee'] as string),
    }
}

const feeChangeRequest = async (type: string, apply_dt: string) => {
    let url = '/api/v1/manager/merchandises/fee-change-histories'
    let params = {}
    if (props.level < 0) {
        params = {
            apply_dt: apply_dt,
            trx_fee: parseFloat(props.item.trx_fee),
            hold_fee: parseFloat(props.item.hold_fee),
            mcht_id: props.item.id,
        }
        url += '/merchandises/' + type
    }
    else {
        params = Object.assign({
            apply_dt: apply_dt,
            level: props.level,
            mcht_id: props.item.id,
        }, getSalesByClass())
        url += '/salesforces/' + type
    }

    try {
        const r = await axios.post(url, params)
        snackbar.value.show('성공하였습니다.', 'success')
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}
const directFeeChange = async () => {
    if (await alert.value.show('정말 즉시적용하시겠습니까?'))
        await feeChangeRequest('direct-apply', formatDate(new Date))
}
const bookFeeChange = async () => {
    const apply_dt = await feeBookDialog.value.show()
    if(apply_dt !== '') {
        if (await alert.value.show('정말 예약적용하시겠습니까?'))
        await feeChangeRequest('book-apply', apply_dt)
    }
}
</script>
<template>
    <VCol cols="12" md="2" style="display: flex; flex-direction: row; justify-content: space-between;">
        <VBtn size="small" variant="tonal" @click="directFeeChange()" style='flex-grow: 1; margin: 0.25em 0.5em;'>
            즉시적용
        </VBtn>
        <VBtn size="small" variant="tonal" color="secondary" @click="bookFeeChange()"
            style='flex-grow: 1; margin: 0.25em 0.5em;'>
            예약적용
        </VBtn>
    </VCol>
</template>
