import type { PayModule } from '@/views/types'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'
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

    const getPayMenuFormats = (pay: PayModule, icon: string) => {
        const pays: any = []
        pays.push({
            title: '결제창 생성',
            class: 'direct()',
            params: pay,
        })
        pays.push({
            title: '결제창 정보',
            class: `select()`,
            params: pay,
        })
        return {
            title: pay.note,
            icon: { icon: icon },
            children: pays
        };
    }

    const getPayLinkFormats = (pays: PayModule[], icon: string) => {
        const children = []
        for (let i = 0; i < pays.length; i++) {
            if(pays[i].pay_window_secure_level)
                children.push(getPayMenuFormats(pays[i], icon))
        }
        return children
    };
    

    const getPaymentMenu = computed(() => {
        const payment_menus = []
        const moduleTypeFilter = (module_type: number) => {
            return payment_modules.value.filter(obj => obj.module_type === module_type)            
        }

        if (getUserLevel() == 10) {
            payment_menus.push(...getPayLinkFormats(moduleTypeFilter(1), getPayMenuIcon(1)))
            payment_menus.push(...getPayLinkFormats(moduleTypeFilter(2), getPayMenuIcon(2)))
            payment_menus.push(...getPayLinkFormats(moduleTypeFilter(3), getPayMenuIcon(3)))
        }

        const transactions = {
            title: '매출 관리',
            icon: { icon: 'ic-outline-payments' },
            children: [
                {
                    title: '상세 조회',
                    to: 'transactions',
                },
            ]
        }
        if(getUserLevel() > 10) {
            transactions.children.push({
                title: '통계 조회',
                to: 'transactions-summary',
            })
        }
        payment_menus.push(transactions)    

        if(getUserLevel() == 10 && corp.pv_options.paid.use_collect_withdraw) {
            payment_menus.push({
                title: '정산 관리',
                icon: { icon: 'tabler-calculator' },
                to: 'transactions-settle-merchandises',
            },
            {
                title: '정산 이력',
                icon: { icon: 'tabler:calendar-time' },
                to: 'transactions-settle-histories-merchandises',
            })
        }
        return payment_menus
    })

    return {
        getPayMenuIcon,
        getPayLinkFormats,
        getPaymentMenu,
        payment_modules,
    }
})
