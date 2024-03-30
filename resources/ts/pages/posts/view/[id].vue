
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PostContentView from '@/views/posts/PostContentView.vue'
import { types } from '@/views/posts/useStore'
import type { Post, Tab } from '@/views/types'
import router from '@/router'
import { axios } from '@axios'

const post = ref<Post>()
const errorHandler = <any>(inject('$errorHandler'))
const route = useRoute()

const tabs = <Tab[]>([
    { icon: 'fxemoji-notepage', title: '게시글 정보' },
])
watchEffect(() => {
    if(Number(route.params.id) && Number(route.params.id) > 0) {
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
                                <PostContentView :post="post" :title="types.find(obj => obj.id === post?.type)?.title"/>
                            </VCard>
                        </VCol>
                    </VRow>
                </VWindowItem>
            </template>
        </CreateForm>
        <VCard style="margin-top: 1em;" slot="button">
            <VCol class="d-flex gap-4">
                <VBtn type="button" color="primary" style="margin-left: auto;" @click="router.back()">
                    뒤로가기
                    <VIcon size="22" icon="tabler:arrow-back" />
                </VBtn>
            </VCol>
        </VCard>
    </section>
</template>
