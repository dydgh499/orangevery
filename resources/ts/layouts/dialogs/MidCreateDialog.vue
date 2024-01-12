<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import corp from '@corp'

const mid_code  = ref(null)
const mid_codes = ref(<string[]>([]))
const visible = ref(false)
const snackbar = <any>(inject('snackbar'))

let resolveCallback: (mid_code: string) => void;

const show = (): Promise<string> => {
    visible.value = true

    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const selected = () => {
    visible.value = false
    if(mid_code.value)
        resolveCallback(mid_code.value)
    else
        snackbar.value.show('금융 VAN을 선택해주세요.', 'warning')
}

watchEffect(() => {
    if(corp.id === 19) {
        mid_codes.value = [
            'BUDM_DO',
            'BUDM_DR',
            'BUDM_DP',
        ]
    }
    else
        mid_codes.value = []
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <!-- Dialog Content -->
        <DialogCloseBtn @click="visible = false; resolveCallback('')" />
        <VCard title="발급될 MID 코드를 입력해주세요.">
            <VCardText>
                <VRow class="pt-3">
                    <CreateHalfVCol :mdl="0" :mdr="12">
                        <template #name></template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="mid_code" :items="mid_codes"
                                label="출금 이체모듈 선택" single-line/>
                        </template>
                    </CreateHalfVCol>
                    <VBtn @click="selected()">
                        <span style="font-weight: bold;">선택하기</span>
                    </VBtn>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
