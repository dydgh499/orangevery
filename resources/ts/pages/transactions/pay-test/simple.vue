
<script setup lang="ts">
import MerchandiseSelectOverview from '@/views/pay/MerchandiseSelectOverview.vue';
import SimplePayOverview from '@/views/pay/SimplePayOverview.vue';
import { payTest } from '@/views/transactions/pay-test/payTest';

const { 
    mcht_id, pmod_id, pay_module, merchandise,
    pay_url, return_url, pay_modules
} = payTest(3)

provide('mcht_id', mcht_id)
provide('pmod_id', pmod_id)
provide('pay_modules', pay_modules)

</script>
<template>
    <section>
        <VRow style="justify-content: center;">
            <VCard rounded>
                <VCardText>
                    <VRow class="match-height">
                        <VCol cols="12" md="12" class="d-flex justify-center align-center">
                            <div style="width: 700px;">
                                <SimplePayOverview :pay_module="pay_module" :merchandise="merchandise"
                                    :return_url="return_url" :pay_url="pay_url">
                                    <template #explain>
                                        <MerchandiseSelectOverview/>
                                    </template>
                                </SimplePayOverview>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VRow>
    </section>
</template>
