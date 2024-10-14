<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { getUserLevel, user_info } from '@axios';

interface Props {
    metas: any[],
}
const props = defineProps<Props>()
const store = <any>(inject('store'))

const lgSize = computed(() => {
    return (store.base_url === '/api/v1/manager/transactions' && (getUserLevel() == 10 && !user_info.value.is_show_fee)) ? 4 : 3;
})

onMounted (() => {
    watchEffect(async () => {        
        if(store.getChartProcess())
            store.setSkeleton(false)
    }) 

})
</script>
<template>
    <VCol v-for="meta in props.metas" :key="meta.title" cols="12" sm="6" :lg="lgSize">
    <VCard>
        <VCardText class="d-flex justify-space-between">
            <div v-if="store.getSkeleton()">
                <span v-html="meta.title"></span>
                <div class="d-flex align-center gap-2 my-1">
                    <SkeletonBox :width="'3em'"/>
                    <SkeletonBox :width="'5em'"/>
                </div>
            </div>
            <div v-else>
                <span v-html="meta.title"></span>
                <div class="d-flex align-center gap-2 my-1">
                    <h6 class="text-h6" v-html="meta.stats"></h6>
                    <span :class="meta.percentage > 0 ? 'text-success' : 'text-error'" v-if="meta.percentage">({{ meta.percentage }}%)</span>
                </div>
                <span v-html="meta.subtitle"></span>
            </div>
            <VAvatar rounded variant="tonal" :color="meta.color" :icon="meta.icon" />
        </VCardText>
    </VCard>
</VCol>
</template>
