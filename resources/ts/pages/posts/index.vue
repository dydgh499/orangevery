<script setup lang="ts">
import { useSearchStore, types } from '@/views/posts/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';

const { store } = useSearchStore()
provide('store', store)


const getPostTypeColor = (type: number) => {
    const id = types.find(item => item.id === type)?.id
    if (id == 0)
        return "default"
    else if (id == 1)
        return "primary"
    else if (id == 2)
        return "success"
}
</script>
<template>
    <BaseIndexView placeholder="게시글 검색" :metas="[]" :add="true" add_name="게시글">
        <template #filter>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden"> {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden">
                    <span v-if="key == 'id'" class="edit-link" @click="store.edit(user.id)">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `type`">
                        <VChip :color="getPostTypeColor(user[key])">
                            {{ types.find(item => item.id === user[key])?.title }}
                        </VChip>
                    </span>
                    <span v-else>
                        {{ user[key] }}
                    </span>
                </td>
            </tr>
        </template>
    </BaseIndexView>
</template>
