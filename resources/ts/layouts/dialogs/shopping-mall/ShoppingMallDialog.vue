<script setup lang="ts">
import CategoryOverview from '@/views/merchandises/shopping-mall/categories/CategoryOverview.vue';
import ProductOverview from '@/views/merchandises/shopping-mall/products/ProductOverview.vue';

const visible = ref(false)
const mcht_id = ref(<number>(-1))
const show = (_mcht_id: number) => {
    mcht_id.value = _mcht_id
    visible.value = true
}
const tab = ref(0)
const tabs = [
    { icon: 'tabler:shopping-bag', title: '상품 관리' },
    { icon: 'tabler:category', title: '카테고리 관리' },
]
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="1400">
        <DialogCloseBtn @click="visible = false" />
        <VCard>
            <VCardText>
                <VTabs v-model="tab" grow>
                    <VTab v-for="(t, index) in tabs" :key="index" :class="{
                        'v-slide-group-item--active v-tab--selected': tab === index,
                        'text-secondary': tab !== index
                    }">
                        <VIcon :size="20" :icon="t.icon" style="margin-right: 0.5em;"/>
                        <span>{{ t.title }}</span>
                    </VTab>
                </VTabs>
                <VForm ref="vForm" class="mt-5">
                    <VWindow v-model="tab" :touch="false">
                        <VWindowItem>
                            <ProductOverview/>
                        </VWindowItem> 
                        <VWindowItem>
                            <CategoryOverview/>
                        </VWindowItem>
                    </VWindow>
                </VForm>
            </VCardText>
        </VCard>
    </VDialog>
</template>
