<script setup lang="ts">
interface Props {
    radio: number,
}
const props = defineProps<Props>()
const emits = defineEmits(['update:radio'])

</script>
<template>
    <VRadioGroup v-model="props.radio" inline>
        <VRadio :value="1" @click="emits('update:radio', 1)">
            <template #label>
                <span>
                    <slot name="true"></slot>
                </span>
            </template>
        </VRadio>

        <VRadio :value="0" @click="emits('update:radio', 0)">
            <template #label>
                <span>
                    <slot name="false"></slot>
                </span>
            </template>
        </VRadio>
    </VRadioGroup>
</template>
