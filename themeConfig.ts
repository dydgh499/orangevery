import { breakpointsVuetify } from '@vueuse/core'

import corp from '@corp'
import { VIcon } from 'vuetify/components'

// ❗ Logo SVG must be imported with ?raw suffix

import { defineThemeConfig } from '@core'
import { RouteTransitions, Skins } from '@core/enums'
import { AppContentLayoutNav, ContentWidth, FooterType, NavbarType } from '@layouts/enums'

export const { themeConfig, layoutConfig } = defineThemeConfig({
  app: {
    title: corp.name,
    logo: h('img', { src: corp.logo_img, style: 'color: rgb(var(--v-global-theme-primary)); width: 45px;' }),
    contentWidth: ContentWidth.중앙정렬,
    contentLayoutNav: AppContentLayoutNav.Vertical,
    overlayNavFromBreakpoint: breakpointsVuetify.md + 16, // 16 for scro
    enableI18n: false,
    theme: 'light',
    isRtl: false,
    skin: Skins.Default,
    routeTransition: RouteTransitions['Scroll Y'],
    iconRenderer: VIcon,
  },
  navbar: {
    type: NavbarType.고정,
    navbarBlur: true,
  },
  footer: { type: FooterType.고정 },
  verticalNav: {
    isVerticalNavCollapsed: false,
    defaultNavItemIconProps: { icon: 'tabler-circle', size: 10 },
    isVerticalNavSemiDark: false,
  },
  horizontalNav: {
    type: 'sticky',
    transition: 'slide-y-reverse-transition',
  },
  icons: {
    chevronDown: { icon: 'tabler-chevron-down' },
    chevronRight: { icon: 'tabler-chevron-right', size: 18 },
    close: { icon: 'tabler-x' },
    verticalNavPinned: { icon: 'tabler-chevron-right' },
    verticalNavUnPinned: { icon: 'tabler-chevron-left' },
    sectionTitlePlaceholder: { icon: 'tabler-separator' },
  },
})
