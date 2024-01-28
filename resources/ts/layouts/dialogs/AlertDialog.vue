<script lang="ts" setup>

const visible = ref(false)
const msg = ref<string>()
const size = ref<string>()
let resolveCallback: (isAgreed: boolean) => void;
const show = (_msg: string, _size='v-dialog-sm'): Promise<boolean> => {
    msg.value = _msg
    size.value = _size
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = () => {
    visible.value = false;
    resolveCallback(true); // 동의 버튼 누름
};

const onCancel = () => {
    visible.value = false;
    resolveCallback(false); // 취소 버튼 누름
};

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent :class="size">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />

        <!-- Dialog Content -->
        <VCard title="알림">
            <VCardText>
                <span v-html="msg"></span>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="onCancel">
                    취소
                </VBtn>
                <VBtn @click="onAgree">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
