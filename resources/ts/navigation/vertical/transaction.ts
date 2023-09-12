import { user_info } from '@axios'
import corp from '@corp'

const getAbilitiesMenu = computed(() => {
    const payments = []
    const settles = []
    const risks = []

    if (corp.pv_options.free.use_hand_pay) {
        payments.push({
            title: '수기 결제 테스트',
            to: 'transactions-pay-test-hand',
        })
    }
    if (corp.pv_options.free.use_auth_pay) {
        payments.push({
            title: '인증 결제 테스트',
            to: 'transactions-pay-test-auth',
        })
    }
    if (corp.pv_options.free.use_simple_pay) {
        payments.push({
            title: '간편 결제 테스트',
            to: 'transactions-pay-test-simple',
        })
    }
    if (user_info.value.level > 10) {
        settles.push({
            title: '정산 관리',
            icon: { icon: 'tabler-calculator' },
            children: [
                {
                    title: '가맹점 정산 관리',
                    to: 'transactions-settle-merchandises',
                },
                {
                    title: '영업점 정산 관리',
                    to: 'transactions-settle-salesforces',
                },
            ]
        },
        {
            title: '정산 이력',
            icon: { icon: 'tabler:calendar-time' },
            children: [
                {
                    title: '가맹점 정산 이력',
                    to: 'transactions-settle-histories-merchandises',
                },
                {
                    title: '영업점 정산 이력',
                    to: 'transactions-settle-histories-salesforces',
                },
            ]
        })
    }
    if (user_info.value.level > 10) {
        risks.push({
            title: '이상거래 관리',
            icon: { icon: 'jam-triangle-danger' },
            to: 'transactions-dangers',
        },
        {
            title: '결제실패 관리',
            icon: { icon: 'carbon:ai-status-failed' },
            to: 'transactions-fails',
        })
    }
    if (corp.pv_options.paid.use_realtime_deposit)
    {
        risks.push({
            title: '실시간이체 이력관리',
            icon: { icon: 'tabler:history' },
            to: 'transactions-realtime-histories',
        })
    }
    return [
        { heading: 'Transaction' },
        {
            title: '결제',
            icon: { icon: 'fluent-payment-32-regular' },
            children: payments
        },
        {
            title: '매출 관리',
            icon: { icon: 'ic-outline-payments' },
            to: 'transactions',
        },
        ...settles,
        ...risks,
    ]
})

export default getAbilitiesMenu
