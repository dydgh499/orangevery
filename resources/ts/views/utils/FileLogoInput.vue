<script lang="ts" setup>

const props = defineProps({
    file: {
        type: Object,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
});
const file = ref();
const priview = ref<string>('/images/img-preview.svg')
const label = ref<string>(props.label)
const visable = ref(false)

const zoomIn = () => {
    if (props.file.value != null) {
        if (props.file.value != '/images/img-preview.svg')
            visable.value = !visable.value
    }
}
const handleFileChange = (event: Event) => {
    const inputElement = event.target as HTMLInputElement;
    if (inputElement.files && inputElement.files.length > 0) {
        priview.value = URL.createObjectURL(inputElement.files[0]);
    }
}
watchEffect(() => {
    if (file.value != null)
        props.file.value = file.value;
})
watchEffect(() => {
    if (typeof props.file.value == 'string') {
        priview.value = props.file.value
    }
    if (props.file.value == null || props.file.value.length == 0) {
        priview.value = '/images/img-preview.svg'
    }
})
</script>
<template>
    <VCol>
        <VFileInput accept="image/*" v-model="file" :label="label" @change="handleFileChange"
            prepend-icon="tabler-camera-up">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
        <VImg rounded :src="priview" class="preview" @click="zoomIn()" />
        <VDialog v-model="visable">
            <!-- Dialog close btn -->
            <DialogCloseBtn @click="visable = !visable" />
            <!-- Dialog Content -->
            <VCard>
                <VImg rounded :src="priview" style='width: 100%;'></VImg>
            </VCard>
        </VDialog>
    </VCol>
</template>
<style lang="scss" scoped>
.preview {
  border: 2px solid rgb(238, 238, 238);
  border-radius: 0.5em;
  float: inline-end;
  margin-block-end: 0.5em;
  margin-block-start: 0.5em;
  margin-inline-start: auto;
  max-inline-size: 10em;
}

.preview:hover {
  border: 2px solid rgb(200, 200, 200);
  cursor: pointer;
}
</style>
