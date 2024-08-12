<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { requiredValidatorV2 } from '@validators'

const vForm = ref()
const visible = ref(false)
const formatDate = <any>(inject('$formatDate'))

const cur_date = new Date()
const apply_dt = ref(formatDate(new Date(cur_date.getFullYear(), cur_date.getMonth(), cur_date.getDate()+1)))

let resolveCallback: (apply_dt: string) => void;

const show = () => {
    visible.value = true

    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const submit = async() => {
    visible.value = false
    resolveCallback(apply_dt.value)
}

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    submit()
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <VCard title="수수료 적용날짜 예약">
            <VCardText>
                <VForm ref="vForm">
                    <CreateHalfVCol :mdl="6" :mdr="6">
                        <template #name>적용일</template>
                        <template #input>
                            <VTextField v-model="apply_dt" type="date" :rules="[requiredValidatorV2(apply_dt, '적용일')]"                            
                                @keydown.enter="handleEvent"
                                style="max-width: 10em;"
                            />
                        </template>
                    </CreateHalfVCol>
                </VForm>
                <b>{{ apply_dt}}</b> 00시에 반영됩니다.<br>
                <br>
                <h5>실수로 적용된 예약적용 수수료는 "수수료율 변경이력" 탭에서 삭제시 반영되지 않습니다.</h5>
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
