<script setup lang="ts">
interface Props {
    radio: boolean,
}
const props = defineProps<Props>()
const radioValue = ref(props.radio);

const emits = defineEmits(['update:radio']);
watch(radioValue, (newValue) => {
    if (newValue !== props.radio) {
        emits('update:radio', newValue);
    }
});
</script>
<template>
    <VRadioGroup v-model="radioValue" inline>
        <VRadio :value="true">
            <template #label>
                <span>
                    <slot name="true"></slot>
                </span>
            </template>
        </VRadio>

        <VRadio :value="false">
            <template #label>
                <span>
                    <slot name="false"></slot>
                </span>
            </template>
        </VRadio>
    </VRadioGroup>
</template>
