<script lang="ts" setup>
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab';

const route = useRoute()
const store = useDynamicTabStore()


watchEffect(() => {
    store.tab = store.tabs.findIndex(obj => obj.path === route.fullPath)
})
</script>
<template>
    <VCard style=" padding: 8px 14px;margin: 24px 24px 0;"
        v-if="store.tabs.length">
        <VTabs v-model="store.tab">
            <template v-for="(t, index) in store.tabs" :key="index">
                <div class="tab-close-container">
                    <VTab class="tab-close-title" @click="store.move(t.path)">
                        <span>{{ t.title }}</span>
                    </VTab>
                    <div class="tab-close-btn" v-if="store.tab !== index" @click="store.remove(index)">
                        <VIcon :icon="`tabler-x`" size="small"/>
                    </div>
                    <div class="tab-close-division" v-if="store.tabs.length -1 !== index"></div>
                </div>
            </template>
        </VTabs>
        <VChip class="tab-count" color="primary">
            <b>{{ store.tabs.length }}</b>
        </VChip>
    </VCard>
</template>
<style lang="scss">
.tab-close-container {
  display: inline-flex;
  padding-block: 0;
  padding-inline: 0.5em;
}

.tab-close-title {
  min-inline-size: 30px !important;
  padding-inline: 0.5em;
}

.tab-close-btn {
  display: inline-block;
  border-radius: 0.2rem;
  background-color: rgb(var(--v-theme-background)) !important;
  block-size: 1.6em;
  color: rgba(var(--v-theme-on-surface), var(--v-disabled-opacity)) !important;
  cursor: pointer;
  margin-block: auto;
  transform: scale(1); /* 초기 상태의 크기 설정 */
  transition: 1s ease all; /* 기본 상태에서의 transition 설정 */

  &:hover {
    transform: scale(1.1) rotate(180deg); /* 회전과 크기 조정 함께 적용 */
    transform-origin: center; /* 회전 중심 설정 */
  }
}

.tab-close-division {
  display: inline-block;
  background-color: rgba(var(--v-theme-on-surface), 0.15);
  block-size: 16px;
  inline-size: 2px;
  margin-block-end: auto;
  margin-block-start: auto;
  margin-inline-start: 1em;
}

.tab-count {
  position: absolute;
  z-index: 9999;
  inset-block-start: 2px;
  inset-inline-start: 2px;
}

</style>
