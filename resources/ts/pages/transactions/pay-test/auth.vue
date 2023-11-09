
<script setup lang="ts">
import AuthPayOverview from '@/views/pay/AuthPayOverview.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { payTest } from '@/views/transactions/pay-test/payTest'

const { 
    mcht_id, pmod_id, pay_module, merchandise,
    pay_url, return_url, merchandises, pay_modules
} = payTest(2)
</script>
<template>
    <section>
        <VCard rounded>
            <VCardText>
                <VRow class="match-height">
                    <VCol cols="12" md="12" class="d-flex justify-center align-center">
                        <div style="width: 700px;">
                            <br>
                            <div style="text-align: center;">
                                <b>
                                    결제할 가맹점과 결제모듈을 선택하신 후 결제하기 버튼을 눌러주세요.
                                </b>
                            </div>
                            <AuthPayOverview :pay_module="pay_module" :merchandise="merchandise"
                                :return_url="return_url" :pay_url="pay_url">
                                <template #explain>
                                    <VCol cols="12">
                                        <VRow no-gutters>
                                            <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                                <template #name>가맹점 선택</template>
                                                <template #input>
                                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id"
                                                        :items="merchandises" prepend-inner-icon="tabler-building-store"
                                                        label="가맹점 선택" item-title="mcht_name" item-value="id" single-line
                                                        eager />
                                                </template>
                                            </CreateHalfVCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12">
                                        <VRow no-gutters>
                                            <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                                <template #name>결제모듈 선택</template>
                                                <template #input>
                                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pmod_id"
                                                        :items="pay_modules" prepend-inner-icon="ic-outline-send-to-mobile"
                                                        label="결제모듈 선택" item-title="note" item-value="id" single-line eager />
                                                </template>
                                            </CreateHalfVCol>
                                        </VRow>
                                    </VCol>
                                </template>
                            </AuthPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </section>
</template>
