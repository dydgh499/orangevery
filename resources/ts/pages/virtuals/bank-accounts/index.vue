<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected';
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import ExtraMenu from '@/views/virtuals/bank-accounts/ExtraMenu.vue'
import { useSearchStore } from '@/views/virtuals/bank-accounts/useStore'
import { useRequestStore } from '@/views/request';
import { DateFilters } from '@core/enums'

const alert = <any>(inject('alert'))

const { request } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)
const snackbar = <any>(inject('snackbar'))

const batchRemove = async() => {
    if (await alert.value.show(`정말 ${selected.value.length}개의 입금계좌를 삭제하시겠습니까?`)) {
        const r = await request({ url: `/api/v1/manager/bank-accounts/batch-updaters/remove`, method: 'delete', data: {
            selected_idxs: selected.value
        } }, true)
        if (r.data.code === 1) {
            selected.value = []
            store.setTable()
        }
    }
}

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}
</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="계좌번호, 메모사항 검색" :metas="[]" :add="false" add_name="입금계좌" :date_filter_type="DateFilters.NOT_USE">
                <template #index_extra_field>
                <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''" item-title="title" item-value="id">
                    일괄삭제
                    <VIcon size="18" icon="tabler-trash" />
                </VBtn>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined" id="page-size-filter"
                    :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" 
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"/>
                </template>
            <template #headers>
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
                    <template v-for="(item, index) in store.getItems" :key="index">
                        <tr>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                                    <span v-if="_key == 'id'">
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                    </span>
                                    <span v-else-if="_key === 'checked'">
                                        <VChip :color="item[_key] ? 'success' : 'error'">
                                            {{ item[_key] ? '검증완료' : '미검증' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'note'" v-html="item[_key]" style="line-height: 2em;"></span>
                                    <span v-else-if="_key === 'extra_col'" v-if="item['withdraw_status'] != 1">
                                        <ExtraMenu :item="item"/>
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
<style scoped>
.total-table {
  inline-size: 100%;
}

.total-table > tr > th {
  padding-inline-end: 1em;
  text-align: start;
}

.total-table > tr > td {
  text-align: end;
}
</style>
