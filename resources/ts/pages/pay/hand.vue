<script setup lang="ts">
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import { pay } from '@/views/pay/pay'
import { axios } from '@axios'
import corp from '@corp'

const { pay_module, merchandise, updatePayModule } = pay(1)
const salesslip = ref()
const pgs = ref([])

updatePayModule()
provide('salesslip', salesslip)

onMounted(async () => {
    const res = await axios.get('/api/v1/pay-gateways/' + pay_module.value.pg_id + '/sale-slip')
    pgs.value = res.data
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <VRow class="match-height">
                    <VCol cols="12" class="d-flex justify-center align-center">
                        <div style="max-width: 700px;">
                            <HandPayOverview :pay_module="pay_module" :merchandise="merchandise">
                                <template #explain>
                                    <div style="padding-bottom: 1em;text-align: center;">
                                        <img :src="corp.logo_img || ''" width="100" height="100">
                                        <div>
                                            <b>환영합니다 !</b>
                                            <br>
                                            <span>결제하실 정보를 입력해주세요.</span>
                                        </div>
                                    </div>
                                </template>
                            </HandPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <br>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
    </section>
</template>
