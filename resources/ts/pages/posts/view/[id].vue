
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { types } from '@/views/posts/useStore'
import type { Post, Tab } from '@/views/types'
import { axios } from '@axios'

const post = ref<Post>()
const errorHandler = <any>(inject('$errorHandler'))
const route = useRoute()

const tabs = <Tab[]>([
    { icon: 'fxemoji-notepage', title: '게시글 정보' },
])
watchEffect(() => {
    if(Number(route.params.id) && Number(route.params.id) > 0)
    {
        axios.get('/api/v1/manager/posts/' + Number(route.params.id))
        .then(r => {
            post.value = r.data
        })
        .catch(e => {
            const r = errorHandler(e)
        })
    }
})
</script>
<template>
    <section>
        <CreateForm :id="0" :path="'posts/view'" :tabs="tabs" :item="post">
            <template #view>
                <VWindowItem>                    
                    <VRow class="match-height">
                        <!-- 👉 개인정보 -->
                        <VCol cols="12" md="12">
                            <VCard>
                                <VCardItem>
                                    <VCardTitle>{{ types.find(obj => obj.id === post?.type)?.title }} </VCardTitle>
                                    <VRow class="pt-5">
                                        <CreateHalfVCol :mdl="2" :mdr="10">
                                            <template #name>제목</template>
                                            <template #input>
                                                <VTextField :value="post?.title"
                                                    prepend-inner-icon="ic-round-subtitles" persistent-placeholder
                                                    readonly />
                                            </template>
                                        </CreateHalfVCol>
                                    </VRow>
                                    <VRow class="pt-5">
                                        <CreateHalfVCol :mdl="2" :mdr="10" style='margin-bottom: 4em;'>
                                            <template #name>내용</template>
                                            <template #input>
                                                <div v-html="post?.content" class="ql-editor" style="border: 1px solid #d1d5db;">
                                                </div>
                                            </template>
                                        </CreateHalfVCol>
                                    </VRow>
                                </VCardItem>
                            </VCard>
                        </VCol>
                    </VRow>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
<style scoped>
.ql-editor {
  box-sizing: border-box;
  block-size: 100%;
  line-height: 1.42;
  outline: none;
  overflow-y: auto;
  padding-block: 12px;
  padding-inline: 15px;
  tab-size: 4;
  text-align: start;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
