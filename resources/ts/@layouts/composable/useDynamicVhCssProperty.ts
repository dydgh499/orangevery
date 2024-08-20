
// Thanks: https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
import { themeConfig } from '@themeConfig'
export const useDynamicVhCssProperty = () => {
    const vh = ref(0)
    const updateVh = () => {
        const zoom = Number(localStorage.getItem(`${themeConfig.app.title}-zoom`) ?? 90)
        const offset = window.innerHeight * ((100 - zoom) / 100)
        vh.value = (window.innerHeight + offset) * 0.01
    }

    watchEffect(() => {
        updateVh()
    })

    tryOnBeforeMount(() => {
        updateVh()
        useEventListener('resize', updateVh)
    })
}
