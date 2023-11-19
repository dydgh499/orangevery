<script setup lang="ts">

interface Props {
    previewStyle: string,
    preview: string,
    style: string,
}
const props = defineProps<Props>()

const visable = ref(false);
const zoomIn = () => {
    if (props.preview != '/utils/icons/img-preview.svg')
        visable.value = !visable.value
}
</script>
<template>
    <section>
        <VImg rounded :src="props.preview" class="preview" @click="zoomIn()" :style="props.previewStyle" />
        <VDialog v-model="visable" persistent class="v-dialog-sm" :style='props.style'>
            <!-- Dialog close btn -->
            <DialogCloseBtn @click="visable = !visable" />
            <!-- Dialog Content -->
            <VCard>
                <VImg rounded :src="props.preview"></VImg>
            </VCard>
        </VDialog>
    </section>
</template>

<style lang="scss" scope>
.preview:hover {
  border: 2px solid rgb(200, 200, 200);
  cursor: pointer;
}

@media only screen and (max-device-width: 960px) {
  .preview {
    margin-block: 0.5em !important;
    margin-inline: 0 !important;
  }
}
</style>
