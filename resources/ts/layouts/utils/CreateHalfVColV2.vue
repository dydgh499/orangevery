<script setup lang="ts">
interface Props {
    mdl: number,
    mdr: number,
}
const props = defineProps<Props>()

</script>
<template>
    <VRow>
        <VCol md="6" cols=12 style="margin-top: auto; margin-bottom: auto;">
            <VRow style="align-items: center;">
                <VCol :md="props.mdl">
                    <slot name="l_name"></slot>
                </VCol>
                <VCol :md="props.mdr">
                    <slot name="l_input"></slot>
                </VCol>
            </VRow>
        </VCol>
        <VCol md="6" cols=12 style="margin-top: auto; margin-bottom: auto;">
            <VRow style="align-items: center;">
                <VCol :md="props.mdl">
                    <slot name="r_name"></slot>
                </VCol>
                <VCol :md="props.mdr">
                    <slot name="r_input"></slot>
                </VCol>
            </VRow>
        </VCol>
    </VRow>
</template>
