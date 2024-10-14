<script lang="ts" setup>
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab'
import Editor from '@/layouts/utils/Editor.vue'
import { getUserLevel } from '@/plugins/axios'
import { types } from '@/views/posts/useStore'
import type { Post } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Post,
}

const route = useRoute()
const props = defineProps<Props>()
const store = useDynamicTabStore()

watchEffect(() => {
    if(props.item.id === 0 && getUserLevel() < 35)
        props.item.type = 2
})
watchEffect(() => {
    const type = types.find(obj => obj.id === props.item.type)
    if(type && (route.fullPath.includes('posts/create') || route.fullPath.includes('posts/edit'))) {
        const idx = store.tabs.findIndex(obj => obj.path === route.fullPath)
        if(idx !== -1) {
            store.tabs[idx].title = type.title + ` ${props.item.id ? `수정(#${props.item.id})` : '추가'}`
        }
    }
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle class="pb-5">게시글 작성</VCardTitle>
                    <VRow v-if="getUserLevel() >= 35">                        
                        <VCol md="1">
                            작성타입
                        </VCol>
                        <VCol md="2" cols="8">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="types" prepend-inner-icon="fxemoji-notepage" 
                                    item-title="title" item-value="id" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol md="1">
                            제목
                        </VCol>
                        <VCol md="11" cols="10">
                            <VTextField v-model="props.item.title"
                                prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                persistent-placeholder :rules="[requiredValidatorV2(props.item.title, '제목')]" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol>
                            <Editor :content="props.item.content" @update:content="props.item.content = $event"/>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
