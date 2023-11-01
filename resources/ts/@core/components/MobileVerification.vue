<script setup lang="ts">
import corp from '@corp';
import { useRequestStore } from '@/views/request'
import { getUserLevel } from '@axios'

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
const button_status = ref(0)
const digits = ref<string[]>([])
const ref_opt_comp = ref<HTMLInputElement | null>(null)
const defaultStyle = {
    style: 'max-width: 48px; text-align: center;',
}
const countdown_time = ref(180)
let countdown_timer = <any>(null)

digits.value = props.default.split('')
if(getUserLevel() >= 35) {
    button_status.value = 2
    emits('update:pay_button', true)
}

const handleKeyDown = (event: KeyboardEvent, index: number) => {
    if (event.code !== 'Tab' && event.code !== 'ArrowRight' && event.code !== 'ArrowLeft')
        event.preventDefault()

    if (event.code === 'Backspace') {
        digits.value[index - 1] = ''

        if (ref_opt_comp.value !== null && index > 1) {
            const inputEl = ref_opt_comp.value.children[index - 2].querySelector('input')

            if (inputEl)
                inputEl.focus()
        }
    }
    const numberRegExp = /^([0-9])$/

    if (numberRegExp.test(event.key)) {
        digits.value[index - 1] = event.key

        if (ref_opt_comp.value !== null && index !== 0 && index < ref_opt_comp.value.children.length) {
            const inputEl = ref_opt_comp.value.children[index].querySelector('input')
            if (inputEl)
                inputEl.focus()
        }
    }
    console.log(digits.value.join('').length)
    if (digits.value.join('').length === props.totalInput)
        verification()
}
const timer = () => {
    if(countdown_time.value === 0)
        clearInterval(countdown_timer)
    else
        countdown_time.value--
}
const requestCodeIssuance = async () => {
    const r = await post('/api/v1/bonaejas/mobile-code-issuance', { phone_num: props.phone_num, brand_id: corp.id })
    if (r.status == 200) {
        snackbar.value.show('입력하신 휴대폰번호로 인증번호를 보냈습니다!<br>6자리 인증번호를 입력해주세요.', 'success')
        button_status.value = 1

        if (countdown_timer)
            clearInterval(countdown_timer)

        countdown_time.value = 180
        countdown_timer = setInterval(timer, 1000);
    }
    else
        snackbar.value.show(r.data.message, 'error')
}
const verification = async () => {
    if (button_status.value === 0) {
        await requestCodeIssuance()
    }
    else if (button_status.value === 1) {
        const r = await post('/api/v1/bonaejas/mobile-code-auth', { phone_num: props.phone_num, verification_number: digits.value.join('') })
        if (r.status == 200) {
            emits('update:pay_button', true)
            button_status.value = 2
            snackbar.value.show('인증에 성공하였습니다.', 'success')
        }
        else {
            snackbar.value.show('인증번호가 다릅니다. 다시 확인해주세요.', 'warning')
        }
    }
    else if (button_status.value === 2) {
        snackbar.value.show('이미 인증에 성공하였습니다.', 'success')
    }
}
const countdownTimer = computed(() => {
    if(countdown_time.value > 0) {
        const min = parseInt((countdown_time.value/60).toString())
        const sec = countdown_time.value%60
        return `${min}:${sec < 10 ? '0' + sec : sec}`
    }
    else
        return `0:00`
})
</script>
<template>
    <div>
        <VCol cols="12" style="padding: 0;" v-if="button_status === 1">
            <div style="margin-top: 1.5em;">
                <VCol>
                    <h6 class="text-base font-weight-bold mb-3">
                        6자리 인증번호를 입력해주세요.
                    </h6>
                    <div ref="ref_opt_comp" class="d-flex align-center gap-4">
                        <VTextField v-for="i in props.totalInput" :key="i" :model-value="digits[i - 1]"
                            v-bind="defaultStyle" maxlength="1" @keydown="handleKeyDown($event, i)" class="vertify-number"/>
                    </div>
                </VCol>
                <VCol class="retry-container">
                    <span @click="requestCodeIssuance()" class="text-primary retry-text">인증번호 재발송</span>
                    <span> 
                        <span>만료시간:</span>
                        <span id="countdown" class="text-primary">{{ countdownTimer }}</span>
                    </span>
                </VCol>
            </div>
        </VCol>

        <VCol cols="12" style="padding: 1em 0;" v-if="button_status !== 2">
            <VBtn block @click="verification()">
                휴대폰 인증하기
            </VBtn>
            <VDivider />
        </VCol>
    </div>
</template>

<style lang="scss" scoped>
.retry-text {
  cursor: pointer;
  font-size: 0.9em;
  text-decoration: underline;
}

.retry-container {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.vertify-number {
  input {
    padding: 0.1rem !important;
    font-size: 1.25rem !important;
    text-align: center !important;
  }
}
</style>
