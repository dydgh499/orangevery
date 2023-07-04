<script setup lang="ts">
interface Props {
    padding: boolean,
}
const props = defineProps<Props>()

</script>
<template>
    <VCol cols="12" style="padding: 0;">
        <VCol cols="12" style="max-width: 800px; margin-right: auto; margin-left: auto;">
            <VCard rounded :style="props.padding ? 'padding: 1em;' : ''">
                <slot name="content">
                </slot>
            </VCard>
        </VCol>
    </VCol>
</template>
