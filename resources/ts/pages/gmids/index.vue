<script setup lang="ts">
import GmidDialog from '@/layouts/dialogs/users/GmidDialog.vue'
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useSearchStore } from '@/views/gmids/useStore'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'
import { avatars } from '@/views/users/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const gmidDialog = ref()
const password  = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="GMID 검색" :metas="[]" :add="false" add_name="" :date_filter_type="DateFilters.NOT_USE">
            <template #index_extra_field>            
                <VBtn prepend-icon="tabler-plus" @click="gmidDialog.show({id:0, profile_img: avatars[Math.floor(Math.random() * avatars.length)]})" size="small" 
                    v-if="getUserLevel() > 35"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    GMID 계정
                </VBtn>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" 
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"/>
            </template>
            <template #headers>
                <tr>
                    <th class='list-square' v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible">
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`" class="edit-link" @click="getUserLevel() >= 35 ? gmidDialog.show(item) : null">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'is_lock'">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                        {{ item[_key] ? 'LOCK' : 'X' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :item="item" :type="3"/>
                                </span>    
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <GmidDialog ref="gmidDialog" />
        <PasswordChangeDialog ref="password" />
    </div>
</template>
