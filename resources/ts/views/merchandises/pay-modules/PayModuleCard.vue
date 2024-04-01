
<script setup lang="ts">
import PaymentTypeOverview from '@/layouts/components/pay-module-windows/PaymentTypeOverview.vue'
import PaymentInfoOverview from '@/layouts/components/pay-module-windows/PaymentInfoOverview.vue'
import TerminalInfoOverview from '@/layouts/components/pay-module-windows/TerminalInfoOverview.vue'
import OptionInfoOverview from '@/layouts/components/pay-module-windows/OptionInfoOverview.vue'

import { VForm } from 'vuetify/components'
import { useRequestStore } from '@/views/request'
import { isAbleModiy } from '@axios'
import type { PayModule } from '@/views/types'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()
const md = ref<number>(3)

onMounted(() => {
    watchEffect(() => {
        md.value = (props.item.module_type == 0 || props.item.module_type == 1) && isAbleModiy(0) ? 3 : 4
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
                        <VCardTitle>결제타입</VCardTitle>
                        <PaymentTypeOverview :item="props.item" :able_mcht_chanage="props.able_mcht_chanage" />
                    </VCol>
                    <VDivider :vertical="$vuetify.display.mdAndUp" />
                    <VCol cols="12" :md="md">
                        <VCardTitle>결제정보</VCardTitle>
                        <PaymentInfoOverview :item="props.item" />
                    </VCol>
                    <VDivider :vertical="$vuetify.display.mdAndUp" />
                    <VCol cols="12" :md="md" v-if="props.item.module_type < 2">
                        <VCardTitle>장비정보</VCardTitle>
                        <TerminalInfoOverview :item="props.item" />
                    </VCol>
                    <template v-if="isAbleModiy(item.id)">
                        <VDivider :vertical="$vuetify.display.mdAndUp" />
                        <VCol cols="12" :md="md">
                            <VCardTitle>옵션</VCardTitle>
                            <OptionInfoOverview :item="props.item" />
                            <VCol style="text-align: end;">
                                <VBtn type="button"
                                    @click="update('/merchandises/pay-modules', props.item, vForm, false)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id" style="margin-left: 1em;"
                                    @click="remove('/merchandises/pay-modules', props.item, false)">
                                    삭제
                                    <VIcon end icon="tabler-trash" />
                                </VBtn>
                            </VCol>
                        </VCol>
                    </template>
                </div>
            </VForm>
        </AppCardActions>
    </section>
</template>
