
<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import HandPayOverview from '@/views/pay/HandPayOverview.vue'
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { payTest } from '@/views/transactions/pay-test/payTest'


const { mchts } = useSalesFilterStore()
const {
    mcht_id, pmod_id, pgs, merchandise, pay_modules, pay_module
} = payTest(1)

const salesslip = ref()
provide('salesslip', salesslip)

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
                            <HandPayOverview :pay_module="pay_module" :merchandise="merchandise">
                                <template #explain>
                                    <VCol cols="12">
                                        <VRow no-gutters>
                                            <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                                <template #name>가맹점 선택</template>
                                                <template #input>
                                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id"
                                                        :items="mchts" prepend-inner-icon="tabler-building-store"
                                                        item-title="mcht_name" item-value="id" single-line eager />
                                                </template>
                                            </CreateHalfVCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12">
                                        <VRow no-gutters>
                                            <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                                <template #name>결제모듈 선택</template>
                                                <template #input>
                                                    <div>
                                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pmod_id"
                                                            :items="pay_modules" prepend-inner-icon="ic-outline-send-to-mobile"
                                                            item-title="note" item-value="id" single-line eager>                                                        
                                                        </VSelect>
                                                    </div>
                                                </template>
                                            </CreateHalfVCol>
                                        </VRow>
                                    </VCol>
                                </template>
                            </HandPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
    </section>
</template>
