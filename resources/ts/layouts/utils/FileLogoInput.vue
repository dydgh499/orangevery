<script lang="ts" setup>
import Preview from '@/layouts/utils/Preview.vue';

const props = defineProps({
    file: {
        type: File,
        required: false,
    },
    preview: {
        type: String || null,
        required: false,
    },
    label: {
        type: String,
        required: true,
    },
});
const files = ref(props.file)
const preview = ref<string>('/icons/img-preview.svg')
const previewStyle = `
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    float: inline-end;
    margin-block-end: 0.5em;
    margin-block-start: 0.5em;
    margin-inline-start: auto;
    max-inline-size: 10em;
`;

const emits = defineEmits(['update:file']);
watchEffect(() => {
    if(files.value != undefined)
    {
        preview.value = files.value.length ? URL.createObjectURL(files.value[0]) : '/icons/img-preview.svg'
        emits('update:file', files.value ? files.value[0] : files.value)
    }
})
watchEffect(() => {
    preview.value = props.preview
})
</script>
<template>
    <VCol>
        <VFileInput accept="image/*" v-model="files" :label="props.label"
            prepend-icon="tabler-camera-up">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
        <Preview :preview="preview" :style="`height: 512px;`" :preview-style="previewStyle"></Preview>
    </VCol>
</template>
