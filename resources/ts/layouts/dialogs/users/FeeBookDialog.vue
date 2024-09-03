<script setup lang="ts">
import { requiredValidatorV2 } from '@validators'

const vForm = ref()
const visible = ref(false)
const formatDate = <any>(inject('$formatDate'))

const cur_date = new Date()
const apply_dt = ref(formatDate(new Date(cur_date.getFullYear(), cur_date.getMonth(), cur_date.getDate()+1)))
const apply_hour = ref()
const show_time = ref(false)
let resolveCallback: (apply_dt: string) => void;

const show = (_show_time=false) => {
    visible.value = true
    apply_hour.value = ''
    show_time.value = _show_time

    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const submit = async() => {
    visible.value = false
    if(show_time.value)
        resolveCallback(apply_dt.value + ` ${apply_hour.value.toString().padStart(2, "0")}:00:00`)
    else
        resolveCallback(apply_dt.value)
}

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    submit()
}

const timeFormat = () => {
    if(apply_hour.value > 23)
        apply_hour.value = 0
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="500">
        <VCard title="적용날짜 예약">
            <VCardText>
                <VForm ref="vForm">
                    <VRow style="align-items: center;">
                        <VCol md="4" cols="12"><b>적용일</b></VCol>
                        <VCol md="8" cols="12">
                            <div style="display: flex;align-items: center;">
                                <VTextField 
                                    v-model="apply_dt" 
                                    type="date"
                                    variant="underlined"
                                    :rules="[requiredValidatorV2(apply_dt, '적용일')]"
                                    @keydown.enter="handleEvent"
                                    style="max-width: 9em;"
                                >
                                <VTooltip activator="parent" location="top">
                                    적용할 날짜 입력
                                </VTooltip>
                                </VTextField>                                
                                <div style=" display: inline-flex;align-items: center;margin-left: 0.5em;">
                                    <template v-if="show_time">
                                        <VTextField 
                                            type="number"
                                            variant="underlined"
                                            v-model="apply_hour"
                                            style="width: 1.5em;"
                                            @input="timeFormat"
                                            placeholder="20"
                                        >
                                            <VTooltip activator="parent" location="top">
                                                적용할 시간 입력
                                            </VTooltip>
                                        </VTextField>
                                        <b style="margin-top: 0.5em;">시</b>
                                    </template>
                                    <b v-else style="margin-top: 0.5em;">0시</b>
                                    <span style="margin-top: 0.5em;">에 적용됩니다.</span>
                                </div> 
                            </div>
                        </VCol>
                    </VRow>
                </VForm>
                <br>
                <VDivider />
                <div style="text-align: end;">
                    <h4>잘못 적용된 예약은 적용되기전 "변경이력"탭에서 삭제시 반영되지 않습니다.</h4>
                </div>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false; resolveCallback('')">
                    취소
                </VBtn>
                <VBtn @click="submit()">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
