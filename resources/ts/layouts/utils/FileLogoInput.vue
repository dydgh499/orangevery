<script lang="ts" setup>
import Preview from '@/layouts/utils/Preview.vue'
import { extensionValidator } from '@validators'

interface Props {
    preview: string,
    label: string,
    validates: string[],
}
const props = defineProps<Props>()

const files = ref(<File[]>([]))
const preview = ref<string>('/icons/img-preview.svg')
const previewStyle = `
    border: 2px solid rgb(130, 130, 130);
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

const extentionRule = computed(() => {
    return (value: File[]) => extensionValidator(value, props.validates);
})
</script>
<template>
    <VCol>
        <VFileInput accept="image/*" v-model="files" :label="props.label" :rules="[extentionRule]"
            prepend-icon="tabler-camera-up">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
        <Preview :preview="preview" :style="``" :preview-style="previewStyle" class="preview"/>
    </VCol>
</template>
