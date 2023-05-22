
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue';
import MchtOverview from '@/views/merchandises/MchtOverview.vue';
import PayModuleOverview from '@/views/pay-modules/PayModuleOverview.vue';
import CreateForm from '@/views/utils/CreateForm.vue'

const router = useRouter()
const path = 'merchandises'
const tabs = [
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'ph-buildings', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const item = reactive<Merchandise>({
    acct_bank_cd: '000',
    acct_bank_nm: '은행명',
    passbook_img: '/images/img-preview.svg',
    id_img: '/images/img-preview.svg',
    contract_img: '/images/img-preview.svg',
    bsin_lic_img: '/images/img-preview.svg',
    is_show_fee: false,
    use_dupe_trx: false,
    pay_day_limit: 0,
    pay_year_limit: 0,
    abnormal_trans_limit: 0,
})
const id = ref<number>(router.currentRoute.value.params.id || 0)

watchEffect(() => {
    const route_id = router.currentRoute.value.params.id
    id.value = route_id ? route_id : 0
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <UserOverview :item="item" :id="id" />
                </VWindowItem>
                <VWindowItem>
                    <MchtOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <PayModuleOverview :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
