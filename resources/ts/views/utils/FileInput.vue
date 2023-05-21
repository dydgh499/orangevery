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
const loading = ref(true)

const zoomIn = () => {
    if(props.file.value != null)
    {
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
    if(file.value != null)
        props.file.value = file.value;
})
watchEffect(() => {
    if (typeof props.file.value == 'string') {
        priview.value = props.file.value
    }
    if (props.file.value == null || props.file.value.length == 0) {
        priview.value = '/images/img-preview.svg'
        loading.value = true
    }
    else
        loading.value = false
})
</script>
<template>
    <VRow no-gutters>
        <VCol cols="12" md="9">
            <VFileInput accept="image/*" show-size v-model="file" :label="label"
                @change="handleFileChange" prepend-icon="tabler-paperclip" />
        </VCol>
        <VCol cols="12" md="3">
            <VImg rounded :src="priview" class="preview" @click="zoomIn()" style="height: 163px;" />
            <VDialog v-model="visable">
                <!-- Dialog close btn -->
                <DialogCloseBtn @click="visable = !visable" />
                <!-- Dialog Content -->
                <VCard>
                    <VImg rounded :src="priview" style='width: 100%;'></VImg>
                </VCard>
            </VDialog>
        </VCol>
    </VRow>
</template>
