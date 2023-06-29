<script lang="ts" setup>
import Preview from '@/layouts/utils/Preview.vue'

const props = defineProps({
    file: {
        type: File,
        required: false,
    },
    preview: {
        type: String || null,
        default: '/icons/img-preview.svg',
        required: false,
    },
    label: {
        type: String,
        required: true,
    },
});
const files = ref(props.file)
const preview = ref<string>(props.preview)
const previewStyle = `
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    margin-block: 0;
    margin-inline: 0.5em;
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
    <VRow no-gutters>
        <VCol cols="12" md="9">
            <VFileInput accept="image/*" show-size v-model="files" :label="label" prepend-icon="tabler-paperclip" >
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
            <Preview :preview="preview" :style="``" :preview-style="previewStyle"></Preview>
        </VCol>
    </VRow>
</template>
