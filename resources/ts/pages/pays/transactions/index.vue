<script setup lang="ts">
import CancelPartDialog from '@/layouts/dialogs/transactions/CancelPartDialog.vue'

import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'
import { module_types } from '@/views/services/options/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/options/useStore'
import { useSearchStore, installments, trxStatuses } from '@/views/pays/transactions/useStore'

import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'
import { Transaction } from '@/views/types'
import { useRequestStore } from '@/views/request'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { post } = useRequestStore()
const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, pss } = useStore()

const cancelPart = ref()

const getTdColor = (item: Transaction) => {
    let style = item.is_cancel ? 'color:red;' : ''
    return style
}


const payCanceled = async (item: Transaction) => {
    const amount = await cancelPart.value.show(item.amount)
    if (amount == 0)
        return
    else {
        if (await alert.value.show('정말 PG사를 통해 결제를 취소하시겠습니까?')) {
            const params = <any>({
                pmod_id: item.pmod_id,
                amount: item.amount,
                trx_id: item.trx_id,
                only: false,
            })
            try {
                const r = await post('/api/v1/transactions/pay-cancel', params)
                if (r.status === 201)
                    snackbar.value.show('성공하였습니다.', 'success')
                else
                    snackbar.value.show(r.data.message, 'error')
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
    }

}

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="승인번호, 결제모듈 별칭 검색" 
            :metas="[]"
            :add="false" add_name="매출" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" 
                    v-model="store.params.page_size" 
                    :items="[10, 20, 30, 50, 100, 200]" 
                    label="조회 개수" 
                    @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
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
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible"
                        class='list-square'>
                        <span>
                            <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                                <VCheckbox v-model="all_selected" class="check-label" />
                                <span>선택/취소</span>
                            </div>
                            <span v-else>
                                {{ header.ko }}
                            </span>
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <td v-if="_header.visible" :style="getTdColor(item)" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <template v-if="getUserLevel() >= 35">
                                        <VCheckbox v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                            <span class="edit-link" @click="store.edit(item['id'])">
                                                #{{ item[_key]}}
                                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                    상세보기
                                                </VTooltip>
                                            </span>
                                    </template>
                                    <span v-else>
                                        #{{ item[_key]}}
                                    </span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip
                                    :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'trx_status'">
                                <VChip :color="trxStatuses.find(obj => obj.id === item[_key])?.color">
                                    {{ trxStatuses.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <VBtn 
                                    v-if="item['is_cancel'] === 0"
                                    @click="payCanceled(item)" size="small">
                                    승인취소
                                </VBtn>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <CancelPartDialog ref="cancelPart" />
    </div>
</template>
