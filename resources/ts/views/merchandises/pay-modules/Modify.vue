
<script setup lang="ts">
import MidCreateDialog from '@/layouts/dialogs/pay-modules/MidCreateDialog.vue';
import CreateForm from '@/layouts/utils/CreateForm.vue';
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue';
import { defaultItemInfo } from '@/views/merchandises/pay-modules/useStore';

interface Props {
    able_mcht_chanage: boolean,
}

const props = defineProps<Props>()
const { path, item } = defaultItemInfo()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const midCreateDlg = ref(null)
const route = useRoute()

provide('midCreateDlg', midCreateDlg)
</script>
<template>
    <section>
        <MidCreateDialog ref="midCreateDlg" />
        <CreateForm :id="Number(route.params.id)" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard :item="item" :able_mcht_chanage="props.able_mcht_chanage"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
