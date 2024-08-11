<script setup lang="ts">
import { simplePays } from '@/views/merchandises/pay-modules/useStore';
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { Merchandise, PayModule, SimplePay } from '@/views/types';
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    return_url: string,
    pay_url: string,
    pay_module: PayModule,
    merchandise: Merchandise,
}
const props = defineProps<Props>()
const simple_pay_info = reactive(<SimplePay>({}))
const vForm = ref<VForm>()

watchEffect(() => {
    simple_pay_info.return_url = props.return_url
})
</script>
<template>
    <VCard flat rounded>
        <VCardText>
            <slot name="explain"></slot>
            <VDivider />
            <VForm ref="vForm" :action="pay_url" method="post">
                <VTextField v-model="simple_pay_info.return_url" type="visible" name="return_url"
                    style="display: none;" />
                <CommonPayOverview :common_info="simple_pay_info" :pay_module="props.pay_module"
                    :merchandise="props.merchandise" :key="props.pay_module.id" :pay_code="'S'">
                    <template #extra_info>
                        <VRow>
                            <VCol md="6" cols="12">
                                <VRow no-gutters>
                                    <VCol cols="4" :md="4">
                                        <label>결제방식</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="simple_pay_info.route"
                                            name="route" :items="simplePays"
                                            variant="underlined"
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
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>

