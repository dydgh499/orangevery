<script setup lang="ts">

const props = defineProps({
    preview: {
        type: String,
        required: true,
    },
    style: {
        type: String,
        required: true,
    },
    previewStyle: {
        type: String,
        required: true,
    }
});
const visable = ref(false);
const zoomIn = () => {
    if (props.preview != '/icons/img-preview.svg')
            visable.value = !visable.value
}
</script>
<template>
    <VImg rounded :src="props.preview" class="preview" @click="zoomIn()" :style="props.previewStyle"/>
    <VDialog v-model="visable">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visable = !visable" />
        <!-- Dialog Content -->
        <VCard>
            <VImg rounded :src="props.preview" :style='props.style'></VImg>
        </VCard>
    </VDialog>
</template>

<style lang="scss" scope>
.preview:hover {
  border: 2px solid rgb(200, 200, 200);
  cursor: pointer;
}

@media only screen and (max-device-width: 960px) {
  .preview {
    margin-block: 0.5em;
    margin-inline: 0;
  }
}
</style>
