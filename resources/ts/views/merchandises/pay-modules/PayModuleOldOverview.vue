
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import { defaultItem } from '@/views/merchandises/pay-modules/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { isAbleModiy } from '@axios'
import type { PayModule, Merchandise } from '@/views/types'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()

const { setNullRemove } = useRequestStore()

const pay_modules = reactive<PayModule[]>([])

const addNewPayModule = async () => {
    const pay_module = <PayModule>(defaultItem)
        pay_module.mcht_id = props.item.id
    pay_modules.unshift(<PayModule>(pay_module))
}

if (props.item.id)
    Object.assign(pay_modules, await getAllPayModules(props.item.id))
watchEffect(() => {
    setNullRemove(pay_modules)
})
</script>
<template>
    <section>
        <VCard style="margin-top: 1em;">
            <VCol class="d-flex gap-4" v-if="isAbleModiy(0)">
                <VBtn type="button" style="margin-left: auto;" @click="addNewPayModule">
                    결제모듈 신규추가
                    <VIcon end icon="tabler-plus" />
                </VBtn>
            </VCol>
        </VCard>
        <template v-for="(pay_module, index) in pay_modules" :key="index">
            <PayModuleCard :item="pay_module" :able_mcht_chanage="false" style="margin-top: 1em;"/>
        </template>
    </section>
</template>
