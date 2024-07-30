<script setup lang="ts">
import IPDetailDialog from '@/layouts/dialogs/services/IPDetailDialog.vue';
import LastLoginDialog from '@/layouts/dialogs/services/LastLoginDialog.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { getUserLevel, pay_token, user_info } from '@/plugins/axios';
import { connection_types, getLevelByChipColor, useSearchStore } from '@/views/services/abnormal-connection-histories/useStore';
import { allLevels } from '@axios';
import { DateFilters } from '@core/enums';

const { 
    store, 
    head, 
    exporter, 
    metas,
} = useSearchStore()

store.params.connection_type = null

const iPDetailDialog = ref()
const lastLoginDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}
// last-login
</script>
<template>
    <div>
        <BaseIndexView placeholder="접근시도 IP, 대상, 값" :metas="metas" :add="false" add_name="" :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.connection_type" density="compact" variant="outlined" item-title="title" item-value="id"
                    style="max-width: 10em;"
                    :items="[{id:null, title:'전체'}].concat(connection_types)" label="접근 타입" eager  @update:modelValue="store.updateQueryString({page_size: store.params.connection_type})" />
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined" id="page-size-filter"
                    :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
                    
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == 'connection_type'">
                                    <VChip :color="store.getSelectIdColor(item[_key])">
                                        {{ connection_types.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key === 'target_level'">
                                    <VChip v-if="item[_key]"
                                        :color="store.getSelectIdColor(getLevelByChipColor(item[_key]))">
                                            {{ allLevels().find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                    <span v-else>세션없음</span>
                                </span>
                                <div v-else-if="_key === 'target_key'" class="content">
                                    {{ item[_key]  }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>{{ item[_key] }}</span>
                                    </VTooltip>
                                </div>
                                <div v-else-if="_key === 'target_value'" class="content">
                                    {{ item[_key]  }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition">
                                        <span>{{ item[_key] }}</span>
                                    </VTooltip>
                                </div>
                                <span v-else-if="_key === 'request_ip'">
                                    <div style="display: inline-flex; flex-direction: row;">
                                        <div style="display: inline-flex; flex-direction: column; justify-content: space-evenly;">
                                            <b>{{ item[_key]  }}</b>
                                            <span v-if="item['mobile_type'] !== ''">
                                                ({{ item['mobile_type'] }})
                                            </span>
                                        </div>
                                    </div>
                                </span>
                                <span v-else-if="_key === 'action'" v-html="item[_key].replace('(', '<br>(')"></span>
                                <span v-else-if="_key === 'comment'" v-html="item[_key].replace('(', '<br>(')"></span>
                                <span v-else-if="_key === 'extra_cols'">
                                    <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: space-evenly; padding: 0.25em;">
                                        <VBtn size="small" variant="tonal" @click="iPDetailDialog.show(item['request_detail'])" style="margin-bottom: 0.25em;">상세보기</VBtn>
                                        <VBtn size="small" variant="tonal" color="error" @click="lastLoginDialog.show(item['request_ip'])">로그인 목록</VBtn>
                                    </div>
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
        <IPDetailDialog ref="iPDetailDialog"/>
        <LastLoginDialog ref="lastLoginDialog"/>
    </div>
</template>
<style scoped>
.content {
  overflow: hidden;
  inline-size: 410px !important;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
}
</style>
