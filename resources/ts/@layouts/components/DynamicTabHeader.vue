<script lang="ts" setup>
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab';
import draggable, { DragEndEvent } from 'vuedraggable'; // DragEndEvent 타입 사용

const route = useRoute()
const store = useDynamicTabStore()
const tabsWrapper = ref<HTMLElement | null>(null);

const scrollTabs = (direction: 'left' | 'right') => {
    if (tabsWrapper.value) {
        const scrollAmount = 500
        tabsWrapper.value.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        })
    }
};

const scrollToActiveTab = async () => {
    await nextTick(); // DOM이 업데이트된 이후 실행
    const activeTab = tabsWrapper.value?.querySelector('.v-tab--selected');
    if (activeTab && tabsWrapper.value) {
        const wrapperLeft = tabsWrapper.value.scrollLeft;
        const wrapperWidth = tabsWrapper.value.clientWidth;
        const tabLeft = (activeTab as HTMLElement).offsetLeft;
        const tabWidth = (activeTab as HTMLElement).clientWidth;

        // 탭이 화면 바깥에 있는 경우 해당 탭으로 스크롤
        if (tabLeft < wrapperLeft || tabLeft + tabWidth > wrapperLeft + wrapperWidth) {
            tabsWrapper.value.scrollTo({
                left: tabLeft - wrapperWidth / 2 + tabWidth / 2,
                behavior: 'smooth'
            });
        }
    }
};

const onDragEnd = (event: DragEndEvent) => {
    const { oldIndex, newIndex } = event;
    if (oldIndex !== undefined && newIndex !== undefined) {
        const movedTab = store.tabs.splice(oldIndex, 1)[0]
        store.tabs.splice(newIndex, 0, movedTab)
    }
};

watchEffect(() => {
    store.tabs = store.add(route)
    store.tab = store.tabs.findIndex(obj => obj.path === route.fullPath)
    scrollToActiveTab()
})

</script>
<template>
    <VCard style=" padding: 8px 14px;margin: 24px 24px 0;" v-if="store.tabs.length">
        <div class="tabs-container" v-if="$vuetify.display.smAndDown === false">
            <VChip class="tab-count" color="primary">
                <b>{{ store.tabs.length }}</b>
            </VChip>
            <VBtn icon @click="scrollTabs('left')" class="scroll-btn" size="24" style="margin-left: 1em;">
                <VIcon icon="mdi-chevron-left" />
            </VBtn>
            <div class="tabs-wrapper" ref="tabsWrapper">
                <div
                    class="v-slide-group--is-overflowing v-tabs v-tabs--horizontal v-tabs--align-tabs-start v-tabs--density-comfortable">
                    <draggable v-model="store.tabs" item-key="path" class="drag-container" @end="onDragEnd">
                        <template #item="{ element, index }">
                            <div class="tab-close-container">
                                <VTab class="tab-close-title" @click="store.move(element.path)" :class="{
                                    'v-slide-group-item--active v-tab--selected': store.tab === index,
                                    'text-secondary': store.tab !== index
                                }">
                                    <span>{{ element.title }}</span>
                                </VTab>
                                <div class="tab-close-btn" v-if="store.tab !== index" @click="store.remove(index)">
                                    <VIcon :icon="`tabler-x`" size="small" />
                                </div>
                                <div class="tab-close-division" v-if="store.tabs.length - 1 !== index"></div>
                            </div>
                        </template>
                    </draggable>
                </div>
            </div>
            <VBtn icon @click="scrollTabs('right')" class="scroll-btn" size="24" style="margin-right: 1em;">
                <VIcon icon="mdi-chevron-right" />
            </VBtn>
            <div class="tab-close-btn all-close-btn text-error" v-if="store.tabs.length > 1" @click="store.allRemove()">
                <VIcon :icon="`tabler-x`" size="small" />
                <VTooltip activator="parent" location="top" transition="scale-transition">
                    <span>전체 탭 닫기</span>
                </VTooltip>
            </div>
        </div>
        <template v-else>
            <VTabs v-model="store.tab">
                <template v-for="(t, index) in store.tabs" :key="index">
                    <div class="tab-close-container">
                        <VTab class="tab-close-title" @click="store.move(t.path)">
                            <span>{{ t.title }}</span>
                        </VTab>
                        <div class="tab-close-btn" v-if="store.tab !== index" @click="store.remove(index)"
                            style="margin-top: 0.4em;">
                            <VIcon :icon="`tabler-x`" size="small" />
                        </div>
                        <div class="tab-close-division" v-if="store.tabs.length - 1 !== index"></div>
                    </div>
                </template>
            </VTabs>
            <VChip class="tab-count" color="primary" style=" position: absolute;z-index: 9999;padding: 0.6em !important;inset-block-start: 1em; inset-inline-start: 0.2em;">
                <b>{{ store.tabs.length }}</b>
            </VChip>
            <div class="tab-close-btn all-close-btn text-error" v-if="store.tabs.length > 1" @click="store.allRemove()" style="position: absolute;z-index: 9999;inset-block-start: 0.8em;inset-inline-end: 0.5em;">
                <VIcon :icon="`tabler-x`" size="small"/>
                <VTooltip activator="parent" location="top" transition="scale-transition">
                    <span>전체 탭 닫기</span>
                </VTooltip>
            </div>
        </template>
    </VCard>
</template>
<style lang="scss">
.tabs-container {
  display: flex;
  align-items: center;
  padding-block: 0;
  padding-inline: 1em;
}

.drag-container {
  display: flex;
  cursor: grab;
  gap: 10px;
  inline-size: 100%;
}

.tabs-wrapper {
  display: flex;
  overflow: hidden;
  flex: 1;
  margin-block: 0;
  margin-inline: 1em;
  scroll-behavior: smooth;
  white-space: nowrap;
}

.tab-close-container {
  display: inline-flex;
  block-size: 2em;
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
  transform: scale(1);

  /* 초기 상태의 크기 설정 */
  transition: 1s ease all;

  /* 기본 상태에서의 transition 설정 */

  &:hover {
    transform: scale(1.1) rotate(180deg);

    /* 회전과 크기 조정 함께 적용 */
    transform-origin: center;

    /* 회전 중심 설정 */
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

.scroll-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background-color: transparent !important;
  cursor: pointer !important;
}
</style>
