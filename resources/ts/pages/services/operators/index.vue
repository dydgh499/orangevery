<script setup lang="ts">
import { useSearchStore, operator_levels } from '@/views/services/operators/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import PasswordChangeDialog from '@/layouts/dialogs/PasswordChangeDialog.vue'

const { store, head, exporter } = useSearchStore()
const password = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = [
    {
        icon: 'tabler-user',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '21,459',
        percentage: +29,
        subtitle: 'Total Users',
    },
    {
        icon: 'tabler-user-plus',
        color: 'error',
        title: '금주 추가된 가맹점',
        stats: '4,567',
        percentage: +18,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-check',
        color: 'success',
        title: '금월 감소한 가맹점',
        stats: '19,860',
        percentage: -14,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'warning',
        title: '금주 감소한 가맹점',
        stats: '237',
        percentage: +42,
        subtitle: 'Last week analytics',
    },
]
</script>
<template>
    <div>
        <BaseIndexView placeholder="ID 및 성명 검색" :metas="metas" :add="true" add_name="운영자" :is_range_date="true">
            <template #filter>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                        class='list-square'>
                        <span>
                            {{ head.main_headers[index] }}
                        </span>
                    </th>
                </tr>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="!header.hidden" class='list-square'>
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                        </template>
                        <template v-else>
                            <td v-show="!_header.hidden" class='list-square'>
                                <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == `user_name`" class="edit-link" @click="store.edit(item['id'])">
                                    {{ item[_key] }}
                                </span>
                                <span v-else-if="_key == `level`">
                                    <VChip
                                        :color="store.getSelectIdColor(operator_levels.find(obj => obj.id === item[_key])?.id)">
                                        {{ operator_levels.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :id="item['id']" :type="2"></UserExtraMenu>
                                </span>
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                        </template>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <PasswordChangeDialog ref="password" />
    </div>
</template>
