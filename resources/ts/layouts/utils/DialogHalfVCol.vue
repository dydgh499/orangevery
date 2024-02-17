<script setup lang="ts">
</script>
<template>
    <div class="d-flex content">
        <div style="width: 33%; align-items: center;">
            <slot name="name"></slot>
        </div>
        <div style="width: 66%; align-items: center;">
            <slot name="input"></slot>
        </div>
    </div>
</template>
<style scoped>
.content {
  padding-inline: 12px;
}

@media (max-width: 500px) {
  .content {
    padding-inline: 8px;
  }
}
</style>

