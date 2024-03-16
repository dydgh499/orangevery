<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { history_types } from '@/views/services/operator-histories/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { OperatorHistory } from '@/views/types'
import corp from '@corp'

const visible = ref(false)
const history = ref(<OperatorHistory>({}))

const store = <any>(inject('store'))
const { mchts, all_sales } = useSalesFilterStore()
const { pgs, pss, settle_types, terminals, finance_vans } = useStore()

const changeKeyName = (history_detail: any) => {
    const keys = [
        'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id',    
        'sales0_fee','sales1_fee','sales2_fee','sales3_fee','sales4_fee','sales5_fee',
    ]
    keys.forEach((key) => {
        if("validation.attributes." + key in history_detail) {
            const level = key.slice(0, 6)
            let key_name = corp.pv_options.auth.levels[level+'_name']
            if(key.includes('fee')) {
                key_name += ' 수수료';
                history_detail['validation.attributes.'+key] *= 100
            }
            history_detail[key_name] =  history_detail['validation.attributes.'+key]
            delete history_detail['validation.attributes.'+key]
        }
    })
    replaceIdtoName(history_detail)
    return history_detail
}

const replaceIdtoName = (history_detail: any) => {
    const levels = corp.pv_options.auth.levels
    const _replaceToName = (lists: any[], key: string, name: string) => {
        if(key in history_detail) {
            const value = lists.find(obj => obj.id == history_detail[key])
            history_detail[key] = value ? value[name] : history_detail[key]
        }
    }
    _replaceToName(pgs, "PG사", 'pg_name')
    _replaceToName(pss, "구간", 'name')
    _replaceToName(mchts, "가맹점", 'mcht_name')
    _replaceToName(terminals, "장비", 'name')
    _replaceToName(settle_types, "정산일", 'name')    
    _replaceToName(finance_vans, "금융벤 ID", 'nick_name')

    _replaceToName(all_sales[0], levels.sales0_name, 'sales_name')
    _replaceToName(all_sales[1], levels.sales1_name, 'sales_name')
    _replaceToName(all_sales[2], levels.sales2_name, 'sales_name')
    _replaceToName(all_sales[3], levels.sales3_name, 'sales_name')
    _replaceToName(all_sales[4], levels.sales4_name, 'sales_name')
    _replaceToName(all_sales[5], levels.sales5_name, 'sales_name')
}

const show = (_history: any) => {
    visible.value = true
    history.value = _history
    history.value.before_history_detail = changeKeyName(history.value.before_history_detail)
    history.value.after_history_detail = changeKeyName(history.value.after_history_detail)
    if(history.value.history_target === '구분 정보') {
        if(history.value.before_history_detail['타입'] === 0) {
            delete history.value.after_history_detail['타입']
            delete history.value.before_history_detail['타입']
            history.value.after_history_detail['구분 타입'] = '장비'
            history.value.before_history_detail['구분 타입'] = '장비'
        }
        else if(history.value.before_history_detail['타입'] === 1) {
            delete history.value.after_history_detail['타입']
            delete history.value.before_history_detail['타입']
            history.value.after_history_detail['구분 타입'] = '커스텀 필터'
            history.value.before_history_detail['구분 타입'] = '커스텀 필터'
        }
    }
}

const title = computed(() => {
    if(history.value)
        return history.value.nick_name+"님 활동이력"
    else
        return ""
})

const isChangeColor = (before: any, after: any) => {
    if(before !== after)
        return 'list-square text-error'
    else
        return 'list-square'
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="1200">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard :title="title">
            <VCardText>
                <b>
                    <div class="d-flex justify-space-between">
                        <h6 class="text-base font-weight-semibold me-3">
                            {{ history.history_target }}
                            {{ history.history_title ? " - "+history.history_title : ''}}
                            <VChip :color="store.getSelectIdColor(history.history_type)">
                                {{ history_types.find(history_type => history_type['id'] === history.history_type)?.title  }}
                            </VChip>   
                        </h6>
                        <span class="text-sm">
                            활동시간: {{ history.created_at }}
                        </span>
                    </div>
                </b>
                <br>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>적용대상</th>
                            <th class='list-square'>이전 값</th>
                            <th class='list-square'>변경 값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(hist, key) in history.after_history_detail" :key="key">
                            <th class='list-square'>{{ key }}</th>
                            <td class='list-square'>{{ history.before_history_detail[key] }}</td>
                            <td :class="isChangeColor(history.before_history_detail[key], hist)">{{ hist }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
