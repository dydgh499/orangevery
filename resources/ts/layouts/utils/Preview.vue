<script setup lang="ts">
import ImageDialog from '@/layouts/dialogs/ImageDialog.vue'

interface Props {
    previewStyle: string,
    preview: string,
    style: string,
}

const props = defineProps<Props>()
const imageDialog = ref()

</script>
<template>
    <section>
        <VImg rounded :src="props.preview" class="preview" @click="imageDialog.show(props.preview)" :style="props.previewStyle" />
        <ImageDialog ref="imageDialog" :style="props.style"/>
    </section>
</template>
