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
                <VRow class="match-height">
                    <VCol cols="12" md="12" class="d-flex justify-center align-center">
                        <div style="width: 700px;">
                            <AuthPayOverview :pay_module="pay_module" :merchandise="merchandise" :return_url="return_url"
                                :pay_url="pay_url" >
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
                            </AuthPayOverview>
                        </div>
                    </VCol>
                </VRow>
             </VCardText>
        </VCard>
    </section>
</template>
