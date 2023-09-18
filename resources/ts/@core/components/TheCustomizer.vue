<script setup lang="tsx">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { RouteTransitions, Skins } from '@core/enums'
import { AppContentLayoutNav, ContentWidth, FooterType, NavbarType } from '@layouts/enums'
import { themeConfig } from '@themeConfig'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useTheme } from 'vuetify'

// import { useTheme } from 'vuetify'

const isNavDrawerOpen = ref(false)

const {
    theme,
    skin,
    appRouteTransition,
    navbarType,
    footerType,
    isVerticalNavCollapsed,
    isVerticalNavSemiDark,
    appContentWidth,
    appContentLayoutNav,
    isAppRtl,
    isNavbarBlurEnabled,
    isLessThanOverlayNavBreakpoint,
} = useThemeConfig()

const { width: windowWidth } = useWindowSize()

const headerValues = computed(() => {
    const entries = Object.entries(NavbarType)

    if (appContentLayoutNav.value === AppContentLayoutNav.Horizontal)
        return entries.filter(([_, val]) => val !== NavbarType.Hidden)

    return entries
})
</script>

<template>
    <template v-if="!isLessThanOverlayNavBreakpoint(windowWidth)">
        <VBtn icon size="small" class="app-customizer-toggler rounded-s-lg rounded-0" style="z-index: 1001;"
            @click="isNavDrawerOpen = true">
            <VIcon icon="tabler-settings" />
        </VBtn>

        <VNavigationDrawer v-model="isNavDrawerOpen" temporary location="end" width="400" :scrim="false"
            class="app-customizer">
            <!-- 👉 Header -->
            <div class="customizer-heading d-flex align-center justify-space-between">
                <div>
                    <h6 class="text-h6">
                        테마 수정
                    </h6>
                    <span class="text-body-1">제작 & 실시간 프리뷰</span>
                </div>
                <VBtn icon variant="text" color="secondary" size="x-small" @click="isNavDrawerOpen = false">
                    <VIcon icon="tabler-x" size="20" />
                </VBtn>
            </div>

            <VDivider />

            <PerfectScrollbar tag="ul" :options="{ wheelPropagation: false }">
                <!-- SECTION Theming -->
                <CustomizerSection title="테마" :divider="false">
                    <!-- 👉 Skin -->
                    <h6 class="text-base font-weight-regular">
                        배경
                    </h6>
                    <VRadioGroup v-model="skin" inline>
                        <VRadio v-for="[key, val] in Object.entries(Skins)" :key="key" :label="key" :value="val" />
                    </VRadioGroup>

                    <!-- 👉 Theme -->
                    <h6 class="mt-3 text-base font-weight-regular">
                        다크모드
                    </h6>
                    <div class="d-flex align-center">
                        <VLabel for="pricing-plan-toggle" class="me-3">
                            비활성화
                        </VLabel>
                        <VSwitch id="pricing-plan-toggle" v-model="theme" label="활성화" true-value="dark"
                            false-value="light" color="primary" style="margin-top: 1.2em;"/>
                    </div>
                </CustomizerSection>
                <!-- !SECTION -->

                <!-- SECTION LAYOUT -->
                <CustomizerSection title="레이아웃">
                    <!-- 👉 Content Width -->
                    <h6 class="text-base font-weight-regular">
                        콘텐츠 넓이
                    </h6>
                    <VRadioGroup v-model="appContentWidth" inline>
                        <VRadio v-for="[key, val] in Object.entries(ContentWidth)" :key="key" :label="key" :value="val" />
                    </VRadioGroup>
                    <!-- 👉 Navbar Type -->
                    <h6 class="mt-3 text-base font-weight-regular">
                        {{ appContentLayoutNav === AppContentLayoutNav.Vertical ? '네비' : '헤더' }} 타입
                    </h6>
                    <VRadioGroup v-model="navbarType" inline>
                        <VRadio v-for="[key, val] in headerValues" :key="key" :label="key" :value="val" />
                    </VRadioGroup>
                    <!-- 👉 Footer Type -->
                    <h6 class="mt-3 text-base font-weight-regular">
                        푸터 타입
                    </h6>
                    <VRadioGroup v-model="footerType" inline>
                        <VRadio v-for="[key, val] in Object.entries(FooterType)" :key="key" :label="key" :value="val" />
                    </VRadioGroup>
                    <!-- 👉 Navbar blur -->
                    <br>
                    <div class="d-flex align-center justify-space-between">
                        <VLabel for="customizer-navbar-blur" class="text-high-emphasis">
                            네비 불투명 적용
                        </VLabel>
                        <div>
                            <VSwitch id="customizer-navbar-blur" v-model="isNavbarBlurEnabled" class="ms-2"
                                color="primary" />
                        </div>
                    </div>
                </CustomizerSection>
                <!-- !SECTION -->

                <!-- SECTION Menu -->
                <CustomizerSection title="메뉴">
                    <!-- 👉 Collapsed Menu -->
                    <div v-if="appContentLayoutNav === AppContentLayoutNav.Vertical"
                        class="d-flex align-center justify-space-between">
                        <VLabel for="customizer-menu-collapsed" class="text-high-emphasis">
                            메뉴 축소
                        </VLabel>
                        <div>
                            <VSwitch id="customizer-menu-collapsed" v-model="isVerticalNavCollapsed" class="ms-2"
                                color="primary" />
                        </div>
                    </div>
                </CustomizerSection>
                <!-- !SECTION -->

                <!-- SECTION MISC -->
                <!-- 👉 Route Transition -->
                <!--
          <CustomizerSection title="MISC">
          <div class="mt-6">
            <VRow>
              <VCol
                cols="5"
                class="d-flex align-center"
              >
                <VLabel
                  for="route-transition"
                  class="text-high-emphasis"
                >
                  Router Transition
                </VLabel>
              </VCol>

              <VCol cols="7">
                <VSelect
                  id="route-transition"
                  v-model="appRouteTransition"
                  :items="Object.entries(RouteTransitions).map(([key, value]) => ({ key, value }))"
                  item-title="key"
                  item-value="value"
                  single-line
                />
              </VCol>
            </VRow>
          </div>
        </CustomizerSection>
        -->
                <!-- !SECTION -->
            </PerfectScrollbar>
        </VNavigationDrawer>
    </template>
</template>

<style lang="scss">
.app-customizer {
  .customizer-section {
    padding: 1.25rem;
  }

  .customizer-heading {
    padding-block: 0.875rem;
    padding-inline: 1.25rem;
  }

  .v-navigation-drawer__content {
    display: flex;
    flex-direction: column;
  }
}

.app-customizer-toggler {
  position: fixed !important;
  inset-block-start: 50%;
  inset-inline-end: 0;
  transform: translateY(-50%);

  &:active {
    transform: translateY(-50%) !important;
  }
}
</style>
