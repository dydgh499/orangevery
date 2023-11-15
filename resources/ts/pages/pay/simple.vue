
<script setup lang="ts">
import SimplePayOverview from '@/views/pay/SimplePayOverview.vue'
import { pay } from '@/views/pay/pay'
import corp from '@corp'

const { pay_module, merchandise, updatePayModule, return_url, pay_url } = pay(3)
updatePayModule()

</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <VRow>
                    <VCol cols="12" md="12" style="padding: 0;">
                        <div id="pay-container">
                            <div style="text-align: center;">
                                <img :src="corp.logo_img || ''" width="100" height="100">
                                <br>
                                <b>환영합니다 !</b>
                                <br>
                                결제하실 정보를 입력해주세요.
                            </div>
                            <SimplePayOverview :pay_module="pay_module" :merchandise="merchandise" :return_url="return_url"
                                :pay_url="pay_url" />
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </section>
</template>
