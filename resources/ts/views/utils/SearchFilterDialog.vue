<script setup lang="ts">
import { Filter } from '@/views/types';
interface Props {
    headers  : Filter[],
}
const props = defineProps<Props>()
const visible = ref(false)
const show = () => {
    visible.value = true;
}
defineExpose({
    show
});
</script>
<template>
    <VDialog
        v-model="visible"
        max-width="600"
    >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="검색 필터">
        <VCardText>
            <VRow>
                <VCol>

                    <VCheckbox
                        v-for="header in props.headers"
                        :key="Object.keys(header)[0]"
                        v-model="header.hidden"
                        :label="header.ko"
                        true-icon="tabler-circle-x"
                        false-icon="tabler-eye-check"
                        color="primary"
                    />
                </VCol>
            </VRow>
        </VCardText>
        </VCard>
    </VDialog>
</template>
