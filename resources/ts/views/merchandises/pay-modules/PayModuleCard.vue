
<script setup lang="ts">
import OptionInfoOverview from '@/layouts/components/pay-module-windows/OptionInfoOverview.vue'
import PaymentInfoOverview from '@/layouts/components/pay-module-windows/PaymentInfoOverview.vue'
import PaymentTypeOverview from '@/layouts/components/pay-module-windows/PaymentTypeOverview.vue'
import TerminalInfoOverview from '@/layouts/components/pay-module-windows/TerminalInfoOverview.vue'
import MidCreateDialog from '@/layouts/dialogs/pay-modules/MidCreateDialog.vue'

import { useRequestStore } from '@/views/request'
import type { PayModule } from '@/views/types'
import { isAbleModiyV2 } from '@axios'
import corp from '@corp'
import { VForm } from 'vuetify/components'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const midCreateDlg = ref(null)
const { update, remove } = useRequestStore()

const md = ref<number>(3)

provide('midCreateDlg', midCreateDlg)

onDeactivated(() => {
    const tooltips = document.querySelectorAll('.v-tooltip.v-overlay--active')
    tooltips.forEach((tooltip) => {
        tooltip.classList.remove('v-overlay--active')
        const contents = tooltip.querySelectorAll('.v-overlay__content')
        contents.forEach((content) => {
            (content as HTMLElement).style.display = 'none'; // 툴팁 강제 숨김 처리
        })
    })
})
</script>
<template>
    <section>
        <AppCardActions action-collapsed :title="props.item.note">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12" :md="md">
                        <PaymentTypeOverview :item="props.item" :able_mcht_chanage="props.able_mcht_chanage" />
                    </VCol>
                    <VDivider :vertical="$vuetify.display.mdAndUp" />
                    <VCol cols="12" :md="md">
                        <PaymentInfoOverview :item="props.item" />
                    </VCol>
                    <template v-if="corp.id !== 30">
                        <VDivider :vertical="$vuetify.display.mdAndUp" />
                        <VCol cols="12" :md="md">
                            <TerminalInfoOverview :item="props.item" />
                        </VCol>
                    </template>
                    <VDivider :vertical="$vuetify.display.mdAndUp" />
                    <VCol cols="12" :md="md">
                        <OptionInfoOverview :item="props.item">
                            <template #edit v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                                <VCol style="text-align: end;">
                                    <VBtn type="button"
                                        @click="update('/merchandises/pay-modules', props.item, vForm, props.able_mcht_chanage)">
                                        {{ props.item.id == 0 ? "추가" : "수정" }}
                                        <VIcon end icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn type="button" color="error" v-if="props.item.id" style="margin-left: 1em;"
                                        @click="remove('/merchandises/pay-modules', props.item, props.able_mcht_chanage)">
                                        삭제
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                    <VBtn type="button" color="warning" v-else @click="props.item.id = -1" style="margin-left: 1em;">
                                        입력란 제거
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                </VCol>
                            </template>
                        </OptionInfoOverview>
                    </VCol>
                </div>
            </VForm>
        </AppCardActions>
        <MidCreateDialog ref="midCreateDlg" />
    </section>
</template>
