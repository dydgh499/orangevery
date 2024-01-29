<script setup lang="ts">
import MultipleHandPayOverview from '@/views/pay/MultipleHandPayOverview.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import Footer from '@/layouts/components/Footer.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'
import { axios } from '@axios';

const { pay_module, merchandise } = pay(1)
const salesslip = ref()
const pgs = ref([])
const pay_modules = ref([])
provide('salesslip', salesslip)
Object.assign(pay_modules, await getAllPayModules(merchandise.value.id))

onMounted(async () => {
    const res = await axios.get('/api/v1/pay-gateways/' + pay_module.value.pg_id + '/sale-slip')
    pgs.value = res.data
})
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <div id="pay-container">
                    <div style="text-align: center;">
                        <img :src="corp.logo_img || ''" width="100" height="100">
                        <br>
                        <b>환영합니다 !</b>
                        <br>
                        결제하실 정보를 입력해주세요.
                    </div>
                    <MultipleHandPayOverview :pay_modules="pay_modules" :merchandise="merchandise">
                        <template #explain>
                        </template>
                    </MultipleHandPayOverview>
                </div>
            </VCardText>
        </VCard>
        <br>
        <VCard rounded>
            <VCardText>
                <footer class="layout-footer">
                    <div class="footer-content-container">
                        <Footer>
                        </Footer>
                    </div>
                </footer>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
    </section>
</template>
