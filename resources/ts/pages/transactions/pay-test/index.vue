<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue';
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue';
import HandPayOverview from '@/views/pay/HandPayOverview.vue';
import MerchandiseSelectOverview from '@/views/pay/MerchandiseSelectOverview.vue';
import type { Merchandise, PayGateway, PayModule, PayWindow } from '@/views/types';

const payment_gateways = ref(<PayGateway[]>[])
const merchandise = ref(<Merchandise>({}))
const pay_module = ref(<PayModule>{module_type: 0})
const pay_window = ref(<PayWindow>({}))
const params_mode = ref(false)
const params = ref({
    item_name : '',
    buyer_name : '',
    amount : 0,
    buyer_phone : '',
})
const salesslip = ref()

provide('payment_gateways', payment_gateways)
provide('merchandise', merchandise)
provide('pay_module', pay_module)
provide('pay_window', pay_window)
provide('params_mode', params_mode)
provide('params', params)
provide('salesslip', salesslip)

</script>
<template>
    <section>
        <VRow style="justify-content: center;">
            <VCard rounded>
                <VCardText style="padding: 0.5em;" :style="$vuetify.display.smAndDown ? '' : 'min-width: 700px;max-width: 700px;'">
                    <VRow class="match-height">
                        <VCol cols="12" :style="$vuetify.display.smAndDown ? '' : 'min-width: 700px;max-width: 700px;'">
                            <div>
                                <MerchandiseSelectOverview/>
                            </div>
                        </VCol>
                        <VCol cols="12" class="d-flex justify-center align-center" v-if="pay_module?.module_type">
                            <div :style="$vuetify.display.smAndDown ? '' : 'min-width: 700px;max-width: 700px;'">
                                <HandPayOverview 
                                    v-if="pay_module?.module_type === 1"
                                    :pay_module="pay_module" 
                                    :merchandise="merchandise"
                                >
                                </HandPayOverview>
                                <AuthPayOverview 
                                    v-else-if="pay_module?.module_type === 2 || pay_module?.module_type === 3"
                                    :pay_module="pay_module" 
                                    :merchandise="merchandise"
                                    :pay_window="pay_window"
                                />
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
             </VCard>
        </VRow>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="payment_gateways" :key="payment_gateways.length"/>
    </section>
</template>
