
<script setup lang="ts">
import { VForm } from 'vuetify/components';
import UserOverview from '@/views/merchandises/UserOverview.vue';
import MchtOverview from '@/views/merchandises/MchtOverview.vue';
import PayModuleOverview from '@/views/pay-modules/PayModuleOverview.vue';
import AlertDialog from '@/views/utils/AlertDialog.vue';
import LoadingDialog from '@/views/utils/LoadingDialog.vue';
import Snackbar from '@/views/utils/Snackbar.vue';
import { useUpdateStore } from '@/views/merchandises/useMchtStore'

const { store } = useUpdateStore()
const alert = ref(null)
const snackbar = ref(null)

onMounted(() => {
    store.alert = alert.value
    store.snackbar = snackbar.value
    store.get('/api/v1/manager/' + store.path + '/' + store.id)
});

const vForm = ref<VForm>()
const tabs = [
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'ph-buildings', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const tab = ref(0);
function isPayModule() {
    return tab.value == 2 ? true : false;
}
</script>
<template>
    <section>

        <VTabs v-model="tab" class="v-tabs-pill">
            <VTab v-for="(t, index) in tabs" :key="tab.icon">
                <VIcon :size="18" :icon="t.icon" class="me-1" />
                <span>{{ t.title }}</span>
            </VTab>
        </VTabs>

        <VCard class="mt-5">
            <VCardText>
                <VForm ref="vForm">
                    <VWindow v-model="tab">
                        <VWindowItem>
                            <UserOverview :item="store.item" :id="store.id" />
                        </VWindowItem>
                        <VWindowItem>
                            <MchtOverview :item="store.item" />
                        </VWindowItem>
                        <VWindowItem>
                            <PayModuleOverview :id="store.id" />
                        </VWindowItem>
                    </VWindow>
                </VForm>
            </VCardText>
        </VCard>
        <!-- 👉 submit -->
        <VCard style="margin-top: 1em;" v-show="!isPayModule()">
            <VCol class="d-flex gap-4">
                <VBtn type="submit" style="margin-left: auto;" @click="store.update(vForm)">
                    수정
                    <VIcon end icon="tabler-checkbox" />
                </VBtn>
                <VBtn color="secondary" variant="tonal" @click="vForm.reset()">
                    리셋
                    <VIcon end icon="tabler-arrow-back" />
                </VBtn>
            </VCol>
        </VCard>
        <Snackbar ref="snackbar" />
        <AlertDialog ref="alert" />
        <LoadingDialog ref="loading" />
    </section>
</template>
