<script setup lang="ts">
import BrandNormalOverview from '@/pages/services/brands/BrandNormalOverview.vue';
import BrandEdit from '@/pages/services/brands/edit/[id].vue';
import router from '@/router';
import { getUserLevel } from '@axios';
import corp from '@corp';

if(getUserLevel() <= 35)
    location.href = '/login'
if(corp.id !== 1)
    router.replace('/services/brands/edit/' + corp.id)

</script>
<template>
    <BrandNormalOverview v-if="corp.id === 1"/>
    <BrandEdit v-else/>
</template>
