<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { useRequestStore } from '@/views/request';
import { useSearchStore } from '@/views/salesforces/fee-table/useStore';
import { salesLevels } from '@axios';
import { DateFilters } from '@core/enums';

const { store, head, exporter } = useSearchStore()
const { post } = useRequestStore()

const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const sales_levels = salesLevels()
store.params.level = sales_levels[0].id as number

const setSalesFee = async (item: any) => {
    const params = {}
    const keys = Object.keys(item)
    for(var i=0; i < keys.length; i++) {
        if(keys[i].includes('sales')) {
            params[keys[i]] = item[keys[i]]
        }
    }
    if(await alert.value.show('정말 수수료율을 일괄적용하시겠습니까?')) {
        const r = await post('/api/v1/manager/salesforces/fee-table', params)
        if (r.status == 201)
            snackbar.value.show('성공하였습니다.', 'success')
        else
            snackbar.value.show(r.data.message, 'error') 
    }
}

const setVisiable = (idx: number, value: boolean) => {
    head.headers["sales"+idx+"_fee"].visible = value
    head.headers["sales"+idx+"_name"].visible = value
}

const onInputChange = computed((value) => {
    console.log(value)
    return value
})

onMounted(() => {
    watchEffect(() => {
        if(store.params.level === 13)
            setVisiable(0, true)
        else
            setVisiable(0, false)
        store.params.level > 15 ? setVisiable(1, false) : setVisiable(1, true)
        store.params.level > 17 ? setVisiable(2, false) : setVisiable(2, true)
        store.params.level > 20 ? setVisiable(3, false) : setVisiable(3, true)
        store.params.level > 25 ? setVisiable(4, false) : setVisiable(4, true)
    })
})
</script>
<template>
    <div>
        <BaseIndexView 
                placeholder="아이디, 상호 검색" 
                :metas="[]" :add="false" 
                add_name=""
                :date_filter_type="DateFilters.NOT_USE"
            >
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect v-model="store.params.level" :items="sales_levels" density="compact" label="조회 등급"
                    item-title="title" item-value="id"
                    @update:modelValue="store.updateQueryString({ level: store.params.level })" />

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
                            <span v-if="(_key as string).includes('_fee')">
                                <VTextField 
                                    :value="item[_key]"
                                    variant="underlined"
                                    type="number" suffix="%"
                                    style="width: 5em; margin: auto;"
                                    />
                            </span>
                            <span v-else-if="_key === 'extra_cols'">
                                <VBtn size="small" variant="tonal" @click="setSalesFee(item)" style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                    모두적용
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
    </div>
</template>
