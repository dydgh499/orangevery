
<script setup lang="ts">
import AuthPayOverview from '@/views/transactions/auth/AuthPayOverview.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { usePayModFilterStore } from '@/views/merchandises/pay-modules/useStore'
import { payModFilter } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule, Merchandise } from '@/views/types'
import { axios } from '@axios'
import corp from '@corp'

const { pay_modules } = usePayModFilterStore()
const merchandises = ref(<Merchandise[]>([]))

const mcht_id = ref()
const pmod_id = ref()
const return_url = 'http://'+'localhost'+'/transactions/result'

const installment = ref(<number>(0))
const pg_id = ref(<number>(0))

axios.get('/api/v1/manager/merchandises/all?module_type=2')
    .then(r => { Object.assign(merchandises.value, r.data.content as Merchandise[]) })
    .catch(e => { console.log(e) })

const filterPayMod = computed(() => {
    const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id == mcht_id.value && obj.module_type == 2 })
    pmod_id.value = payModFilter(pay_modules, filter, pmod_id.value as number)
    return filter
})
watchEffect(() => {
    const pmod = pay_modules.find(obj => obj.id == pmod_id.value)
    if (pmod) {
        installment.value = pmod.installment
        pg_id.value = pmod.pg_id ?? 0
    }
})
/*
    <img :src="corp.logo_img || ''" width="100" height="100">
    <br>
    <b>
        환영합니다 !
    </b>
    <br>
    결제하실 정보를 입력해주세요.
*/
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
                            <AuthPayOverview :pmod_id="pmod_id || 0" :installment="installment || 0" :pg_id="pg_id" :return_url="return_url">
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
                            </AuthPayOverview>
                        </div>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </section>
</template>
