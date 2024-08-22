import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'
import { filter } from 'lodash'
import { ref } from 'vue'

const payment_modules = getUserLevel() == 10 ? await getAllPayModules() : []
export const useQuickViewStore = defineStore('useQuickViewStore', () => {
    const hands = ref(<PayModule[]>(filter(payment_modules, { module_type: 1 })))
    const auths = ref(<PayModule[]>(filter(payment_modules, { module_type: 2 })))
    const simples = ref(<PayModule[]>(filter(payment_modules, { module_type: 3 })))

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
        if (getUserLevel() == 10) {
            if (corp.pv_options.free.use_hand_pay)
                payment_menus.push(...getPayLinkFormats(hands.value, getPayMenuIcon(1)))
            if (corp.pv_options.free.use_auth_pay) 
                payment_menus.push(...getPayLinkFormats(auths.value, getPayMenuIcon(2)))    
            if (corp.pv_options.free.use_simple_pay) 
                payment_menus.push(...getPayLinkFormats(simples.value, getPayMenuIcon(3)))
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
        hands,
        auths,
        simples,
        payment_modules,
    }
})
