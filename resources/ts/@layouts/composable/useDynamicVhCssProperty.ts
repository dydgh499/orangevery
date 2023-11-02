
// Thanks: https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
import { useZoomProperty } from '@layouts/composable/useZoomProperty'

export const useDynamicVhCssProperty = () => {
    const vh = ref(0)
    const { zoom } = useZoomProperty()
    const updateVh = () => {
        const offset = window.innerHeight * ((100 - zoom.value) / 100)
        vh.value = (window.innerHeight + offset) * 0.01
        document.documentElement.style.setProperty('--vh', `${vh.value}px`)
    }

    watchEffect(() => {
        updateVh()
    })

    tryOnBeforeMount(() => {
        updateVh()
        useEventListener('resize', updateVh)
    })
}
