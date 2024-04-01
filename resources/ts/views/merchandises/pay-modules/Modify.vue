
<script setup lang="ts">
import PaymentTypeOverview from '@/layouts/components/pay-module-windows/PaymentTypeOverview.vue'
import PaymentInfoOverview from '@/layouts/components/pay-module-windows/PaymentInfoOverview.vue'
import TerminalInfoOverview from '@/layouts/components/pay-module-windows/TerminalInfoOverview.vue'
import OptionInfoOverview from '@/layouts/components/pay-module-windows/OptionInfoOverview.vue'

import { defaultItemInfo } from '@/views/merchandises/pay-modules/useStore'
import CreateForm from '@/layouts/utils/CreateForm.vue'

import { VForm } from 'vuetify/components'
import { useRequestStore } from '@/views/request'
import { isAbleModiy } from '@axios'

interface Props {
    able_mcht_chanage: boolean,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const { path, item } = defaultItemInfo()
const { update, remove } = useRequestStore()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const route = useRoute()
const md = ref<number>(3)

onMounted(() => {
    watchEffect(() => {
        md.value = (item.module_type == 0 || item.module_type == 1) && isAbleModiy(0) ? 3 : 4
    })
})
</script>
<template>
    <section>
        <CreateForm :id="Number(route.params.id) || 0" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <AppCardActions action-collapsed :title="item.note">
                        <VDivider />
                        <VForm ref="vForm">
                            <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                                <VCol cols="12" :md="md">
                                    <VCardTitle>결제타입</VCardTitle>
                                    <PaymentTypeOverview :item="item" :able_mcht_chanage="props.able_mcht_chanage" />
                                </VCol>
                                <VDivider :vertical="$vuetify.display.mdAndUp" />
                                <VCol cols="12" :md="md">
                                    <VCardTitle>결제정보</VCardTitle>
                                    <PaymentInfoOverview :item="item" />
                                </VCol>
                                <VDivider :vertical="$vuetify.display.mdAndUp" />
                                <VCol cols="12" :md="md" v-if="item.module_type < 2">
                                    <VCardTitle>장비정보</VCardTitle>
                                    <TerminalInfoOverview :item="item" />
                                </VCol>
                                <template v-if="isAbleModiy(0)">
                                    <VDivider :vertical="$vuetify.display.mdAndUp" />
                                    <VCol cols="12" :md="md">
                                        <VCardTitle>옵션</VCardTitle>
                                        <OptionInfoOverview :item="item" />
                                            <VCol style="text-align: end;">
                                                <VBtn type="button"
                                                    @click="update('/merchandises/pay-modules', item, vForm, false)">
                                                    {{ item.id == 0 ? "추가" : "수정" }}
                                                    <VIcon end icon="tabler-pencil" />
                                                </VBtn>
                                                <VBtn type="button" color="error" v-if="item.id" style="margin-left: 1em;"
                                                    @click="remove('/merchandises/pay-modules', item, false)">
                                                    삭제
                                                    <VIcon end icon="tabler-trash" />
                                                </VBtn>
                                            </VCol>
                                    </VCol>
                                </template>
                            </div>
                        </VForm>
                    </AppCardActions>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
