<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import { UserPayInfo } from '@/views/types';
import { PayParamTypes } from '@core/enums';
import { requiredValidatorV2 } from '@validators';

interface Props {
    user_pay_info: UserPayInfo,
    params_mode: number,
    params: any,
}
const props = defineProps<Props>()

const {
    phone_num_format,
    phone_num,
    formatPhoneNum,
} = inputFormater()

watchEffect(() => {
    if(props.params_mode === PayParamTypes.SMS) {
        phone_num_format.value = props.params.buyer_phone.toString()
        props.user_pay_info.buyer_name = props.params.buyer_name     
        props.user_pay_info.buyer_phone = props.params.buyer_phone
    }
    else
        props.user_pay_info.buyer_phone = phone_num.value
})
</script>

<template>
    <VRow>
        <VDivider />
        <VCardTitle style="margin-top: 0.5em;">
            <b>구매자정보</b>
        </VCardTitle>
    </VRow>
    <VRow>
        <VCol md="6" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol cols="4" :md="4">
                    <label>구매자명</label>
                </VCol>
                <VCol cols="8" :md="8">
                    <div v-if="props.params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
                        <VIcon size="24" icon="tabler-user" style="margin-right: 16px;"/>
                        <h4>{{ props.user_pay_info.buyer_name }}</h4>
                    </div>
                    <VTextField 
                        v-else
                        v-model="props.user_pay_info.buyer_name"
                        variant="underlined"
                        placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(props.user_pay_info.buyer_name, '구매자명')]" 
                        prepend-icon="tabler-user" />
                </VCol>
            </VRow>
        </VCol>
        <VCol md="6" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol cols="4" :md="4">
                    <label>연락처</label>
                </VCol>
                <VCol cols="8" :md="8">
                    <div v-if="props.params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
                        <VIcon size="24" icon="tabler-device-mobile" style="margin-right: 16px;"/>
                        <h4>{{ phone_num_format }}</h4>
                    </div>
                    <VTextField 
                        v-else
                        v-model="phone_num_format" 
                        @input="formatPhoneNum"
                        variant="underlined"
                        prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                        :rules="[requiredValidatorV2(props.user_pay_info.buyer_phone, '구매자 연락처')]" 
                        />
                </VCol>
            </VRow>
        </VCol>
    </VRow>
</template>
