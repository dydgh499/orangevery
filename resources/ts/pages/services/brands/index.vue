<script setup lang="ts">
import { useSearchStore } from '@/views/services/brands/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas: any[] = []
</script>
<template>
        <BaseIndexView placeholder="서비스명" :metas="metas" :add="true" add_name="서비스" :is_range_date="true">
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
                            <span v-else-if="_key == `dns`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `logo_img`" class="edit-link" @click="store.edit(item['id'])">
                                <img :src="item.logo_img" style="max-height: 60px; padding: 0.3em;"/>
                            </span>                            
                            <span v-else-if="_key == `company_nm`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>                            
                            <span v-else-if="_key == `main_color`">
                                <div :style="`width: 90%; height: 50%;background:`+item.theme_css.main_color"></div>
                            </span>
                            <span v-else-if="_key == `deposit_amount`"> 
                                    {{ item[_key].toLocaleString() }}
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
</template>
