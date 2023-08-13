<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

</script>
<template>
    <VCol class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <SkeletonBox :width="'8em'"/>
            </div>
            <div style="font-weight: bold;">
                <SkeletonBox :width="'5em'"/>
            </div>
        </div>
        <VAvatar rounded variant="tonal" icon="ic-outline-payments" />
    </VCol>
    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <SkeletonBox :width="'8em'"/>
            </div>
            <div style="font-weight: bold;">
                <SkeletonBox :width="'5em'"/>
            </div>
        </div>
    </VCol>
    <VCol style="padding-top: 0;" class="d-flex justify-space-between small-font">
        <div>
            <div class="small-font">
                <SkeletonBox :width="'8em'"/>
            </div>
            <div style="font-weight: bold;">
                <SkeletonBox :width="'5em'"/>
            </div>
        </div>
        <span style=" margin-top: auto; font-weight: 600;" class="small-font">
            <SkeletonBox :width="'7em'"/>
        </span>
    </VCol>
</template>
<style>
.small-font {
  font-size: 0.9em;
}
</style>
