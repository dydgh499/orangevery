
<script setup lang="ts">
import HeadOfficeWithdrawDialog from '@/layouts/dialogs/services/HeadOfficeWithdrawDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import { useSearchStore } from '@/views/services/head-office-withdraw/useStore'
import { realtimeMessage, realtimeResult } from '@/views/transactions/settle-histories/useCollectWithdrawHistoryStore'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const headOfficeWithdrawDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}
</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="입금계좌 검색" :metas="[]" :add="false" add_name="입금계좌" :date_filter_type="DateFilters.SETTLE_RANGE">
                <template #filter>
                </template>
                <template #index_extra_field>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                        :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"/>                        
                    <VBtn prepend-icon="carbon:batch-job" @click="headOfficeWithdrawDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                        지정계좌 이체
                    </VBtn>
                </template>
                <template #headers>
                    <tr>
                        <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                            <span>{{ header.ko }}</span>
                        </th>
                    </tr>
                </template>
                <template #body>
                    <template v-for="(item, index) in store.getItems" :key="index">
                        <tr>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                                    <b v-if="_key === 'withdraw_amount'" class="text-primary">
                                        {{ item[_key].toLocaleString() }}
                                    </b>
                                    <span v-else-if="_key == 'result_code'">
                                        <VChip :color="store.getSelectIdColor(realtimeResult(item[_key]))">
                                            {{ realtimeMessage(item) }}
                                        </VChip>
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
            <HeadOfficeWithdrawDialog ref="headOfficeWithdrawDialog"/>
        </div>
    </section>
</template>
