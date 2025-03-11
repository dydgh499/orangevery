<script setup lang="ts">
import NotiDetailDialog from '@/layouts/dialogs/histories/NotiDetailDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/merchandises/noti-send-histories/ExtraMenu.vue'
import { useSearchStore } from '@/views/merchandises/noti-send-histories/useStore'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { notiSendHistoryInterface } from '@/views/transactions/transactions'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { notiSend } = notiSendHistoryInterface()
const notiDetail = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('notiDetail', notiDetail)
const search_placeholder = getUserLevel() >= 35 ? "MID, TID, 승인번호, 가맹점명 검색" : "승인번호 검색"

const httpCodeColor = (http_code: number) => {
    if (http_code < 300)
        return "success"
    else if (http_code < 500)
        return "warning"
    else
        return "error"
}

const batchRetry = async () => {
    notiSend(selected.value)
}

const getResponseBody = (body: string) => {
    try {
        return JSON.stringify(JSON.parse(body))
    }
    catch(e) {
        return body
    }
}

</script>
<template>
    <div>
        <BaseIndexView :placeholder="search_placeholder" :metas="[]" :add="false" add_name="노티이력"
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="false" :cus_filter="true"
                    :sales="true"/>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="gridicons:reply" @click="batchRetry()" size="small" v-if="getUserLevel() >= 35">
                    노티 재발송
                </VBtn>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.result_status" 
                    :items="[{ id: null, title: '전체' }, { id: 1, title: '성공' }, { id: 2, title: '실패' }]" label="응답결과" item-title="title"
                    item-value="id" @update:modelValue="store.updateQueryString({result_status: store.params.result_status})" 
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>
                    
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type" 
                    :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입" item-title="title"
                    item-value="id" @update:modelValue="store.updateQueryString({module_type: store.params.module_type})" 
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>

            </template>
            <template #headers>
                <tr>
                    <template v-for="(sub_header, index) in head.getSubHeaderComputed" :key="index">
                        <th :colspan="head.getSubHeaderComputed.length - 1 == index ? sub_header.width + 1 : sub_header.width"
                            class='list-square sub-headers' v-show="sub_header.width">
                            <span>{{ sub_header.ko }}</span>
                        </th>
                    </template>
                </tr>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id'">
                            <VCheckbox v-model="all_selected" class="check-label" />
                            <span>선택/취소</span>
                        </div>
                        <span v-else>
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
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == 'id'">
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                                <span v-else-if="_key == `trans_id`">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'message'">
                                    {{ getResponseBody(item[_key]) }}
                                </span>
                                <span v-else-if="_key == 'module_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == `is_cancel`">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                        {{ item[_key] ? '취소' : '승인' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == `http_code`">
                                    <VChip :color="httpCodeColor(item[_key])">
                                        {{ item[_key] }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <ExtraMenu :item="item"/>
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
        <NotiDetailDialog ref="notiDetail" />
    </div>
</template>
