<script lang="ts" setup>

const props = defineProps({
    file: {
        type: Object,
        required: true,
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

const file = ref();
const priview = ref<string>('/images/img-preview.svg')

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
    <VCol md="6">
        <VFileInput accept="image/*" v-model="file" :label="`미리보기 이미지(1260px * 630px)`" @change="handleFileChange"
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
    <VCol md="6">
        <div class="preview-box">
            <div class="preview-image-box" :style="`background-image: url(${priview});`">
            </div>
            <div class="preview-text-box">
                <p class="title">{{ props.name.value }}</p>
                <p>{{ props.og_description.value }}</p>
            </div>
        </div>
    </VCol>
</template>
<style lang="scss">
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
  overflow: hidden;
  font-size: 13px;
  margin-block-start: -4px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.title {
  font-weight: 500;
}
</style>
