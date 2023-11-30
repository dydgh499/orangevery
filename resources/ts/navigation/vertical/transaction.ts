import { user_info } from '@axios'
import corp from '@corp'


const getPaymentTap = () => {
    let payment = []
    if(user_info.value.level >= 35) {
        payment.push({
            title: '결제',
            icon: { icon: 'fluent-payment-32-regular' },
            children: <any>([])
        })
        if (corp.pv_options.free.use_hand_pay) {
            payment[0].children.push({
                title: '수기 결제 테스트',
                to: 'transactions-pay-test-hand',
            })
        }
        if (corp.pv_options.free.use_auth_pay) {
            payment[0].children.push({
                title: '인증 결제 테스트',
                to: 'transactions-pay-test-auth',
            })
        }
        if (corp.pv_options.free.use_simple_pay) {
            payment[0].children.push({
                title: '간편 결제 테스트',
                to: 'transactions-pay-test-simple',
            })
        }    
    }
    return payment
}

const getSettleManagement = () => {
    const settles = []
    let settle_childs = []
    if (user_info.value.level > 10) {
        settle_childs = [
            {
                title: '가맹점 정산 관리',
                to: 'transactions-settle-merchandises',
            },
            {
                title: '영업점 정산 관리',
                to: 'transactions-settle-salesforces',
            },
        ]
        if (corp.pv_options.paid.use_cancel_deposit) {
            settle_childs.push({
                title: '취소 수기 입금',
                to: 'transactions-settle-cancel-deposits',
            })
        }
        if(corp.pv_options.paid.use_collect_withdraw) {
            settle_childs.push({
                title: '가맹점 직접출금',
                to: 'transactions-settle-merchandises-self-settle'
            })
        }
        
        settles.push({
            title: '정산 관리',
            icon: { icon: 'tabler-calculator' },
            children: settle_childs,
        })
    }
    return settles
}

const getSettleHistoryTap = () => {
    const settle_histories = []
    const settle_history_childs = []
    if (user_info.value.level > 10) {

        settle_history_childs.push({
            title: '가맹점 정산 이력',
            to: 'transactions-settle-histories-merchandises',
        })
        settle_history_childs.push({
            title: '영업점 정산 이력',
            to: 'transactions-settle-histories-salesforces',
        })
        if (user_info.value.level >= 35 && corp.use_different_settlement) {
            settle_history_childs.push({
                title: '차액 정산 이력',
                to: 'transactions-settle-histories-difference',
            })
        }
        settle_histories.push({
            title: '정산 이력',
            icon: { icon: 'tabler:calendar-time' },
            children: settle_history_childs
        })
    }
    return settle_histories
}

const getRiskTap = () => {
    const risks = []
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
            title: '실시간이체 관리',
            icon: { icon: 'tabler:history' },
            to: 'transactions-realtime-histories',
        })
    }
    return risks
}

const getAbilitiesMenu = computed(() => {
    const payment = getPaymentTap()
    const settles = getSettleManagement()
    const settle_histories = getSettleHistoryTap()
    const risks = getRiskTap()

    return [
        { heading: 'Transaction' },
        ...payment,
        {
            title: '매출 관리',
            icon: { icon: 'ic-outline-payments' },
            to: 'transactions',
        },
        ...settles,
        ...settle_histories,
        ...risks,
    ]
})

export default getAbilitiesMenu
