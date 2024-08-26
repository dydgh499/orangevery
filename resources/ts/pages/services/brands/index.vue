<script setup lang="ts">
import BrandNormalOverview from '@/pages/services/brands/BrandNormalOverview.vue';
import router from '@/router';
import { getUserLevel } from '@axios';
import corp from '@corp';

onMounted(() => {
    if(getUserLevel() >= 40) {
        if(corp.id !== 1)
            router.replace('/services/brands/edit/' + corp.id)
    }
    else
        location.href = '/login'
})
</script>
<template>
    <BrandNormalOverview v-if="corp.id === 1"/>
</template>
