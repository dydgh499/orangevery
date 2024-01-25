<script setup lang="ts">
interface Props {
    style: string,
}
const props = defineProps<Props>()
const preview = ref()
const visable = ref(false)
const show = (_preview: string) => {
    preview.value = _preview
    if (_preview != '/utils/icons/img-preview.svg')
        visable.value = !visable.value
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visable" persistent class="v-dialog-sm" :style='props.style'>
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visable = !visable" />
        <!-- Dialog Content -->
        <VCard>
            <VImg rounded :src="preview"></VImg>
        </VCard>
    </VDialog>
</template>
