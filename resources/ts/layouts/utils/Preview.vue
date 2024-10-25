<script setup lang="ts">
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue';
import { VuePDF, usePDF } from '@tato30/vue-pdf';

interface Props {
    previewStyle: string,
    preview: string,
    style: string,
    ext: string,
}

const props = defineProps<Props>()
const imageDialog = ref()

const current_pdf = ref()
const { pdf, pages, info } = usePDF(current_pdf)

const openFile = () => {
    if (props.preview != '/utils/icons/img-preview.svg') {
        window.open(props.preview);     
    }
}

watchEffect (() => {
    if(props.ext === 'pdf'){        
        current_pdf.value = props.preview
    }
})
</script>
<template>
    <section>        
        <template v-if="props.ext === 'pdf'">
            <div :style="props.previewStyle">
                <VuePDF ref="vpdf" :pdf="pdf" fit-parent :style="{ margin: '0.1em' }" @click="openFile" />
            </div>
        </template>
        <template v-else>
            <VImg rounded :src="props.preview" @click="imageDialog.show(props.preview)"
                :style="props.previewStyle" />
            <ImageDialog ref="imageDialog" :style="props.style" />
        </template>
    </section>
</template>
<style scoped>
/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.pdf-viewer) {
  block-size: 18em;
}

/* stylelint-disable-next-line selector-id-pattern */
:deep(.pdf-app #viewerContainer) {
  overflow: hidden !important;
  inset-block-start: 0 !important;
}

/* stylelint-disable-next-line selector-id-pattern */
:deep(.pdf-app #toolbarViewer) {
  display: none !important;
}

:deep(#toolbarContainer) {
  block-size: 0 !important;
}

:deep(.page) {
  border-width: 3px !important;
}
</style>
