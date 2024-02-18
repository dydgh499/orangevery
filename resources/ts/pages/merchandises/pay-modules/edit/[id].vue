
<script setup lang="ts">
import Modify from '@/views/merchandises/pay-modules/Modify.vue'
</script>
<template>
    <section>
        <Suspense>
            <Modify :able_mcht_chanage="true"/>
        </Suspense>
    </section>
</template>
