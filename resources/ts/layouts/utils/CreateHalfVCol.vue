<script setup lang="ts">
interface Props {
    mdl: number,
    mdr: number,
}
const props = defineProps<Props>()

</script>
<template>
    <VCol cols="12">
        <VRow no-gutters style="align-items: center;">
            <VCol cols="12" :md="props.mdl">
                <label>
                    <slot name="name"></slot> 
                </label>
            </VCol>
            <VCol cols="12" :md="props.mdr">
                <slot name="input"></slot> 
            </VCol>
        </VRow>
    </VCol>
</template>
