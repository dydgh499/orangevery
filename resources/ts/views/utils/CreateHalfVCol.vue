<script setup lang="ts">
</script>
<template>
    <VCol cols="12">
        <VRow no-gutters>
            <VCol cols="12" md="3">
                <label>
                    <slot name="name"></slot> 
                </label>
            </VCol>
            <VCol cols="12" md="9">
                <slot name="input"></slot> 
            </VCol>
        </VRow>
    </VCol>
</template>
