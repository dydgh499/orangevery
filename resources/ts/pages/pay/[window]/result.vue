<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import PayResultOverview from '@/views/pay/PayResultOverview.vue';
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import type { PayGateway, SalesSlip } from '@/views/types';


const route = useRoute()
const { getPayWindow } = payWindowStore()

const sales_slip = ref(<SalesSlip>({}))
const payment_gateways = ref(<PayGateway[]>[])
const window_code = ref(200)
const window_message = ref('')
const is_load = ref(false)

const salesSlipDialog = ref()
const snackbar = <any>(inject('snackbar'))

provide('salesSlipDialog', salesSlipDialog)

const businessNumMasking = () => {
    if(sales_slip.value.business_num?.length as number > 9) {
        const bsin_num = sales_slip.value.business_num?.replace(/\D/g, '') as string
        sales_slip.value.business_num = bsin_num.slice(0, 3) + "-" + bsin_num.slice(3, 5) + "-" + bsin_num.slice(5)
    }
}

onMounted(async () => {
    const [_code, _message, _params_mode, _data] = await getPayWindow(route.params.window, route.query.pc)
    if(_code === 200) {
        sales_slip.value = {
            ..._data.merchandise,
            ...route.query,
        }
        sales_slip.value.pg_id       = _data.payment_gateway.id
        sales_slip.value.is_cancel   = Number(route.query.is_cancel ?? false)
        sales_slip.value.trx_dttm    = (route.query.trx_dttm ?? new Date()) as string
        sales_slip.value.amount      = Number(sales_slip.value.amount) 
        sales_slip.value.module_type = _data.payment_module.module_type 
        payment_gateways.value = [_data.payment_gateway]
        businessNumMasking()
    }
    else {
        window_code.value = _code
        window_message.value = _message
        snackbar.value.show(window_message.value, 'error')
    }
    is_load.value = true
})
</script>
<template>
    <section class="result-wrapper">
        <VCard rounded>
            <VCardText>
                <template v-if="is_load">
                    <PayResultOverview 
                        v-if="window_code === 200"
                        :sales_slip="sales_slip" :key="payment_gateways.length"
                    />
                    <VRow class="match-height" v-else>
                        <VCol style="padding: 3em; text-align: center;">
                            <div style="text-align: center;">
                                <VIcon size="40" icon="line-md:emoji-frown-twotone" color="error"/>                        
                            </div>
                            <br>
                            <h3 style="text-align: center;">거래결과창을 사용할 수 없습니다. </h3>
                            <div style=" padding: 1em;text-align: center;">
                                <h4>- {{ window_message }} -</h4>
                            </div>
                        </VCol>
                    </VRow>
                </template>
                <VRow class="match-height" v-else>
                    <VCol style="padding: 3em; text-align: center;">
                        <b style="text-align: center;">거래결과창을 로딩하고 있습니다...</b>
                        <div style=" padding: 1em;text-align: center;">
                            <VIcon size="40" icon="svg-spinners:3-dots-move" style="margin-right: 0.5em;"/>
                            <VIcon size="40" icon="svg-spinners:3-dots-move" />
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesSlipDialog" :pgs="payment_gateways" :key="payment_gateways.length"/>
    </section>
</template>
<style scoped>
.result-wrapper {
  inline-size: 600px;
  margin-inline: auto;
}

@media (max-width: 700px) {
  .result-wrapper {
    inline-size: 100%;
  }
}

</style>
