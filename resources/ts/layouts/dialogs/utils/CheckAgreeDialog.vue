<script lang="ts" setup>

const visible = ref(false)
const type = ref<string>()
const method = ref<string>()

const check_text = ref()
const select_count = ref()
let resolveCallback: (isAgreed: boolean) => void;
const show = (_select_count: number, _method:string, _type: string): Promise<boolean> => {

    select_count.value = _select_count
    method.value = _method
    type.value = _type
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const isSameCheckText = computed(() => {
    return check_text.value === `${type.value} ${select_count.value}개 일괄${method.value}`
})

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
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <VCard title="알림">
            <VCardText>
                <span>{{ type }} 전체<b>({{ select_count }}개)</b>가 선택되었습니다.</span>
                <br>
                <b class='text-error'>정말 일괄적용 하시겠습니까?</b>
                <br>
                <br>
                <h5 class='text-error'>적용이 완료된 후 다시 되돌릴 수 없습니다.</h5>
                <br>
                <h5>오{{ method }}방지를 위해 하기 입력란에 
                    "<b class="text-error">{{ type }} {{select_count}}개 일괄{{ method }}</b>"
                    을 입력해주세요.
                </h5>
                <VTextField v-model="check_text" 
                    :placeholder="`${type} ${select_count}개 일괄${method}`"
                    variant="underlined"/>

            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="onCancel">
                    취소
                </VBtn>
                <VBtn @click="onAgree" :disabled="!isSameCheckText">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
