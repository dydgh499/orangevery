<script setup lang="ts">
import { UserPayInfo } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import DaumPostCodeDialog from '@/layouts/dialogs/users/DaumPostCodeDialog.vue'

interface Props {
    user_pay_info: UserPayInfo,
}
const props = defineProps<Props>()
const daumPostCodeDialog = ref()
const setAddress = async () => {
    const address = await daumPostCodeDialog.value.show()
    if(address !== '') 
        props.user_pay_info.addr = address
}

</script>
<template>
    <VRow>
        <VDivider />
        <VCardTitle style="margin-top: 0.5em;">
            <b>배송정보</b>
        </VCardTitle>
    </VRow>
    <VRow>
        <VCol md="12" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol cols="4" :md="2">
                    <label>배송지 주소</label>
                </VCol>
                <VCol cols="8" :md="8">
                    <div style="display: flex;align-items: center;">
                        <VTextField 
                        v-model="props.user_pay_info.addr"
                        :disabled="true"
                        variant="underlined"
                        placeholder="배송지 주소를 검색해주세요."
                        :rules="[requiredValidatorV2(props.user_pay_info.addr, '배송지 주소')]" 
                        prepend-icon="tabler:address-book"
                        @click="" />
                        <VBtn size="small" style="margin-left: 1em;" @click="setAddress()">
                            주소검색
                        </VBtn>
                    </div>
                </VCol>
            </VRow>
        </VCol>
        <VCol md="12" cols="12" style="padding: 0 12px;">
            <VRow no-gutters style="min-height: 3.5em;">
                <VCol cols="4" :md="2">
                    <label>상세주소</label>
                </VCol>
                <VCol cols="8" :md="10">
                    <VTextField 
                        v-model="props.user_pay_info.detail_addr" 
                        variant="underlined"
                        prepend-icon="tabler:list-details" placeholder="상세주소를 입력해주세요"
                        :rules="[requiredValidatorV2(props.user_pay_info.detail_addr, '상세주소')]" 
                        />
                </VCol>
            </VRow>
        </VCol>
        <VCol md="12" cols="12">
            <VTextarea v-model="props.user_pay_info.note" counter label="메모사항"
                variant="filled"
                prepend-inner-icon="twemoji-spiral-notepad" maxlength="255" auto-grow />
        </VCol>
    </VRow>
    <DaumPostCodeDialog ref="daumPostCodeDialog"/>
</template>
