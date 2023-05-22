<script lang="ts" setup>
import { axios } from '@axios';
import { requiredValidator } from '@validators';
import type { PayGateway } from '@/views/types'
import { VForm } from 'vuetify/components';
import { useStore } from '@/views/pay-gateways/useStore';

interface Props {
    item: PayGateway,
}
const vForm = ref<VForm>()
const props = defineProps<Props>();

const { pgs, pss, pay_conds, ternimals } = useStore()
const pg_types = [
    {name:'페이투스'},
    {name:'페이투스'},
    {name:'페이투스'},
    {name:'페이투스'},
    {name:'페이투스'},
]
onMounted(() => {
    props.item.pg_type = props.item.pg_type == 0 ? null : props.item.pg_type
})

function update() {
    let url = '/api/v1/pay-gateways'
    url += props.item.id ? "/" + props.item.id : ""
    vForm.value?.validate()
    axios.post(url, props.item)
}
</script>
<template>
    <AppCardActions action-collapsed :title="props.item.company_nm" :collapsed="true">
        <VDivider />
        <VForm ref="vForm">
            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            </div>
        </VForm>
    </AppCardActions>
</template>
