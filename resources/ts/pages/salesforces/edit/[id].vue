
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { getUserLevel, user_info } from '@/plugins/axios'
import { isFixplus } from '@/plugins/fixplus'
import FixplusOverview from '@/views/salesforces/FixplusOverview.vue'
import SalesforceOverview from '@/views/salesforces/SalesforceOverview.vue'
import { defaultItemInfo } from '@/views/salesforces/useStore'
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue'
import type { Salesforce, Tab } from '@/views/types'
import UserOverview from '@/views/users/UserOverview.vue'
import corp from '@corp'

const {path, item } = defaultItemInfo()
const id = ref<number>(0)
const route = useRoute()

const _isAbleThemeDesign = (_item: Salesforce) => {
    if(corp.pv_options.paid.use_sales_dns && _item.level === 30) {
        if(getUserLevel() >= 35 || (_item.id === user_info.value.id)) {
            return true
        }
    }
    return false
}

const isAbleThemeDesign = computed(() => {
    return _isAbleThemeDesign(item)
})

const getTab = computed(() => {
    console.log(item)
    const tabs = <Tab[]>([])
    if(isFixplus()) {
        tabs.push({ icon: 'tabler-user-check', title: '영업점정보' })
    }
    else {
        tabs.push(...[
            { icon: 'tabler-user-check', title: '개인정보' },
            { icon: 'ph-buildings', title: '영업점정보' },
        ])
        if(_isAbleThemeDesign(item))
            tabs.push({ icon: 'tabler-color-filter', title: '테마디자인' })
    }
    return tabs
})

watchEffect(() => {
    id.value = Number(route.params.id) || 0
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="getTab" :item="item">
            <template #view>
                <template v-if="isFixplus()">
                    <VWindowItem>
                        <FixplusOverview :item="item"/>
                    </VWindowItem>
                </template>
                <template v-else>
                    <VWindowItem>
                        <UserOverview :item="item" :key="id" :is_mcht="false" />
                    </VWindowItem>
                    <VWindowItem>
                        <SalesforceOverview :item="item" :key="id"/>
                    </VWindowItem>
                    <VWindowItem v-if="isAbleThemeDesign">
                        <BrandDesignOverview :item="item" :key="id"/>
                    </VWindowItem>
                </template>
            </template>
        </CreateForm>
    </section>
</template>
