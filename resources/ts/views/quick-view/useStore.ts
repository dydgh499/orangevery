import type { PayModule } from '@/views/types'
import { getUserLevel, user_info } from '@axios'
import { ref } from 'vue'

export const useQuickViewStore = defineStore('useQuickViewStore', () => {
    const payment_modules = ref(<PayModule[]>(
        getUserLevel() === 10 ? user_info.value.online_pays : []
    ))
    const getPayMenuIcon = (module_type: number) => {
        if(module_type === 1)
            return 'fluent-payment-32-regular'
        else if(module_type === 2)
            return 'fluent:payment-32-filled'
        else if(module_type === 3)
            return 'streamline:money-wallet-money-payment-finance-wallet'
        else
            return ''
    }

    const getPayMenuTitle = (payment_module: PayModule) => {
        let module_type = ''
        if(payment_module.module_type === 1)
            module_type = '수기'
        else if(payment_module.module_type === 2)
            module_type = '인증'
        else if(payment_module.module_type === 3)
            module_type = '간편'

        return module_type
    }

    const getPayLink = (pays: PayModule[]) => {
        const pay = (<any>({}))
        if(pays.length) {
            pay.title = '결제창'
            pay.icon = { icon: 'fluent-payment-32-regular' }
            pay.children = []
            for (let i = 0; i < pays.length; i++) {
                if(pays[i].pay_window_secure_level)
                    pay.children.push({
                        title: pays[i].note + ` (${getPayMenuTitle(pays[i])})`,
                        class: `payWindow()`,
                        params: pays[i],
                })
            }    
        }
        return pay
    }

    const getFirstModule = () => {
        const module = payment_modules.value.find(payment => payment.pay_window_secure_level > 0)
        if(module) {
            return {
                icon: getPayMenuIcon(module.module_type),
                title: getPayMenuTitle(module) + '결제',
                module: module
            }
        }
        else {
            return {
                icon: '',
                title: '',
                module: {},
            }
        }
    }
    
    const getPaymentMenu = computed(() => {
        if (getUserLevel() == 10)
            return getPayLink(payment_modules.value)
        else
            return {}
    })

    return {
        getFirstModule,
        getPaymentMenu,
        payment_modules,
    }
})
