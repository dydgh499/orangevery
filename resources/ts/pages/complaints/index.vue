<script setup lang="ts">
import { useSearchStore } from '@/views/complaints/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { issuers, complaint_types } from '@/views/complaints/useStore';

const { store } = useSearchStore()
provide('store', store)

const metas = []
const getComplainTypeColor = (type: number | null) => {
    const id = complaint_types.find(item => item.id === type)?.id
    if (id == 1)
        return "default"
    else if (id == 2)
        return "primary"
    else if (id == 3)
        return "success"
    else if (id == 4)
        return "info"
}
</script>
<template>
    <BaseIndexView placeholder="TID 검색" :metas="[]" :add="true" add_name="민원">
        <template #filter>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'> 
                    <span v-if="key == 'id'" class="edit-link" @click="store.edit(user.id)">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `type`">
                        <VChip :color="getComplainTypeColor(user[key])">
                            {{ complaint_types.find(item => item.id === user[key])?.title }}
                        </VChip>
                    </span>
                    <span v-else-if="key == `issuer_id`">
                            {{ issuers.find(item => item.id === user[key])?.title }}
                    </span>
                    <span v-else-if="key == `is_deposit`">
                        <VChip :color="store.booleanTypeColor(!user[key])">
                            {{ user[key] ? '입금' : '미입금' }}
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
