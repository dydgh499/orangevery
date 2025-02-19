import { getUserLevel } from '@axios'
import corp from '@corp'

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

    if (getUserLevel() >= 35 || (corp.pv_options.auth.visibles.abnormal_trans_sales && getUserLevel() >= 13)) {
        transactions.children.push({
            title: '이상거래 관리',
            to: 'transactions-dangers',
        },
        {
            title: '결제실패 관리',
            to: 'transactions-fails',
        })        
        if (corp.pv_options.paid.use_realtime_deposit)
        {
            transactions.children.push({
                title: '즉시출금 관리',
                to: 'transactions-realtime-histories',
            })
        }
    }

    if(corp.pv_options.paid.use_shop && (getUserLevel() >= 35 || getUserLevel() === 10)) {
        transactions.children.push({
            title: '주문 관리',
            to: 'transactions-orders',
        })
    }
    if(getUserLevel() >= 35) {
        transactions.children.push({
            title: '결제 테스트',
            to: 'transactions-pay-test',
        })
    }
    return transactions
}

export const getTransactionMenu = () => {
    const menu = <any[]>[
        _getTransactionMenu(),
    ]
    return menu
}
