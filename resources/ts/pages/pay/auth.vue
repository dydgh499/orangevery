<script setup lang="ts">
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'

const { pay_module, merchandise, updatePayModule, return_url, pay_url } = pay(2)
updatePayModule()

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
                    <AuthPayOverview :pay_module="pay_module" :merchandise="merchandise" :return_url="return_url"
                        :pay_url="pay_url" />
                </div>
            </VCardText>
        </VCard>
    </section>
</template>
<style scoped>
@media (min-width: 700px) {
  #pay-container {
    inline-size: 700px;
  }
}
</style>
