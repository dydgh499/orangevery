<script lang="ts" setup>
import { QuillEditor } from '@vueup/vue-quill'
import BlotFormatter from 'quill-blot-formatter/dist/BlotFormatter'
import ImageUploader from 'quill-image-uploader'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { axios } from '@axios'

interface Props {
    content: string
}
const props = defineProps<Props>();
const snackbar = <any>(inject('snackbar'))
const modules = [
    {
        name: 'blotFormatter',
        module: BlotFormatter,
        options: {/* options */ }
    },
    {
        name: 'imageUploader',
        module: ImageUploader,
        options: {
            upload(file: any) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append("file", file);
                    axios({
                        headers: { "Content-Type": "multipart/form-data", },
                        url: '/api/v1/manager/posts/upload',
                        method: 'post',
                        data: formData,
                    })
                    .then(r => { 
                        snackbar.value.show('성공하였습니다.', 'success')
                        resolve(r.data.url);
                    })
                    .catch(e => { 
                        snackbar.value.show(e.response.data.message, 'error')
                        reject("Upload failed")
                    })
                })
            }
        }
    }
]

const content = ref(<string>(""))
const emits = defineEmits(['update:content']);
watchEffect(() => {
    if(content.value != null)
        emits('update:content', content.value)
    if(content.value === "" && props.content !== "")
        content.value = props.content
})
</script>
<template>
    <div style="display: block;">
        <QuillEditor v-model:content="content" contentType="html" :modules="modules" theme="snow" toolbar="full" placeholder="게시글 내용을 작성하세요."/>
    </div>
</template>

<style scoped>
:deep(.ql-container) {
  border: 1px solid rgba(var(--v-border-color), 0.5);
  min-block-size: 20em;
}

:deep(.ql-toolbar) {
  border: 1px solid rgba(var(--v-border-color), 0.5);
}
</style>
