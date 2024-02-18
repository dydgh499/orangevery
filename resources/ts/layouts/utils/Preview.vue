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
const config = ref({
  sidebar: {
    viewThumbnail: false,
    viewOutline: false,
    viewAttachments: false,
  },
  secondaryToolbar: {
    secondaryPresentationMode: true,
    secondaryOpenFile: false,
    secondaryPrint: false,
    secondaryDownload: false,
    secondaryViewBookmark: false,
    firstPage: false,
    lastPage: false,
    pageRotateCw: false,
    pageRotateCcw: false,
    cursorSelectTool: false,
    cursorHandTool: false,
    scrollVertical: false,
    scrollHorizontal: false,
    scrollWrapped: false,
    spreadNone: false,
    spreadOdd: false,
    spreadEven: false,
    documentProperties: false,
  },
  toolbar: {
    toolbarViewerLeft: {
      findbar: false,
      previous: false,
      next: false,
      pageNumber: false,
    },
    toolbarViewerRight: {
      presentationMode: true,
      openFile: false,
      print: false,
      download: true,
      viewBookmark: false,
    },
    toolbarViewerMiddle: {
      zoomOut: false,
      zoomIn: false,
      scaleSelectContainer: false,
    },
  },
  errorWrapper: true,
})

const openFile = () => {
    if (props.preview != '/utils/icons/img-preview.svg') {
        window.location.href = props.preview;
        window.location.replace(props.preview)
        window.open(props.preview);     
    }
}
</script>
<template>
    <section>
        <template v-if="props.ext === 'pdf'">
            <vue-pdf-app :pdf="props.preview" class="preview pdf-viewer" :style="props.previewStyle" page-scale="page-height"
                :config="config" @click="openFile()"/>
        </template>
        <template v-else>
            <VImg rounded :src="props.preview" class="preview" @click="imageDialog.show(props.preview)"
                :style="props.previewStyle" />
            <ImageDialog ref="imageDialog" :style="props.style" />
        </template>
    </section>
</template>
<style scoped>
/* stylelint-disable-next-line selector-pseudo-class-no-unknown */
:deep(.pdf-viewer) {
  block-size: 20em;
}

/* stylelint-disable-next-line selector-id-pattern */
:deep(.pdf-app #viewerContainer) {
  overflow: hidden !important;
}

/* stylelint-disable-next-line selector-id-pattern */
:deep(.pdf-app #toolbarViewer) {
  display: none !important;
}

:deep(.page) {
  border-width: 3px !important;
}
</style>
