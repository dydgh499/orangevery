<script setup lang="ts">
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { AuthPay, Merchandise, PayModule } from '@/views/types';
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    return_url: string,
    pay_url: string,
    pay_module: PayModule, 
    merchandise: Merchandise,
}
const props = defineProps<Props>()
const auth_pay_info = reactive(<AuthPay>({}))
const vForm = ref<VForm>()

watchEffect(() => {
    auth_pay_info.return_url = props.return_url
})

</script>
<template>
    <VCard flat rounded>
        <VCardText style="padding: 0;">
            <slot name="explain">
            </slot>
            <VDivider />
            <VForm ref="vForm" :action="pay_url" method="post">
                <VTextField v-model="auth_pay_info.return_url" type="visible" name="return_url" style="display: none;" />
                <CommonPayOverview 
                    :common_info="auth_pay_info" 
                    :pay_module="props.pay_module"
                    :merchandise="props.merchandise"
                    :key="props.pay_module.id"                    
                    :pay_code="'A'"
                />
            </VForm>
        </VCardText>
    </VCard>
</template>
