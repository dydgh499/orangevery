<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'
interface Props {
    metas: any[],
}
const props = defineProps<Props>()
const store = <any>(inject('store'))

onMounted (() => {
    watchEffect(async () => {        
        if(store.getChartProcess())
            store.is_skeleton = false
    })
})
</script>
<template>
    <VCol v-for="meta in props.metas" :key="meta.title" cols="12" sm="6" lg="3">
    <VCard>
        <VCardText class="d-flex justify-space-between">
            <div v-if="store.is_skeleton">
                <span>{{ meta.title }}</span>
                <div class="d-flex align-center gap-2 my-1">
                    <SkeletonBox :width="'3em'"/>
                    <SkeletonBox :width="'5em'"/>
                </div>
            </div>
            <div v-else>
                <span>{{ meta.title }}</span>
                <div class="d-flex align-center gap-2 my-1">
                    <h6 class="text-h6">
                        {{ meta.stats }}
                    </h6>
                    <span :class="meta.percentage > 0 ? 'text-success' : 'text-error'">({{ meta.percentage }}%)</span>
                </div>
                <span>{{ meta.subtitle }}</span>
            </div>

            <VAvatar rounded variant="tonal" :color="meta.color" :icon="meta.icon" />
        </VCardText>
    </VCard>
</VCol>
</template>
