<script setup lang="ts">
import CancelPartDialog from '@/layouts/dialogs/transactions/CancelPartDialog.vue'

import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/useStore'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, metas, dataToChart, table } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, pss, cus_filters } = useStore()


const cancelPart = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

provide('cancelPart', cancelPart)

store.params.level = 10
store.params.issuer = '전체'


const getTdColor = (item: any) => {
    let style = item['is_cancel'] ? 'color:red;' : ''
    const cus_filter = cus_filters.find(obj => obj.id === item.custom_id)
    if(cus_filter && (cus_filter.color !== '#000000' && cus_filter.color !== '#00000000'))
        style += `background:${cus_filter.color};`
    return style
}

onMounted(() => {
    watchEffect(async () => {
        await dataToChart()
    })
})
</script>
<template>
    <div>
        <VRow>
            <VCol v-for="meta in metas" :key="meta.title" cols="12" sm="6" :lg="3">
                <VCard>
                    <VCardText class="d-flex">
                        <div v-if="store.getSkeleton()">
                            <span v-html="meta.title"></span>
                            <div class="d-flex align-center gap-2 my-1">
                                <SkeletonBox :width="'3em'"/>
                                <SkeletonBox :width="'5em'"/>
                            </div>
                        </div>
                        <div v-else style="width: 100%;">
                            <div style="display: flex;width: 100%; justify-content: space-between;">
                                <span>
                                    {{meta.title}}
                                </span>
                            </div>
                            <div class="d-flex align-center gap-2 my-1">
                                <h6 class="text-h6" v-html="meta.stats"></h6>
                                <span v-if="meta.percentage"
                                    :class="meta.percentage > 0 ? 'text-success' : 'text-error'">
                                    ({{ meta.percentage }}%)
                                </span>
                            </div>
                            <span v-html="meta.subtitle"></span>
                        </div>
                        <VAvatar rounded variant="tonal" :color="meta.color" :icon="meta.icon" style="margin-left: auto;"/>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
        <br>
        <BaseIndexView placeholder="상호, MID, TID, 승인번호, 거래번호, 결제모듈 별칭, 주민번호, 사업자번호, 휴대폰 번호 검색" 
            :metas="[]"
            :add="false" add_name="매출" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="false">
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
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
