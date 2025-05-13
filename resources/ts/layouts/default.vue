<script lang="ts" setup>
import AlertDialog from '@/layouts/dialogs/utils/AlertDialog.vue'
import LoadingDialog from '@/layouts/dialogs/utils/LoadingDialog.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'
import router from '@/router'
import { useSkins } from '@core/composable/useSkins'
import { useThemeConfig } from '@core/composable/useThemeConfig'
// @layouts plugin
import { AppContentLayoutNav } from '@layouts/enums'

const DefaultLayoutWithVerticalNav = defineAsyncComponent(() => import('./components/DefaultLayoutWithVerticalNav.vue'))

const { width: windowWidth } = useWindowSize()
const { appContentLayoutNav, switchToVerticalNavOnLtOverlayNavBreakpoint } = useThemeConfig()

// ℹ️ This will switch to vertical nav when define breakpoint is reached when in horizontal nav layout
// Remove below composable usage if you are not using horizontal nav layout in your app
switchToVerticalNavOnLtOverlayNavBreakpoint(windowWidth)

const { layoutAttrs, injectSkinClasses } = useSkins()

injectSkinClasses()

const { appRouteTransition } = useThemeConfig()
const customer_link = ref(router.currentRoute.value.path.includes('/pay/') || router.currentRoute.value.path.includes('/shop/'))
const alert = ref()
const snackbar = ref()
const loading = ref()

provide('alert', alert)
provide('snackbar', snackbar)
provide('loading', loading)


</script>

<template>
    <section>
        <DefaultLayoutWithVerticalNav
            v-if="appContentLayoutNav === AppContentLayoutNav.Vertical && customer_link === false" v-bind="layoutAttrs" />
        <div v-else-if="router.currentRoute.value.path.includes('/shop/')" class="d-flex justify-center align-center custom-warpper">
            <RouterView v-slot="{ Component }">
                <Transition :name="appRouteTransition" mode="out-in">
                    <Component :is="Component" />
                </Transition>
                <Snackbar ref="snackbar" />
                <AlertDialog ref="alert" />
                <LoadingDialog ref="loading" />
            </RouterView>
        </div>
        <div v-else class="d-flex justify-center align-center custom-warpper">
            <RouterView v-slot="{ Component }">
                <Transition :name="appRouteTransition" mode="out-in">
                    <Component :is="Component" />
                </Transition>
                <Snackbar ref="snackbar" />
                <AlertDialog ref="alert" />
                <LoadingDialog ref="loading" />
            </RouterView>
        </div>
    </section>
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";

.custom-warpper {
  flex-direction: column;
  block-size: 100%;
}

</style>
