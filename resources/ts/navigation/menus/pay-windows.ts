import { getUserLevel } from "@/plugins/axios"
import { useQuickViewStore } from "@/views/quick-view/useStore"

export const getPayWindowMenu = () => {
    const menu = <any[]>[]
    if(getUserLevel() === 10) {
        const { getPaymentMenu } = useQuickViewStore()
        if(getPaymentMenu?.children && getPaymentMenu?.children?.length > 0) {
            menu.push({heading: 'Payment Window'})
            menu.push(getPaymentMenu)
        }
    }
    return menu
}
