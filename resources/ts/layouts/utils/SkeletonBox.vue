<template>
    <span :style="{ height, width: computedWidth, borderRadius }" class="skeleton-box"></span>
</template>
<script setup lang="ts">

const props = defineProps({
    maxWidth: {
        default: 100,
        type: Number
    },
    minWidth: {
        default: 80,
        type: Number
    },
    height: {
        default: '1em',
        type: String
    },
    width: {
        default: null,
        type: String
    },
    borderRadius: {
        default: null,
        type: String
    }
});

const computedWidth = computed(() => {
    return props.width || `${Math.floor((0.9 * (props.maxWidth - props.minWidth)) + props.minWidth)}%`;
});
</script>

<style lang="scss">
.skeleton-box {
  position: relative;
  display: inline-block;
  overflow: hidden;
  border: 1px rgb(var(--v-theme-grey-100)) solid;
  border-radius: 0.4em;
  background-color: rgb(var(--v-theme-grey-100));
  vertical-align: middle;

  &::after {
    position: absolute;
    animation: shimmer 1.5s infinite; /* 빠르게 반복되는 애니메이션 */
    background-image:
      linear-gradient(
        90deg,
        rgba(var(--v-theme-background), 0) 0%,
        rgba(var(--v-theme-background), 0.3) 25%,
        rgba(var(--v-theme-background), 0.6) 50%,
        rgba(var(--v-theme-background), 0.3) 75%,
        rgba(var(--v-theme-background), 0) 100%
      );
    content: "";
    inset: 0;
    transform: translateX(-100%);
  }

  @keyframes shimmer {
    100% {
      transform: translateX(100%);
    }
  }
}
</style>
