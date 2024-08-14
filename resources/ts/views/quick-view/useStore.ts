import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'
import * as CryptoJS from 'crypto-js'
import { filter } from 'lodash'
import { ref } from 'vue'

const payment_modules = getUserLevel() == 10 ? await getAllPayModules() : []
export const useQuickViewStore = defineStore('useQuickViewStore', () => {
    const hands = ref(<PayModule[]>(filter(payment_modules, { module_type: 1 })))
    const auths = ref(<PayModule[]>(filter(payment_modules, { module_type: 2 })))
    const simples = ref(<PayModule[]>(filter(payment_modules, { module_type: 3 })))
    const url = new URL(window.location.href)

    const getEncryptParams = (pay: PayModule) => {
        return encodeURIComponent(
            CryptoJS.AES.encrypt(
                JSON.stringify(
                    {
                        m: pay.mcht_id,
                        p: pay.id,
                        o: Boolean(pay.is_old_auth),
                        i: pay.installment,
                        t: Date.now() % 10000,
                        g: pay.pg_id,
                    }
                )
                , '^^_masking_^^').toString())
    }

    const getPayMenuFormats = (pay: PayModule, type: string, icon: string) => {
        const pays: any = []
        pays.push({
            title: '결제창 생성',
            class: 'direct()',
            params: pay,
        })
        pays.push({
            title: '결제창 조회',
            class: `select()`,
            params: pay,
        })
        return {
            title: pay.note,
            icon: { icon: icon },
            children: pays            
        };
    }

    const getPayLinkFormats = (pays: PayModule[], type: string, icon: string) => {
        const children = []
        for (let i = 0; i < pays.length; i++) {
            if(pays[i].show_pay_view)
                children.push(getPayMenuFormats(pays[i], type, icon))
        }
        return children
    };
    

    const getPaymentMenu = computed(() => {
        const payment_menus = []
        if (getUserLevel() == 10) {
            if (corp.pv_options.free.use_hand_pay)
                payment_menus.push(...getPayLinkFormats(hands.value, 'hand', 'fluent-payment-32-regular'))
            if (corp.pv_options.free.use_auth_pay) 
                payment_menus.push(...getPayLinkFormats(auths.value, 'auth', 'fluent:payment-32-filled'))    
            if (corp.pv_options.free.use_simple_pay) 
                payment_menus.push(...getPayLinkFormats(simples.value, 'simple', 'streamline:money-wallet-money-payment-finance-wallet'))
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
        getEncryptParams,
        getPayLinkFormats,
        getPaymentMenu,
        hands,
        auths,
        simples,
        payment_modules,
    }
})
