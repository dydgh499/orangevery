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

    const getPayMenuFormats = (pay: PayModule, type: string) => {
        const pays: any = []
        const params = getEncryptParams(pay)
        pays.push({
            title: '이동하기',
            href: '/pay/' + type + "?e=" + params,
        })
        pays.push({
            title: '링크복사',
            class: 'copy()',
            params: url.origin + '/pay/' + type + "?e=" + params,
        })
        if(corp.pv_options.paid.use_hand_pay_drct) {
            pays.push({
                title: '링크생성',
                class: 'direct()',
                params: url.origin + '/pay/' + type + "?e=" + params,
            })
        }
        if(corp.pv_options.paid.use_hand_pay_sms) {
            pays.push({
                title: 'SMS 결제 전송',
                class: 'sms()',
                params: url.origin + '/pay/' + type + "?e=" + params,
            })
        }
        if(type === 'hand' && corp.pv_options.paid.use_multiple_hand_pay) {
            pays.push({
                title: '다중결제',
                href: url.origin + '/pay/multiple-hand?e=' + params,
            })
        }
        return {
            title: pay.note,
            children: pays            
        };
    }

    const getPayLinkFormats = (pays: PayModule[], type: string) => {
        const children = []
        for (let i = 0; i < pays.length; i++) {
            if(pays[i].show_pay_view)
                children.push(getPayMenuFormats(pays[i], type))
        }
        return children
    };
    

    const getPaymentMenu = computed(() => {
        const payment_menus = []
        if (getUserLevel() == 10) {
            if (corp.pv_options.free.use_hand_pay) {
                let children = getPayLinkFormats(hands.value, 'hand')
                if(children.length > 0) {
                    payment_menus.push({
                        title: '수기결제',
                        icon: { icon: 'fluent-payment-32-regular' },
                        children: children,
                    });    
                }
            }
            if (corp.pv_options.free.use_auth_pay) {
                let children = getPayLinkFormats(auths.value, 'auth')
                if(children.length > 0) {
                    payment_menus.push({
                        title: '인증결제',
                        icon: { icon: 'fluent-payment-32-regular' },
                        children: children,
                    });    
                }
            }
            if (corp.pv_options.free.use_simple_pay) {
                let children = getPayLinkFormats(simples.value, 'simple')
                if(children.length > 0) {
                    payment_menus.push({
                        title: '간편결제',
                        icon: { icon: 'fluent-payment-32-regular' },
                        children: children,
                    });    
                }
            }
        }

        payment_menus.push({
            title: '매출 관리',
            icon: { icon: 'ic-outline-payments' },
            to: 'transactions',
        });

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
