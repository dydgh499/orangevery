<script setup lang="ts">
import type { Anchor } from 'vuetify/lib/components'

interface Props {
    text: string,
    content: string,
    location: Anchor,
}
const props = defineProps<Props>()
</script>
<template>
    <div class="d-inline-flex align-center gap-2 justify-content-evenly">
        <span>
            {{ props.text }}
        </span>
        <VTooltip open-on-click :open-on-hover="false" :location="props.location" transition="scale-transition">
            <template #activator="{ props }">
                <VIcon v-bind="props" size="20" icon="ic:outline-help" color="primary" style="margin-bottom: 0.2em;" />
            </template>
            <span>
                {{ props.content }}
            </span>
        </VTooltip>
    </div>
</template>
