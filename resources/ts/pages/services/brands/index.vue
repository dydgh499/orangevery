
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import BrandAuthOverview from '@/views/services/brands/BrandAuthOverview.vue'
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue'
import BrandOptionOverview from '@/views/services/brands/BrandOptionOverview.vue'
import BrandIdentityOverview from '@/views/services/brands/identity-auth-infos/BrandIdentityOverview.vue'

import corp from '@/plugins/corp'
import BrandOverview from '@/views/services/brands/BrandOverview.vue'
import { defaultItemInfo } from '@/views/services/brands/useStore'
import type { Tab } from '@/views/types'
import { getUserLevel } from '@axios'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '운영사정보' },
    { icon: 'tabler-color-filter', title: '테마디자인' },
    { icon: 'tabler-table-options', title: '추가옵션' },
    { icon: 'tabler:auth-2fa', title: '인증옵션' },
])
if(getUserLevel() == 50)
    tabs.push({ icon: 'carbon:two-factor-authentication', title: '유료옵션' })

</script>
<template>
    <section>
        <CreateForm :id="corp.id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <BrandOverview :item="item" :id="corp.id" />
                </VWindowItem>
                <VWindowItem>
                    <BrandDesignOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <BrandOptionOverview :item="item.pv_options" :key="item.id"/>
                </VWindowItem>
                <VWindowItem>
                    <BrandIdentityOverview :item="item.identity_auth_infos" :key="item.id"/>
                </VWindowItem>
                <VWindowItem v-if="getUserLevel() == 50">
                    <BrandAuthOverview :item="item.pv_options"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
