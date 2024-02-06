<script lang="ts" setup>
import { useLayouts } from '@layouts'
import { config } from '@layouts/config'
import { can } from '@layouts/plugins/casl'
import type { NavLink } from '@layouts/types'
import { getComputedNavLinkToProp, isNavLinkActive } from '@layouts/utils'

defineProps<{
  item: NavLink
}>()

const snackbar  = <any>(inject('snackbar'))
const payLink  = <any>(inject('payLink'))


const { width: windowWidth } = useWindowSize()
const { isVerticalNavMini, dynamicI18nProps } = useLayouts()
const hideTitleAndBadge = isVerticalNavMini(windowWidth)

const copy = (text: string) => {
    try {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        snackbar.value.show('복사에 성공하였습니다.', 'success')
    } catch (err) {
        snackbar.value.show('복사에 실패하였습니다.', 'error')
    }
}

const direct = (url: string) => {
    payLink.value.show('PAY-LINK', url)
}

const sms = (url: string) => {
    payLink.value.show('SMS-SEND', url)    
}
const log = () => {
    window.open('http://log.payvery.kr:3000/')
}
const TapFunctionNavi = (item: NavLink) => {
    if(item.class == 'copy()')
        copy(item.params ?? '')
    else if(item.class == 'direct()')
        direct(item.params ?? '')
    else if(item.class == 'sms()')
        sms(item.params ?? '')
    else if(item.class == 'log()')
        log()
}
</script>

<template>
  <li
    v-if="can(item.action, item.subject)"
    class="nav-link"
    :class="{ disabled: item.disable }"    
    @click="TapFunctionNavi(item)"
  >
    <Component
      :is="item.to ? 'RouterLink' : 'a'"
      v-bind="getComputedNavLinkToProp(item)"
      :class="{ 'router-link-active router-link-exact-active': isNavLinkActive(item, $router) }"
    >
      <Component
        :is="config.app.iconRenderer || 'div'"
        v-bind="item.icon || config.verticalNav.defaultNavItemIconProps"
        class="nav-item-icon"
      />
      <TransitionGroup name="transition-slide-x">
        <!-- 👉 Title -->
        <Component
          :is="config.app.enableI18n ? 'i18n-t' : 'span'"
          v-show="!hideTitleAndBadge"
          key="title"
          class="nav-item-title"
          v-bind="dynamicI18nProps(item.title, 'span')"
        >
          {{ item.title }}
        </Component>

        <!-- 👉 Badge -->
        <Component
          :is="config.app.enableI18n ? 'i18n-t' : 'span'"
          v-if="item.badgeContent"
          v-show="!hideTitleAndBadge"
          key="badge"
          class="nav-item-badge"
          :class="item.badgeClass"
          v-bind="dynamicI18nProps(item.badgeContent, 'span')"
        >
          {{ item.badgeContent }}
        </Component>
      </TransitionGroup>
    </Component>
  </li>
</template>

<style lang="scss">
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
  }
}
</style>
