<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { useRequestStore } from '@/views/request';
import { replaceVariable } from '@/views/services/activity-histories/useStore';
import { useSearchStore } from '@/views/services/book-applies/useStore';
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';

if(getUserLevel() <= 35)
    location.href = '/login'

const { request, remove } = useRequestStore()
const { store, head, exporter, metas } = useSearchStore()
store.params.dest_type = 1

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const destory = async (id: number) => {
    let dest_type = ''
    if(store.params.dest_type === 0)
        dest_type = 'salesforces'
    else if(store.params.dest_type === 1)
        dest_type = 'merchandises'
    else if(store.params.dest_type === 2)
        dest_type = 'payment_modules'
        else if(store.params.dest_type === 3)
        dest_type = 'noti_urls'
    else 
        return

    remove(`/services/book-applies/${dest_type}`, {
        id: id,
    }, false)
}
</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="변경 값 검색" :metas="metas" :add="false" add_name="예약변경" :date_filter_type="DateFilters.DATE_RANGE">
                <template #filter>
                </template>
                <template #index_extra_field>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.dest_type" :items="[{ id: 0, title: '영업라인' }, {id: 1, title:'가맹점'}, {id: 2, title:'결제모듈'}, {id: 3, title:'노티주소'}]"
                        density="compact" variant="outlined" item-title="title" item-value="id" label="변경대상"
                        @update:modelValue="store.updateQueryString({dest_type: store.params.dest_type})"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"
                    />
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                        :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"/>
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
                        <template v-for="(header, key) in head.flat_headers">
                            <template v-if="store.params.dest_type < 2 && header.ko === '별칭'">
                            </template>
                            <th v-else :key="key" v-show="header.visible" class='list-square'>
                                {{ header.ko }}
                            </th>
                        </template>
                    </tr>
                </template>
                <template #body>
                    <template v-for="(item, index) in store.getItems" :key="index">
                        <tr>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <template v-if="store.params.dest_type < 2 && _key === 'note'">
                                </template>
                                <td v-else v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                                    <span v-if="_key == 'id'">
                                        #{{ item[_key] }}
                                    </span>
                                    <span v-else-if="_key === 'change_status'">
                                        <VChip :color="store.booleanTypeColor(!item[_key])" >
                                            {{ item[_key] ? '변경완료' : '변경예약' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'extra_col'">
                                        <VBtn size="small" type="button" color="error" @click="destory(item['id'])">
                                            삭제
                                            <VIcon size="22" icon="tabler-trash"/>
                                        </VBtn>
                                    </span>
                                    <span v-else-if="_key === 'apply_data'">
                                        {{ replaceVariable(item[_key]) }}
                                    </span>
                                    <span v-else>
                                        {{ item[_key] }}
                                    </span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>
            </BaseIndexView>
        </div>
    </section>
</template>
