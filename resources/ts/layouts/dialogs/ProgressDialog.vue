<script lang="ts" setup>

const gage = ref(80)
const message = ref('테스트 데이터 입니다 ...')
const visible = ref(false)
const show = (view: boolean, val: number, msg: string) => {
    visible.value = view
    message.value = msg
    gage.value = val
}
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="500">
        <VCard class="demo-space-y" max-width="500">
            <VCardText class="p-3">
                <span v-html="message" style="line-height: 40px;"></span>
                <VProgressLinear rounded stream v-model="gage" height="20" color="primary" style="margin-block: 2em 1em;">
                    <strong>{{ Math.ceil(gage) }}%</strong>
                </VProgressLinear>
            </VCardText>
        </VCard>
    </VDialog>
</template>
