import { getUserLevel } from '@axios'
import corp from '@corp'

const getPaymentTestTap = () => {
    let payment = []
    if(corp.pv_options.paid.use_shop && (getUserLevel() >= 35 || getUserLevel() === 10)) {
        payment.push({
            title: '주문 이력',
            to: 'transactions-orders',
        })
    }
    if(getUserLevel() >= 35) {
        payment.push({
            title: '결제 테스트',
            to: 'transactions-pay-test',
        })
    }
    return payment
}

const getRiskTap = () => {
    const risks = []
    risks.push({
        title: '이상거래 이력',
        to: 'transactions-dangers',
    },
    {
        title: '결제실패 이력',
        to: 'transactions-fails',
    })
    return risks
}

const _getTransactionMenu = () => {
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

    transactions.children.push(...getRiskTap())
    transactions.children.push(...getPaymentTestTap())
    return transactions
}

export const getTransactionMenu = () => {
    const menu = <any[]>[
        { heading: 'Transaction' },
        _getTransactionMenu(),
    ]
    return menu
}
