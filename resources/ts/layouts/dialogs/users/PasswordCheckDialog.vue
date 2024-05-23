<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { requiredValidatorV2 } from '@validators'

const password = ref()
const visible = ref(false)
const is_show = ref(false)

let resolveCallback: (password: string) => void;

const show = () => {
    visible.value = true
    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = () => {
    visible.value = false;
    resolveCallback(password.value); // 동의 버튼 누름
};

const onCancel = () => {
    visible.value = false;
    resolveCallback(''); // 취소 버튼 누름
};

const handleEvent = (event: KeyboardEvent) => {
    event.preventDefault()
    onAgree()
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="onCancel()" />
        <!-- Dialog Content -->
        <VCard title="패스워드 확인">
            <VCardText>
                <VCol cols="12">
                    <CreateHalfVCol :mdl="4" :mdr="8">
                        <template #name>
                            <span style="line-height: 2.5em;">패스워드 확인</span>
                        </template>
                        <template #input>
                            <VTextField v-model="password" counter prepend-inner-icon="tabler-lock"
                                :rules="[requiredValidatorV2(password, '패스워드')]"
                                :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                                :type="is_show ? 'text' : 'password'" persistent-placeholder
                                @click:append-inner="is_show = !is_show"
                                @keydown.enter="handleEvent"
                                autocomplete />
                        </template>
                    </CreateHalfVCol>
                </VCol>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="onCancel()">
                    취소
                </VBtn>
                <VBtn @click="onAgree()">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
