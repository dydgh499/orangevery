import { getUserLevel } from '@axios'
import corp from '@corp'


const getPaymentTestTap = () => {
    let payment = []
    if(getUserLevel() >= 35) {
        payment.push({
            title: '결제 테스트',
            to: 'transactions-pay-test',
            query: {}
        })
    }
    return payment
}

const getRiskTap = () => {
    const risks = []
    if (getUserLevel() >= 35 || corp.pv_options.auth.visibles.abnormal_trans_sales) {
        risks.push({
            title: '이상거래 관리',
            to: 'transactions-dangers',
        },
        {
            title: '결제실패 관리',
            to: 'transactions-fails',
        })        
        if (corp.pv_options.paid.use_realtime_deposit)
        {
            risks.push({
                title: '즉시출금 관리',
                to: 'transactions-realtime-histories',
            })
        }
    }
    return risks
}

const getTransactionTap = () => {
    let transactions = []
    transactions.push({
        title: '매출 관리',
        icon: { icon: 'ic-outline-payments' },
        children: [
            {
                title: '상세 조회',
                to: 'transactions',
            },
        ]
    })
    if(getUserLevel() > 10) {
        transactions[0].children.push({
            title: '통계 조회',
            to: 'transactions-summary',
        })
    }
    transactions[0].children.push(...getRiskTap())
    transactions[0].children.push(...getPaymentTestTap())
    return transactions
}

const getSettleManagement = () => {
    const settles = []
    let settle_childs = []
    if (getUserLevel() > 10) {
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
        
        if(getUserLevel() >= 35) {
            if(corp.pv_options.paid.use_collect_withdraw) {
                settle_childs.push({
                    title: '모아서 출금 관리',
                    to: 'transactions-settle-collect-withdraws',
                })
            }
            settle_childs.push({
                title: '취소 수기 입금',
                to: 'transactions-settle-cancel-deposits',
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
    if (getUserLevel() > 10) {

        settle_history_childs.push({
            title: '가맹점 정산 이력',
            to: 'transactions-settle-histories-merchandises',
        })
        settle_history_childs.push({
            title: '영업점 정산 이력',
            to: 'transactions-settle-histories-salesforces',
        })
        if (getUserLevel() >= 35 && corp.use_different_settlement) {
            settle_history_childs.push({
                title: '차액 정산 이력',
                to: 'transactions-settle-histories-difference',
            })
        }
        if(getUserLevel() >= 35 && corp.pv_options.paid.use_collect_withdraw) {
            settle_history_childs.push({
                title: '모아서 출금 이력',
                to: 'transactions-settle-histories-collect-withdraws',
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

const getAbilitiesMenu = computed(() => {
    const transactions = getTransactionTap()
    const settles = getSettleManagement()
    const settle_histories = getSettleHistoryTap()

    return [
        { heading: 'Transaction' },
        ...transactions,
        ...settles,
        ...settle_histories,
    ]
})

export default getAbilitiesMenu
