<script setup lang="ts">
import { simplePays } from '@/views/merchandises/pay-modules/useStore';
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { AuthPay, Merchandise, PayModule, PayWindow } from '@/views/types';
import { reactive } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    pay_module: PayModule, 
    merchandise: Merchandise,
    pay_window: PayWindow,
}

const route = useRoute()
const vForm = ref<VForm>()
const props = defineProps<Props>()
const auth_pay_info = reactive(<AuthPay>({}))
const action_url = ref()

watchEffect(() => {
    if(props.pay_module.module_type === 2)
        action_url.value = import.meta.env.VITE_NOTI_URL + '/v2/online/pay/auth'
    else if(props.pay_module.module_type === 3)
        action_url.value = import.meta.env.VITE_NOTI_URL + '/v2/online/pay/simple'
})
watchEffect(() => {
    if(location.href.includes('/pay-test'))
        auth_pay_info.return_url = window.location.origin + '/transactions/pay-test/' + props.pay_window.window_code + '/result'
    else {
        const base_url = window.location.origin + '/pay/' + props.pay_window.window_code + '/result'

        if(route.query.pc && route.query.pc.length)
            auth_pay_info.return_url = base_url + `?pc=${route.query.pc}`
        else
            auth_pay_info.return_url = base_url
    }
})
</script>
<template>
    <VCard flat rounded>
        <VCardText style="padding: 0;">
            <VDivider />
            <VForm ref="vForm" :action="action_url" method="post">
                <VTextField v-model="auth_pay_info.return_url" type="visible" name="return_url" style="display: none;" />
                <CommonPayOverview 
                    :common_info="auth_pay_info" 
                    :pay_module="props.pay_module"
                    :merchandise="props.merchandise"
                    :key="props.pay_module.id"
                    :pay_code="props.pay_module.module_type === 2 ? 'A' : 'S'"
                >
                    <template #extra_info>
                        <VRow v-if="props.pay_module.module_type === 3">
                            <VCol md="6" cols="12">
                                <VRow no-gutters>
                                    <VCol cols="4" :md="4">
                                        <label>결제방식</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="auth_pay_info.route"
                                            name="route" :items="simplePays" variant="underlined"
                                            prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="결제방식 선택"
                                            item-title="title" item-value="id" single-line />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </template>
                </CommonPayOverview>
            </VForm>
        </VCardText>
    </VCard>
</template>
