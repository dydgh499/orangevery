<script lang="ts" setup>
import Preview from '@/layouts/utils/Preview.vue'

interface Props {
    preview: string,
    label: string,
}

const getFileExtension = (file_name: string) => {
    const dot = file_name.lastIndexOf('.') + 1
    return file_name.substring(dot, file_name.length).toLowerCase()
}

const props = defineProps<Props>()
const files = ref(<File[]>([]))
const preview = ref<string>(props.preview)
const ext = ref<string>('')
const previewStyle = `
    border: 2px solid rgb(130, 130, 130);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
`;

const emits = defineEmits(['update:file']);

watchEffect(() => {
    if(files.value != undefined) {
        if(files.value.length) 
            ext.value = getFileExtension(files.value[0].name)

        preview.value = files.value.length ? URL.createObjectURL(files.value[0]) : '/utils/icons/img-preview.svg'
        emits('update:file', files.value ? files.value[0] : files.value)
    }
})
watchEffect(() => {
    preview.value = props.preview
    ext.value = getFileExtension(props.preview)
})
</script>
<template>
    <VRow no-gutters>
        <VCol cols="12" md="9">
            <VFileInput accept="*" show-size v-model="files" :label="label" prepend-icon="tabler-paperclip" >
                <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
            </VFileInput>
        </VCol>
        <VCol cols="12" md="3">
            <Preview :preview="preview" :style="``" :preview-style="previewStyle" class="preview" :ext="ext"/>
        </VCol>
    </VRow>
</template>
