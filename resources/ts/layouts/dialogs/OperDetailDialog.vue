<script setup lang="ts">
import { OperatorHistory } from '@/views/types'
import { history_types } from '@/views/services/operator-histories/useStore'
import corp from '@corp'

const visible = ref(false)
const history = ref(<OperatorHistory>({}))
const store = <any>(inject('store'))

const changeKeyName = () => {
    const keys = [
        'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id',    
        'sales0_fee','sales1_fee','sales2_fee','sales3_fee','sales4_fee','sales5_fee',
    ]
    keys.forEach((key) => {
        if("validation.attributes." + key in history.value.history_detail) {
            const level = key.slice(0, 6)
            let key_name = corp.pv_options.auth.levels[level+'_name']
            if(key.includes('fee'))
                key_name += ' 수수료';

            history.value.history_detail[key_name] = history.value.history_detail['validation.attributes.'+key]            
            delete history.value.history_detail['validation.attributes.'+key]
        }
    })
}

const show = (_history: any) => {
    visible.value = true
    history.value = _history
    changeKeyName()
}

const title = computed(() => {
    if(history.value)
        return history.value.nick_name+"님 활동이력"
    else
        return ""
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="800">
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
                            <th class='list-square'>적용값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(hist, key) in history.history_detail" :key="key">
                            <th class='list-square'>{{ key }}</th>
                            <td class='list-square'>{{ hist }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
