<script setup lang="ts">
import corp from '@corp';
import { useRequestStore } from '@/views/request'

interface Props {
    totalInput?: number,
    default?: string,
    phone_num: string,
}

const props = withDefaults(defineProps<Props>(), {
    totalInput: 6,
    default: '',
})

const emits = defineEmits(['update:pay_button'])

const { post } = useRequestStore()
const snackbar = <any>(inject('snackbar'))
const show_retry_button = ref(false)
const button_status = ref(0)
const digits = ref<string[]>([])
const refOtpComp = ref<HTMLInputElement | null>(null)

digits.value = props.default.split('')
const defaultStyle = {
    style: 'max-width: 48px; text-align: center;',
}

// eslint-disable-next-line sonarjs/cognitive-complexity
const handleKeyDown = (event: KeyboardEvent, index: number) => {
    if (event.code !== 'Tab' && event.code !== 'ArrowRight' && event.code !== 'ArrowLeft')
        event.preventDefault()

    if (event.code === 'Backspace') {
        digits.value[index - 1] = ''

        if (refOtpComp.value !== null && index > 1) {
            const inputEl = refOtpComp.value.children[index - 2].querySelector('input')

            if (inputEl)
                inputEl.focus()
        }
    }
    const numberRegExp = /^([0-9])$/

    if (numberRegExp.test(event.key)) {
        digits.value[index - 1] = event.key

        if (refOtpComp.value !== null && index !== 0 && index < refOtpComp.value.children.length) {
            const inputEl = refOtpComp.value.children[index].querySelector('input')
            if (inputEl)
                inputEl.focus()
        }
    }
    console.log(digits.value.join('').length)
    if (digits.value.join('').length === props.totalInput)
        verification()
}
const requestCodeIssuance = async () => {
    const r = await post('/api/v1/manager/transactions/mobile-code-issuance', { phone_num: props.phone_num })
    snackbar.value.show('입력하신 휴대폰번호로 인증번호를 보냈습니다!<br>6자리 인증번호를 입력해주세요.', 'success')
    show_retry_button.value = false
}
const verification = async () => {
    if (button_status.value === 0) {
        await requestCodeIssuance()
        button_status.value = 1
    }
    else if (button_status.value === 1) {
        const r = await post('/api/v1/manager/transactions/mobile-code-auth', { phone_num: props.phone_num, 'verification_number': digits.value.join('') })
        if (r.code == 200) {
            emits('update:pay_button', true)
            button_status.value = 2
            snackbar.value.show('인증에 성공하였습니다.', 'warning')
        }
        else {
            snackbar.value.show('인증번호가 다릅니다. 다시 확인해주세요.', 'warning')
            show_retry_button.value = true
        }
    }
    else if (button_status.value === 2) {

    }
    //props.phone_num
}
</script>
<template>
    <div v-if="corp.pv_options.paid.use_pay_verification_mobile">
        <VCol cols="12" style="padding: 0;" v-if="button_status === 1">
            <div style="margin-top: 1.5em;">
                <h6 class="text-base font-weight-bold mb-3">
                    6자리 인증번호를 입력해주세요.              
                </h6>
                <div ref="refOtpComp" class="d-flex align-center gap-4">
                    <VTextField v-for="i in props.totalInput" :key="i" :model-value="digits[i - 1]" v-bind="defaultStyle"
                        maxlength="1" @keydown="handleKeyDown($event, i)" />
                </div>
                <div v-if="show_retry_button">
                    <span @click="requestCodeIssuance()" class="text-primary retry-text">인증번호 재발송</span>
                    <span> 대기시간</span>
                </div>
            </div>
        </VCol>

        <VCol cols="12" style="padding: 1em 0;">
            <VBtn block @click="verification()">
                휴대폰 인증하기
            </VBtn>
            <VDivider />
        </VCol>
    </div>
</template>

<style lang="scss" scoped>
.retry-text {
  font-size: 0.9em;
  text-decoration: underline;
}

.v-field__field {
  input {
    padding: 0.1rem;
    font-size: 1.25rem;
    text-align: center;
  }
}
</style>
