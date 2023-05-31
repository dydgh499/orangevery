<script setup lang="ts">
import { axios } from '@axios';

interface Props {
    class: number,
    sales_id: number | null,
    sales_fee: number,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = inject('$errorHandler');

const directFeeChange = async (_class: number, sales_id: number | null, sales_fee: number) => {
    if (await alert.value.show('정말 즉시적용하시겠습니까?')) {

    }
}
const bookFeeChange = async (_class: number, sales_id: number | null, sales_fee: number) => {
    if (await alert.value.show('정말 예약적용하시겠습니까? 명일 00시에 반영됩니다.')) {

    }
}
</script>
<template>
    <VCol cols="12" md="3" style="display: flex; flex-direction: row; justify-content: space-between;">
        <VBtn type="submit" size="small" variant="tonal"
            @click="directFeeChange(props.class, props.sales_id, props.sales_fee)"
            style='flex-grow: 1; margin: 0.25em 0.5em;'>
            즉시적용
            <VIcon end icon="tabler-direction-sign" />
        </VBtn>
        <VBtn type="submit" size="small" variant="tonal" color="secondary"
            @click="bookFeeChange(props.class, props.sales_id, props.sales_fee)"
            style='flex-grow: 1; margin: 0.25em 0.5em;'>
            예약적용
            <VIcon end icon="tabler-clock-up" />
        </VBtn>
    </VCol>
</template>
