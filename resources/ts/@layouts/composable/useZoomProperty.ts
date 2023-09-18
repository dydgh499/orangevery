import { themeConfig } from '@themeConfig'

export const useZoomProperty = () => {
    const snackbar = <any>(inject('snackbar'))
    const zoom = ref(<number>(Number(localStorage.getItem(`${themeConfig.app.title}-zoom`) ?? 90)))
    
    const setZoom = () => {
        //document.documentElement.style.setProperty('font-size', `${zoom.value/100}em`)
        document.documentElement.style.setProperty('zoom', `${zoom.value}%`)
        localStorage.setItem(`${themeConfig.app.title}-zoom`, zoom.value.toString())
    }
    const zoomIn = () => {
        zoom.value += 10
        if (zoom.value > 120) {
            snackbar.value.show('더이상 확대할 수 없습니다', 'warning')
            zoom.value = 120
        }
    }

    const zoomOut = () => {
        zoom.value -= 10
        if (zoom.value < 90) {
            snackbar.value.show('더이상 축소할 수 없습니다', 'warning')
            zoom.value = 90
        }
    }
    watchEffect(() => {
        setZoom()
    })

    return {
        zoom,
        zoomIn,
        zoomOut
    }
}
