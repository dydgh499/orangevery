<script setup lang="ts">
import { useSearchStore, types } from '@/views/posts/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import PostReplyView from '@/views/posts/PostReplyView.vue'
import ExtraMenu from '@/views/posts/ExtraMenu.vue'
import router from '@/router'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="게시글 검색" :metas="[]" :add="true" add_name="게시글" :is_range_date="null">
        <template #filter>
        </template>
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" :eager="true" />
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.type"
                :items="types" prepend-inner-icon="fxemoji-notepage" label="게시글 타입"
                item-title="title" item-value="id" style="min-width: 11em;"/>
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
            <template v-for="(item, index) in store.getItems" :key="index">
                <tr>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                            <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'type'">
                                <VChip :color="store.getSelectIdColor(types.find(obj => obj.id === item[_key])?.id)">
                                    {{ types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'title'" class="edit-link"
                                @click="router.push('/posts/view/' + item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item">
                                </ExtraMenu>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
                <PostReplyView v-for="(reply, _index) in item.replies" :key="_index" :post="reply" :depth="1">
                </PostReplyView>
            </template>
        </template>
    </BaseIndexView>
</template>
<style>
.title {
  inline-size: 80em;
  max-inline-size: 100em;
  padding-block: 2em;
  padding-inline: 0;
}
</style>
