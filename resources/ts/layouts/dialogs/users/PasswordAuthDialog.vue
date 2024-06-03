<script lang="ts" setup>
import MobileVerification from '@core/components/MobileVerification.vue'

const mobileVerfication = ref(null)
const visible = ref(false)
const phone_num = ref('')
const merchandise = ref({id:-1})

let resolveCallback: (token: string) => void;

const show = async (_phone_num: string): Promise<string> => {
    phone_num.value = _phone_num
    visible.value = true
    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = (token: string) => {
    visible.value = false    
    resolveCallback(token); // 동의 버튼 누름
};

const onCancel = () => {
    visible.value = false    
    resolveCallback(''); // 취소 버튼 누름

}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="500">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="onCancel()" />
        <!-- Dialog Content -->
        <VCard class="pa-5 pa-sm-8">
          <VCardItem class="text-start">
                <VCardTitle class="text-h6 font-weight-bold mb-2">SMS 휴대폰번호 인증</VCardTitle>
                <br>
                <span class="text-base">
                    로그인을 하기위해 휴대폰 인증이 필요합니다.
                </span>
                <MobileVerification :totalInput="6" :phone_num="phone_num" :merchandise="merchandise" @update:token="onAgree($event)"
                    ref="mobileVerfication"
                />
            </VCardItem>
        </VCard>
    </VDialog>
</template> 
