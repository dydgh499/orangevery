
<script setup lang="ts">
import HandPayOverview from '@/views/transactions/hand/HandPayOverview.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { usePayModFilterStore } from '@/views/merchandises/pay-modules/useStore'
import { payModFilter } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule, Merchandise } from '@/views/types'
import { axios } from '@axios'


const { pay_modules } = usePayModFilterStore()
const merchandises = ref(<Merchandise[]>([]))

const salesslip = ref()
const mcht_id = ref()
const pmod_id = ref()

provide('salesslip', salesslip)

const installment = ref(<number>(0))
const is_old_auth = ref(<boolean>(false))
const merchandise = ref(<Merchandise>({}))

axios.get('/api/v1/manager/merchandises/all?module_type=1')
    .then(r => { Object.assign(merchandises.value, r.data.content as Merchandise[]) })
    .catch(e => { console.log(e) })

const filterPayMod = computed(() => {
    const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id == mcht_id.value && obj.module_type == 1 })
    pmod_id.value = payModFilter(pay_modules, filter, pmod_id.value as number)
    return filter
})
watchEffect(() => {
    const pmod = pay_modules.find(obj => obj.id == pmod_id.value)
    if (pmod) {
        installment.value = pmod.installment
        is_old_auth.value = pmod.is_old_auth
        merchandise.value = merchandises.value.find(obj => obj.id == mcht_id.value)
    }
})
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
                            <HandPayOverview :pmod_id="pmod_id || 0" :installment="installment || 0"
                                :is_old_auth="is_old_auth || false" :merchandise="merchandise">
                                <template #explain>
                                    <VCol cols="12">
                                        <VRow no-gutters>
                                            <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                                                <template #name>가맹점 선택</template>
                                                <template #input>
                                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id"
                                                        :items="merchandises" prepend-inner-icon="tabler-building-store"
                                                        label="가맹점 선택" item-title="mcht_name" item-value="id" single-line
                                                        create />
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
                                                        :items="filterPayMod" prepend-inner-icon="ic-outline-send-to-mobile"
                                                        label="결제모듈 선택" item-title="note" item-value="id" single-line />
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
        <SalesSlipDialog ref="salesslip" />
    </section>
</template>
