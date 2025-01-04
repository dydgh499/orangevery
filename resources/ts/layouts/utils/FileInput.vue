<script lang="ts" setup>
import Preview from '@/layouts/utils/Preview.vue'
import { isAbleModiy } from '@axios'

interface Props {
    preview: string,
    label: string,
}
const getFileExtension = (file_name: string) => {
    if(file_name.length > 0) {
        const file_names = file_name.split('?')
        const dot = file_names[0].lastIndexOf('.') + 1
        return file_names[0].substring(dot, file_names[0].length).toLowerCase()
    }
    else
        return 'png'
}

const props = defineProps<Props>()
const files = ref(<File[]>([]))
const ext = ref<string>('')
const preview_style = `
    border: 1px solid rgb(130, 130, 130);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
`;

const emits = defineEmits(['update:file', 'update:path']);

const remove = () => {
    ext.value = 'svg' 
    emits('update:path', '/utils/icons/img-preview.svg')
}

const upload = () => {
    if(files.value.length) {
        ext.value = getFileExtension(files.value[0].name)
        emits('update:file', files.value ? files.value[0] : files.value)
        emits('update:path', URL.createObjectURL(files.value[0]))
    }
}
watchEffect(() => {
    if(files.value.length === 0)
        ext.value = getFileExtension(props.preview)
})
watchEffect(() => {
    if(files.value.length === 0 && props.preview.includes('blob:'))
        emits('update:path', '/utils/icons/img-preview.svg')
})
</script>
<template>
    <VRow no-gutters style="align-items: flex-start;">
        <VCol cols="12" md="6">
            <template v-if="isAbleModiy(0)">
                <VFileInput accept=".jpg,.bmp,.png,.jpeg,.webp,.pdf" v-model="files" :label="label"  @change="upload()" >
                    <template #selection="{ fileNames }">
                    <template v-for="fileName in fileNames" :key="fileName">
                        <VChip label size="small" variant="outlined" color="primary" class="me-2">
                            {{ fileName }}
                        </VChip>
                    </template>
                </template>
                </VFileInput>
                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                    jpg,bmp,png,jpeg,webp,pdf만 업로드 가능합니다.
                </VTooltip>
            </template>
            <span v-else>{{ label.replace(' 업로드', '') }}</span>
        </VCol>
        <VCol cols="12" md="6">
            <DialogCloseBtn class="close-btn" @click="remove()" v-if="props.preview !== '/utils/icons/img-preview.svg' && files.length === 0 && isAbleModiy(0)"/>
            <Preview :preview="props.preview" :style="``" :preview-style="preview_style" class="preview" :ext="ext"/>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.close-btn) {
  inset-block-start: auto !important;
  inset-inline-end: auto !important;
  margin-inline-end: 2em !important;
}
</style>
