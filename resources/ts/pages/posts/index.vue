<script setup lang="ts">
import { useDynamicTabStore } from '@/@core/utils/dynamicTab'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import router from '@/router'
import PostReplyView from '@/views/posts/PostReplyView.vue'
import { getContentTooltip, moveContent, types, useSearchStore } from '@/views/posts/useStore'
import { Searcher } from '@/views/searcher'
import { allLevels, getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const route = useRoute()
const store = Searcher('posts')
const { head } = useSearchStore()
const { tabs } = useDynamicTabStore()

const exporter = async () => {
    const keys = Object.keys(head.flat_headers.value)
    const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
    let datas = r.data.content;
    for (let i = 0; i < datas.length; i++) {
        datas[i]['type'] = types.find(types => types.id === datas[i]['type'])?.title
        datas[i] = head.sortAndFilterByHeader(datas[i], keys)
    }
    head.exportToExcel(datas)
}

if(route.query.type !== undefined)
    store.params.type = Number(route.query.type)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

watchEffect(() => {
    if(store.params.type !== undefined) {
        const type = types.find(obj => obj.id === store.params.type)
        if(type && (route.fullPath.includes('/posts?type=') && route.fullPath.includes(store.params.type as string))) {
            const idx = tabs.findIndex(obj => obj.path === route.fullPath)
            if(idx !== -1) {
                tabs[idx].title = type.title
            }
        }
    }
})
</script>
<template>
    <BaseIndexView placeholder="게시글 검색" :metas="[]" :add="true" :add_name="getUserLevel() < 30 ? '1:1 문의' : '게시글'" :date_filter_type="DateFilters.NOT_USE">
        <template #filter>
        </template>
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.type"
                :items="[{id:null, title:'전체'}].concat(types)" prepend-inner-icon="fxemoji-notepage" label="게시글 타입"
                item-title="title" item-value="id" @update:modelValue="store.updateQueryString({type: store.params.type})"
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <span :class="key === 'title' ? 'title' : ''">
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <template v-for="(item, index) in store.getItems.value" :key="index">
                <tr>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                            <span v-if="_key == `id`" class="edit-link" @click="moveContent(item, store)">
                                #{{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    {{ getContentTooltip(item) }}
                                </VTooltip>
                            </span>
                            <span v-else-if="_key == 'type'">
                                <VChip :color="store.getSelectIdColor(types.find(obj => obj.id === item[_key])?.id)">
                                    {{ types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'writer'">
                                <div>
                                    <template v-if="item['level'] >= 35">                           
                                        <VChip :color="store.getAllLevelColor(item['level'])">
                                            운영자
                                        </VChip>
                                    </template>
                                    <template v-else>
                                        <span style="margin: 0.1em;">{{ item[_key] }}</span>
                                        <br>
                                        <VChip :color="store.getAllLevelColor(item['level'])">
                                            {{ allLevels().find(obj => obj.id === item['level'])?.title }}
                                        </VChip>
                                    </template>
                                </div>
                            </span>
                            <span v-else-if="_key == 'title'" class="edit-link"
                                @click="moveContent(item, store)">
                                {{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    {{ getContentTooltip(item) }}
                                </VTooltip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <VBtn size="small" type="button" color="primary" @click="router.push('/posts/reply?parent_id=' + item['id'])">
                                    <VIcon size="22" icon="gridicons:reply" />
                                    답변하기
                                </VBtn>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
                <PostReplyView v-for="(reply, _index) in item.replies" :key="_index" :post="reply" :depth="1"/>
            </template>
        </template>
    </BaseIndexView>
</template>
<style scoped>
:deep(.title) {
  inline-size: 55%;
  max-inline-size: 100em;
  padding-block: 2em;
  padding-inline: 0;
}
</style>
