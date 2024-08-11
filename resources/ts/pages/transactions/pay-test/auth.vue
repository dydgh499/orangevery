
<script setup lang="ts">
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue';
import MerchandiseSelectOverview from '@/views/pay/MerchandiseSelectOverview.vue';
import { payTest } from '@/views/transactions/pay-test/payTest';

const { 
    mcht_id, pmod_id, pay_module, merchandise,
    pay_url, return_url, pay_modules
} = payTest(2)

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
                            <div style="max-width: 700px;">
                                <AuthPayOverview :pay_module="pay_module" :merchandise="merchandise"
                                    :return_url="return_url" :pay_url="pay_url">
                                    <template #explain>
                                        <MerchandiseSelectOverview/>
                                    </template>
                                </AuthPayOverview>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VRow>
    </section>
</template>
