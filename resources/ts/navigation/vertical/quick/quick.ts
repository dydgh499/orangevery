import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'
import * as CryptoJS from 'crypto-js'
import { filter, map } from 'lodash'

const hands = ref(<PayModule[]>([]))
const auths = ref(<PayModule[]>([]))
const simples = ref(<PayModule[]>([]))
const pay_modules = reactive<PayModule[]>([])
const url = new URL(window.location.href)
if (getUserLevel() == 10) {
    Object.assign(pay_modules, await getAllPayModules())
}

const getPayLinkFormats = (pays: PayModule[], type: string) => {
    return map(pays, (pay) => {
        const pays: any = []
        const params = encodeURIComponent(
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
        return {
            title: pay.note,
            children: pays            
        };
    });
};

watchEffect(() => {
    hands.value = filter(pay_modules, { module_type: 1 });
    auths.value = filter(pay_modules, { module_type: 2 });
    simples.value = filter(pay_modules, { module_type: 3 });
});
const getAbilitiesMenu = computed(() => {
    const payments: any = []
    if (getUserLevel() == 10) {
        if (corp.pv_options.free.use_hand_pay) {
            payments.push({
                title: '수기결제',
                icon: { icon: 'fluent-payment-32-regular' },
                children: getPayLinkFormats(hands.value, 'hand'),
            });
        }
        if (corp.pv_options.free.use_auth_pay) {
            payments.push({
                title: '인증결제',
                icon: { icon: 'fluent-payment-32-regular' },
                children: getPayLinkFormats(auths.value, 'auth'),
            });
        }
        if (corp.pv_options.free.use_simple_pay) {
            payments.push({
                title: '간편결제',
                icon: { icon: 'fluent-payment-32-regular' },
                children: getPayLinkFormats(simples.value, 'simple'),
            });
        }
    }
    payments.push({
        title: '매출 관리',
        icon: { icon: 'ic-outline-payments' },
        to: 'transactions',
    });
    
    if(getUserLevel() == 10 && corp.pv_options.paid.use_collect_withdraw) {
        payments.push({
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
    return [
        { heading: '' },
        {
            title: '홈',
            icon: { icon: 'tabler-smart-home' },
            to: 'quick-view',
        },
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [
                { title: '가맹점 목록', to: 'merchandises' },
                { title: '장비 관리', to: 'merchandises-terminals' },
                //{ title: '결제모듈 관리', to: 'merchandises-pay-modules' },
            ]
        },
        { heading: 'Transaction' },
        ...payments,

        { heading: 'Service' },
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ]
})
export default getAbilitiesMenu

