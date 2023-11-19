<script lang="ts" setup>

const props = defineProps({
    file: {
        type: File,
        required: false,
    },    
    preview: {
        type: String,
        required: false,
    },
    name: {
        type: Object,
        required: true,
    },
    og_description: {
        type: Object,
        required: true,
    },
})

const files = ref(props.file)
const preview = ref<string>(props.preview == undefined ? '/utils/icons/img-preview.svg' : props.preview)

const emits = defineEmits(['update:file']);
watchEffect(() => {
    if(files.value != undefined)
    {
        preview.value = files.value.length ? URL.createObjectURL(files.value[0]) : '/utils/icons/img-preview.svg'
        emits('update:file', files.value ? files.value[0] : files.value)
    }
})
</script>
<template>
    <VCol md="6" style="padding: 0.5em;">
        <VFileInput accept="image/*" v-model="files" :label="`미리보기 이미지(1260px * 630px)`"
            prepend-icon="tabler-camera-up">
            <template #selection="{ fileNames }">
                <template v-for="fileName in fileNames" :key="fileName">
                    <VChip label size="small" variant="outlined" color="primary" class="me-2">
                        {{ fileName }}
                    </VChip>
                </template>
            </template>
        </VFileInput>
        <VRow no-gutters class="pt-5">
            <VTextarea v-model="props.og_description.value" counter label="내용"/>
        </VRow>
    </VCol>
    <VCol md="6" style="padding: 0.5em;">
        <div class="preview-box">
            <div class="preview-image-box" :style="`background-image: url(${preview});`">
            </div>
            <div class="preview-text-box">
                <p class="title">{{ props.name.value }}</p>
                <p>{{ props.og_description.value }}</p>
            </div>
        </div>
    </VCol>
</template>
<style lang="scss" scoped>
.preview-box {
  border: 1px solid #e5e5e5;
  border-radius: 8px;
}

.preview-image-box {
  background-position: 50%;
  background-repeat: no-repeat;
  background-size: cover;
  block-size: 210px;
  border-block-end: 1px solid #e5e5e5;
  border-start-end-radius: 8px;
  border-start-start-radius: 8px;
  inline-size: 100%;
}

.preview-text-box {
  padding-block: 8px;
  padding-inline: 12px;
}

.description {
  overflow: visible;
  font-size: 13px;
  margin-block-start: -4px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.title {
  font-weight: 500;
}
</style>
