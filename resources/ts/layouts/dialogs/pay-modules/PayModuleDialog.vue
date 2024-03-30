<script lang="ts" setup>
import type { PayModule } from '@/views/types'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import MidCreateDialog from '@/layouts/dialogs/pay-modules/MidCreateDialog.vue'
import PaymentTypeOverview from '@/layouts/components/pay-module-windows/PaymentTypeOverview.vue'
import PaymentInfoOverview from '@/layouts/components/pay-module-windows/PaymentInfoOverview.vue'
import TerminalInfoOverview from '@/layouts/components/pay-module-windows/TerminalInfoOverview.vue'
import OptionInfoOverview from '@/layouts/components/pay-module-windows/OptionInfoOverview.vue'

import { useRequestStore } from '@/views/request'
import { isAbleModiy } from '@axios'
import { VForm } from 'vuetify/components'

interface Props {
    able_mcht_chanage: boolean,
}
const props = defineProps<Props>()

const tab = ref(0)
const vForm = ref<VForm>()
const visible = ref(false)
const midCreateDlg = ref(null)
const pay_module = ref(<PayModule>({}))

provide('midCreateDlg', midCreateDlg)

const { update, remove } = useRequestStore()

let resolveCallback: (modify: boolean) => void;

const show = (_pay_module: PayModule): Promise<boolean> => {
    pay_module.value = _pay_module
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}
const close = (modify: boolean) => {
    visible.value = modify
    resolveCallback(modify)
}

const payModuleUpdate = async () => {
    const res = await update('/merchandises/pay-modules', pay_module.value, vForm.value, false)
    if(res !== undefined) {
        if(res.status === 201) {
            visible.value = false
            resolveCallback(true)
        }
    }
}
const payModuleRemove = async () => {
    const res = await remove('/merchandises/pay-modules', pay_module.value, false)
    if(res !== undefined) {
        if(res.status === 201) {
            visible.value = false
            resolveCallback(true)
        }
    }
}

defineExpose({
    show
});
</script>
<template>
    <section>
        <VNavigationDrawer :width="450" location="end" class="scrollable-content" :model-value="visible">
            <AppDrawerHeaderSection :title="pay_module.note" @cancel="close(false)" />
            <VDivider />
            <VForm ref="vForm">
                <VTabs v-model="tab">
                    <VTab>결제타입</VTab>
                    <VTab>결제정보</VTab>
                    <VTab>장비정보</VTab>
                    <VTab v-if="isAbleModiy(pay_module.id)">옵션정보</VTab>
                </VTabs>
                <PerfectScrollbar :options="{ wheelPropagation: false }">
                    <VWindow v-model="tab">
                        <VWindowItem>
                            <PaymentTypeOverview :item="pay_module" :able_mcht_chanage="props.able_mcht_chanage" />
                        </VWindowItem>
                        <VWindowItem>
                            <PaymentInfoOverview :item="pay_module" />
                        </VWindowItem>
                        <VWindowItem>
                            <TerminalInfoOverview :item="pay_module" />
                        </VWindowItem>
                        <VWindowItem v-if="isAbleModiy(pay_module.id)">
                            <OptionInfoOverview :item="pay_module" />
                        </VWindowItem>
                    </VWindow>
                </PerfectScrollbar>
            </VForm>
            <VDivider />
            <VRow v-if="isAbleModiy(pay_module.id)" style="padding: 1em;">
                <VCol cols="12" class="d-flex gap-4">
                    <VBtn type="button" style="margin-left: auto;" @click="payModuleUpdate()">
                        {{ pay_module.id == 0 ? "추가" : "수정" }}
                        <VIcon end icon="tabler-pencil" />
                    </VBtn>
                    <VBtn type="button" color="error" v-if="pay_module.id" @click="payModuleRemove()">
                        삭제
                        <VIcon end icon="tabler-trash" />
                    </VBtn>
                </VCol>
            </VRow>
        </VNavigationDrawer>
        <MidCreateDialog ref="midCreateDlg" />
    </section>
</template>
