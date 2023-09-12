<script lang="ts" setup>
import type { Component } from 'vue'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VNodeRenderer } from './VNodeRenderer'
import { injectionKeyIsVerticalNavHovered, useLayouts } from '@layouts'
import { VerticalNavGroup, VerticalNavLink, VerticalNavSectionTitle } from '@layouts/components'
import { config } from '@layouts/config'
import type { NavGroup, NavLink, NavSectionTitle, VerticalNavItems } from '@layouts/types'

interface Props {
  tag?: string | Component
  navItems: VerticalNavItems
  isOverlayNavActive: boolean
  toggleIsOverlayNavActive: (value: boolean) => void
}

const props = withDefaults(defineProps<Props>(), {
  tag: 'aside',
})

const refNav = ref()

const { width: windowWidth } = useWindowSize()

const isHovered = useElementHover(refNav)

provide(injectionKeyIsVerticalNavHovered, isHovered)

const { isVerticalNavCollapsed: isCollapsed, isLessThanOverlayNavBreakpoint, isVerticalNavMini, isAppRtl } = useLayouts()

const hideTitleAndIcon = isVerticalNavMini(windowWidth, isHovered)

const resolveNavItemComponent = (item: NavLink | NavSectionTitle | NavGroup) => {
  if ('heading' in item)
    return VerticalNavSectionTitle
  if ('children' in item)
    return VerticalNavGroup

  return VerticalNavLink
}
/*
  ℹ️ Close overlay side when route is changed
  Close overlay vertical nav when link is clicked
*/
const route = useRoute()

watch(() => route.name, () => {
  props.toggleIsOverlayNavActive(false)
})

const isVerticalNavScrolled = ref(false)
const updateIsVerticalNavScrolled = (val: boolean) => isVerticalNavScrolled.value = val

const handleNavScroll = (evt: Event) => {
  isVerticalNavScrolled.value = (evt.target as HTMLElement).scrollTop > 0
}

</script>

<template>
  <Component
    :is="props.tag"
    ref="refNav"
    style="border-right: 1px dashed rgba(145, 158, 171, 20%);"
    class="layout-vertical-nav"
    :class="[
      {
        'overlay-nav': isLessThanOverlayNavBreakpoint(windowWidth),
        'hovered': isHovered,
        'visible': isOverlayNavActive,
        'scrolled': isVerticalNavScrolled,
      },
    ]"
  >
    <!-- 👉 Header -->
    <div class="nav-header" style="width: 100%;">
      <slot name="nav-header">
        <RouterLink
          to="/"
          class="app-logo d-flex align-center gap-x-3 app-title-wrapper"
          style="width: 15.5em; margin-left: 0.5em;"
        >
            <VNodeRenderer :nodes="config.app.logo" style="width: 45px;"/>

          <Transition name="vertical-nav-app-title">
            <h1
              v-show="!hideTitleAndIcon"
              class="app-title font-weight-bold leading-normal text-xl"
            >
              {{ config.app.title }}
            </h1>
          </Transition>
        </RouterLink>
        <!-- 👉 Vertical nav actions -->
        <!-- Show toggle collapsible in >md and close button in <md -->
        <template v-if="!isLessThanOverlayNavBreakpoint(windowWidth)">
            <!-- not visiable -->
          <Component
            :is="config.app.iconRenderer || 'div'"
            v-show="isCollapsed && !hideTitleAndIcon"
            v-bind="config.icons.verticalNavUnPinned"
            @click="isCollapsed = !isCollapsed"
            class="header-arrow active"
          />
            <!-- visiable -->
            <Component
            :is="config.app.iconRenderer || 'div'"
            v-show="!isCollapsed && !hideTitleAndIcon"
            v-bind="config.icons.verticalNavPinned"
            @click="isCollapsed = !isCollapsed"
            class="header-arrow active"
          />
        </template>
        <template v-else>
          <Component
            :is="config.app.iconRenderer || 'div'"
            class="header-action"
            v-bind="config.icons.verticalNavUnPinned"
            @click="toggleIsOverlayNavActive(false)"
          />
        </template>
      </slot>
    </div>
    <slot name="before-nav-items">
      <div class="vertical-nav-items-shadow" />
    </slot>
    <slot
      name="nav-items"
      :update-is-vertical-nav-scrolled="updateIsVerticalNavScrolled"
    >
      <PerfectScrollbar
        :key="isAppRtl"
        tag="ul"
        class="nav-items"
        :options="{ wheelPropagation: false }"
        @ps-scroll-y="handleNavScroll"
      >
        <Component
          :is="resolveNavItemComponent(item)"
          v-for="(item, index) in navItems"
          :key="index"
          :item="item"
        />
      </PerfectScrollbar>
    </slot>
  </Component>
</template>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/mixins";

// 👉 Vertical Nav
.layout-vertical-nav {
  position: fixed;
  z-index: variables.$layout-vertical-nav-z-index;
  display: flex;
  flex-direction: column;
  block-size: 100%;
  inline-size: variables.$layout-vertical-nav-width;
  inset-block-start: 0;
  inset-inline-start: 0;
  transition: transform 0.25s ease-in-out, inline-size 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  will-change: transform, inline-size;

  .nav-header {
    display: flex;
    align-items: center;

    .header-action {
      cursor: pointer;
    }
  }

  .app-title-wrapper {
    margin-inline-end: auto;
  }

  .nav-items {
    block-size: 100%;

    // ℹ️ We no loner needs this overflow styles as perfect scrollbar applies it
    // overflow-x: hidden;

    // // ℹ️ We used `overflow-y` instead of `overflow` to mitigate overflow x. Revert back if any issue found.
    // overflow-y: auto;
  }

  .nav-item-title {
    overflow: hidden;
    margin-inline-end: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  // 👉 Collapsed
  .layout-vertical-nav-collapsed & {
    &:not(.hovered) {
      inline-size: variables.$layout-vertical-nav-collapsed-width;
    }

    &.hovered {
      .no-active {
        inset-inline-start: 11.3em;
      }
    }
  }

  // 👉 Overlay nav
  &.overlay-nav {
    &:not(.visible) {
      transform: translateX(-#{variables.$layout-vertical-nav-width});

      @include mixins.rtl {
        transform: translateX(variables.$layout-vertical-nav-width);
      }
    }
  }
}

.header-arrow {
  position: absolute !important;
  border: 1px dashed rgba(145, 158, 171, 20%);
  border-radius: 50%;
  background-color: rgb(var(--v-theme-surface));
  block-size: 1em;
  font-size: 22px;
  inline-size: 1em;
}

.active {
  inset-inline-start: 10.3em;
}
</style>
