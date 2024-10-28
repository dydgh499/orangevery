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
const vpdf = ref()
const current_pdf = ref()
const { pdf, pages, info } = usePDF(current_pdf)

const openFile = () => {
    if (props.preview != '/utils/icons/img-preview.svg') {
        window.open(props.preview);     
    }
}

watchEffect (async () => {
    if(props.ext === 'pdf'){        
        current_pdf.value = props.preview
        await nextTick();
        if(vpdf.value) {
            vpdf.value.reload()
        }
    }
})
</script>
<template>
    <section>        
        <template v-if="props.ext === 'pdf'">
            <div :style="props.previewStyle" class="pdf-container">
                <div :style="{ margin: '0.1em' }">
                    <VuePDF ref="vpdf" :pdf="pdf" fit-parent @click="openFile" />
                </div>
            </div>
        </template>
        <template v-else>
            <VImg rounded :src="props.preview" @click="imageDialog.show(props.preview)"
                :style="props.previewStyle" />
            <ImageDialog ref="imageDialog" :style="props.style" />
        </template>
    </section>
</template>
