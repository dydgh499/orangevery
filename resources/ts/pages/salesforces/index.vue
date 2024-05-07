<script setup lang="ts">
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue';
import SalesforceNormalOverview from '@/pages/salesforces/SalesforceNormalOverview.vue';
import SalesforceOverlapOverview from '@/pages/salesforces/SalesforceOverlapOverview.vue';
import corp from '@corp';

const password = ref()
provide('password', password)

</script>
<template>
    <div>
        <SalesforceOverlapOverview v-if="corp.pv_options.paid.sales_parent_structure"/>
        <SalesforceNormalOverview v-else/>
        <PasswordChangeDialog ref="password" />
    </div>
</template>
