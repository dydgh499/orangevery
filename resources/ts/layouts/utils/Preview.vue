<script setup lang="ts">
import ImageDialog from '@/layouts/dialogs/ImageDialog.vue'
import VuePdfApp from "vue3-pdf-app";
import "vue3-pdf-app/dist/icons/main.css";

interface Props {
    previewStyle: string,
    preview: string,
    style: string,
    ext: string,
}

const props = defineProps<Props>()
const imageDialog = ref()

</script>
<template>
    <section>
        <template v-if="props.ext === 'pdf'">
            <vue-pdf-app :pdf="props.preview" />
        </template>
        <template v-else>
            <VImg rounded :src="props.preview" class="preview" @click="imageDialog.show(props.preview)"
                :style="props.previewStyle" />
            <ImageDialog ref="imageDialog" :style="props.style" />
        </template>
    </section>
</template>
