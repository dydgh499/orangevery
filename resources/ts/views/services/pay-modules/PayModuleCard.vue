
<script setup lang="ts">
import OptionInfoOverview from '@/layouts/components/pay-module-windows/OptionInfoOverview.vue'
import PaymentInfoOverview from '@/layouts/components/pay-module-windows/PaymentInfoOverview.vue'
import PaymentTypeOverview from '@/layouts/components/pay-module-windows/PaymentTypeOverview.vue'
import MidCreateDialog from '@/layouts/dialogs/pay-modules/MidCreateDialog.vue'

import { useRequestStore } from '@/views/request'
import type { PayModule } from '@/views/types'
import { isAbleModiyV2 } from '@axios'
import { HistoryTargetNames } from '@core/enums'
import { VForm } from 'vuetify/components'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const midCreateDlg = ref(null)
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))
const { update, remove } = useRequestStore()

const md = ref<number>(6)

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
                        <PaymentInfoOverview :item="props.item">
                            <template #edit>
                                <VCol style="text-align: end;">
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: auto;"
                                        color="secondary" 
                                        variant="tonal"
                                        @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['merchandises/pay-modules'])">
                                        이력
                                        <VIcon end size="20" icon="tabler:history" />
                                    </VBtn>
                                    <template v-if="isAbleModiyV2(props.item, 'services/pay-modules')">
                                        <VBtn 
                                            style="margin-left: 1em;"
                                            @click="update('/services/pay-modules', props.item, vForm, props.able_mcht_chanage)">
                                            {{ props.item.id == 0 ? "추가" : "수정" }}
                                            <VIcon end size="20" icon="tabler-pencil" />
                                        </VBtn>
                                        <VBtn v-if="props.item.id"
                                            style="margin-left: 1em;"
                                            color="error"
                                            @click="remove('/services/pay-modules', props.item, props.able_mcht_chanage)">
                                            삭제
                                            <VIcon end size="20" icon="tabler-trash" />
                                        </VBtn>
                                        <VBtn v-else
                                            style="margin-left: 1em;"
                                            color="warning"
                                            @click="props.item.id = -1">
                                            입력란 제거
                                            <VIcon end size="20" icon="tabler-trash" />
                                        </VBtn>
                                    </template>
                                </VCol>
                            </template>
                        </PaymentInfoOverview>
                    </VCol>
                    <!--
                    <VCol cols="12" :md="md">
                        <OptionInfoOverview :item="props.item">
                            <template #edit>
                                <VCol style="text-align: end;">
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: auto;"
                                        color="secondary" 
                                        variant="tonal"
                                        @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['merchandises/pay-modules'])">
                                        이력
                                        <VIcon end size="20" icon="tabler:history" />
                                    </VBtn>
                                    <template v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
                                        <VBtn 
                                            style="margin-left: 1em;"
                                            @click="update('/merchandises/pay-modules', props.item, vForm, props.able_mcht_chanage)">
                                            {{ props.item.id == 0 ? "추가" : "수정" }}
                                            <VIcon end size="20" icon="tabler-pencil" />
                                        </VBtn>
                                        <VBtn v-if="props.item.id"
                                            style="margin-left: 1em;"
                                            color="error"
                                            @click="remove('/merchandises/pay-modules', props.item, props.able_mcht_chanage)">
                                            삭제
                                            <VIcon end size="20" icon="tabler-trash" />
                                        </VBtn>
                                        <VBtn v-else
                                            style="margin-left: 1em;"
                                            color="warning"
                                            @click="props.item.id = -1">
                                            입력란 제거
                                            <VIcon end size="20" icon="tabler-trash" />
                                        </VBtn>
                                    </template>
                                </VCol>
                            </template>
                        </OptionInfoOverview>
                    </VCol>
                    -->
                </div>
            </VForm>
        </AppCardActions>
        <!--
        <MidCreateDialog ref="midCreateDlg" />
        -->
    </section>
</template>
