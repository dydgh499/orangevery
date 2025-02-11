<script lang="ts" setup>
import { isAbleModiy } from '@axios';
import { checkDirectObjectV2 } from '@validators';

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

const emits = defineEmits(['update:file', 'update:path']);

const upload = () => {
    if(files.value.length) {
        ext.value = getFileExtension(files.value[0].name)
        emits('update:file', files.value ? files.value[0] : files.value)
        emits('update:path', URL.createObjectURL(files.value[0]))
    }
}

const open = (link: string) => {
    window.open(link)
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
    <VRow>
        <VCol cols="12" md="12">
            <VAlert border="start" border-color="primary">
                <div>
                    <template v-if="isAbleModiy(0)">
                        <VFileInput 
                            style="padding-top: 0.5em;"
                            variant="underlined" accept=".jpg,.bmp,.png,.jpeg,.webp,.pdf" 
                            v-model="files" :label="label" @change="upload()" >
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
                    <div v-if="props.preview !== '/utils/icons/img-preview.svg'">
                        <VChip color="primary" style="margin-top: 1em; float: inline-end;" @click="open(props.preview)">
                            {{ label.replace('업로드', '') }} 확인하기
                        </VChip>
                    </div>
                    <div v-else>
                        <VChip color="default" style="margin-top: 1em; float: inline-end;">
                            {{ checkDirectObjectV2(label.replace(' 업로드', '')) }} 등록되지 않았습니다.
                        </VChip>
                    </div>
                </div>
            </VAlert>
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
