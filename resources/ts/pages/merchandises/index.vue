<script setup lang="ts">
import ShoppingMallDialog from '@/layouts/dialogs/shopping-mall/ShoppingMallDialog.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { feeCalcMenual, merchant_statuses, MerchantStatusColor, useSearchStore } from '@/views/merchandises/useStore';
import { selectFunctionCollect } from '@/views/selected';
import { useStore } from '@/views/services/pay-gateways/useStore';
import UserExtraMenu from '@/views/users/UserExtraMenu.vue';

import BatchDialog from '@/layouts/dialogs/BatchDialog.vue';
import InitPayVerficationDialog from '@/layouts/dialogs/users/InitPayVerficationDialog.vue';
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue';

import { useFeeCalculatorStore } from '@/views/merchandises/feeCalculatorStore';
import { module_types } from '@/views/merchandises/pay-modules/useStore';
import { getUserLevel, isAbleModiy } from '@axios';
import { DateFilters, ItemTypes } from '@core/enums';
import corp from '@corp';

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { getBrandSettleFee, getSalesSettleInfo, settleFeeValidate } = useFeeCalculatorStore()
const { pgs, pss, settle_types, cus_filters } = useStore()
const password  = ref()
const batchDialog = ref()
const shoppingMallDialog = ref()
const initPayVerficationDialog = ref()

const alert = <any>(inject('alert'))



provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

onMounted(() => {
    watchEffect(async() => {
        if(store.getChartProcess() === false && getUserLevel() > 10) {
            const r = await store.getChartData()
            if(r.status === 200) {
                metas[0]['stats'] = r.data.this_month_add.toLocaleString()
                metas[1]['stats'] = (r.data.this_month_del * -1).toLocaleString()
                metas[2]['stats'] = r.data.this_week_add.toLocaleString()
                metas[3]['stats'] = (r.data.this_week_del * -1).toLocaleString()  
                metas[0]['percentage'] = store.getPercentage(r.data.this_month_add, r.data.total)
                metas[1]['percentage'] = store.getPercentage((r.data.this_month_del * -1), r.data.total)
                metas[2]['percentage'] = store.getPercentage(r.data.this_week_add, r.data.total)
                metas[3]['percentage'] = store.getPercentage((r.data.this_week_del * -1), r.data.total)            
            }
        }
    })
})
</script>
<template>
    <div>
        <BaseIndexView 
            placeholder="아이디, 상호, 연락처, 대표자명, 사업자번호, 예금주, 계좌번호 검색" 
            :sub_search_placeholder="getUserLevel() > 10 ? 'MID, TID 검색' : ''"
            :sub_search_name="getUserLevel() > 10 ?  '결제모듈' : ''"
            :metas="metas" :add="isAbleModiy(0)" 
            add_name="가맹점"
            :date_filter_type="DateFilters.NOT_USE"
            >
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() > 10">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터" item-title="title"
                                item-value="id"
                                @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                        <VCol cols="6" sm="3" v-if="getUserLevel() > 10">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.merchant_status"
                                :items="[{ id: null, title: '전체' }].concat(merchant_statuses)" label="가맹점 상태" item-title="title"
                                item-value="id" @update:modelValue="[store.updateQueryString({ merchant_status: store.params.merchant_status })]"/>
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small"
                    style="margin: 0.25em;">
                    일괄작업
                </VBtn>
                <VBtn
                    color="info" v-if="corp.pv_options.paid.use_shop && (getUserLevel() >= 35 || getUserLevel() === 10)"
                    prepend-icon="material-symbols:work-history-outline" @click="shoppingMallDialog.show(-1)"
                    size="small" :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    쇼핑몰 관리
                </VBtn>
                <VBtn prepend-icon="ic:outline-help" @click="alert.show(feeCalcMenual(), 'v-dialog-lg')" size="small" v-if="getUserLevel() >= 35">
                    수익률 계산식
                </VBtn>
                <div :style="$vuetify.display.smAndDown ? 'margin-top: 1em' : ''">
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.settle_hold" label="지급보류건 조회"
                        color="error" @update:modelValue="store.updateQueryString({ settle_hold: store.params.settle_hold })" v-if="getUserLevel() >= 35"/>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_lock" label="잠금계정 조회"
                        color="warning" @update:modelValue="store.updateQueryString({ is_lock: store.params.is_lock })" v-if="getUserLevel() >= 35"/>
                </div>
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
                            <VCheckbox v-if="getUserLevel() >= 35" v-model="all_selected" class="check-label"/>
                            <span v-if="getUserLevel() >= 35">선택/취소</span>
                            <span v-else>{{ header.ko }}</span>
                        </div>
                        <span v-else>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index" :class="settleFeeValidate(item) === false ? 'text-error' : ''">
                    <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false && settleFeeValidate(item) === false">
                        <div>
                            수수료율 재점검 필요({{item['mcht_name']}})
                            <br>
                            <br>
                            <span v-if="corp.pv_options.paid.fee_input_mode === false">
                                구간 수수료율 = {{ item['payment_modules'].length ? Number(pss.find(ps => ps.id === item['payment_modules'][0].ps_id)?.trx_fee) : 0}} %<br>
                            </span>
                            본사 수익률 = {{ getBrandSettleFee(item, getSalesSettleInfo(item)).settle_fee }} %<br>
                            영업라인 수익률 합계 = {{ getSalesSettleInfo(item).sales_total_fee }} %<br>
                        </div>
                    </VTooltip>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span class="edit-link" @click="store.edit(item['id'])">
                                        #{{ item[_key] }}
                                        <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                            상세보기
                                        </VTooltip>
                                    </span>
                                </div>
                            </span>
                            <span v-else-if="_key == `user_name`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    상세보기
                                </VTooltip>
                            </span>
                            <span v-else-if="(_key as string).includes('_fee') && (_key as string).includes('_sales')">
                                <VChip v-if="item[`sales${(_key as string).replace(/\D/g, '')}_id`] && (corp.pv_options.free.use_fee_detail_view || item[_key])">
                                    {{ (item[_key] * 100).toFixed(3) }} %
                                </VChip>
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="corp.pv_options.paid.fee_input_mode === false && $vuetify.display.smAndDown === false && (_key as string).includes('sales')">
                                    수익률: {{  item[`sales${(_key as string).replace(/\D/g, '')}_settlement_fee`] }} %
                                </VTooltip>
                            </span>
                            <span v-else-if="(_key as string).includes('_fee')">
                                <VChip v-if="corp.pv_options.free.use_fee_detail_view || item[_key]">
                                    {{ (item[_key] as number).toFixed(3) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'settle_types'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ settle_types.find(obj => obj.id === payment_module['settle_type'])?.name }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'p_mids'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ payment_module['p_mid'] }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'mids'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ payment_module['mid'] }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'tids'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ payment_module['tid'] }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'module_types'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ module_types.find(module => module.id === payment_module['module_type'])?.title }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'pgs'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ pgs.find(pg => pg.id === payment_module['pg_id'])?.pg_name }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'contract_img'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ pss.find(ps => ps.id === payment_module['ps_id'])?.trx_fee }} %
                                    </option>
                                </select>
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    본사 수익률: {{ getBrandSettleFee(item, getSalesSettleInfo(item)).settle_fee }}%
                                </VTooltip>
                            </span>
                            <span v-else-if="_key == 'pss'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ pss.find(ps => ps.id === payment_module['ps_id'])?.name }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'serial_nums'">
                                <select class="custom-select">
                                    <option v-for="(payment_module, key) in item['payment_modules']" :key="key">
                                        {{ payment_module['serial_num'] }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'resident_num'">
                                <span>{{ item['resident_num_front'] }}</span>
                                <span style="margin: 0 0.25em;">-</span>
                                <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                <span v-else>{{ item['resident_num_back'] }}</span>
                            </span>
                            <span v-else-if="_key == 'settle_hold_s_dt'">
                                <VChip color="error" v-if="item[_key]">
                                    {{ item[_key]  }}
                                </VChip>
                                <span v-else>-</span>
                            </span>
                            <span v-else-if="_key == 'notis'">
                                <select class="custom-select">
                                    <option v-for="(noti, key) in item['notis']" :key="key">
                                        {{ noti['note'] }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'merchant_status'">
                                <VChip :color="MerchantStatusColor(item[_key])">
                                    {{ merchant_statuses.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'is_lock'">
                                <VChip :color="store.booleanTypeColor(item[_key])">
                                    {{ item[_key] ? 'LOCK' : 'X' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <UserExtraMenu :item="item" :type="0" :key="item['id']" @init:pay_verfication="initPayVerficationDialog.show($event)"/>
                            </span>
                            <div v-else-if="_key == 'note'">
                                <VTextarea v-if="item[_key]" 
                                    variant="solo"
                                    style="width: 20em;"
                                    :rows="3" 
                                    row-height="5"
                                    v-model="item[_key]" :readonly="true"
                                />
                                <span v-else>{{ item[_key] }}</span>
                            </div>
                            <span v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                {{ item[_key] }}
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
         <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.Merchandise"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()"/>
        <PasswordChangeDialog ref="password" />
        <ShoppingMallDialog ref="shoppingMallDialog"/>
        <InitPayVerficationDialog ref="initPayVerficationDialog" />
    </div>
</template>
