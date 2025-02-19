import { getUserLevel } from '@axios'
import corp from '@corp'

const _getSettlementMenu = () => {
    const settlement = <any>{
        title: '정산 관리',
        icon: { icon: 'tabler:calendar-time' },
        children: [],
    }
    
    if(getUserLevel() >= 13) {
        settlement.children.push(
            {
                title: '가맹점 정산 관리',
                to: 'transactions-settle-merchandises',
            },
            {
                title: '영업점 정산 관리',
                to: 'transactions-settle-salesforces',
            },
        )
    }

    if(getUserLevel() >= 35) {
        if(corp.pv_options.paid.use_collect_withdraw) {
            settlement.children.push({
                title: '모아서 출금 관리',
                to: 'transactions-settle-collect-withdraws',
            })
        }
    }

    if(getUserLevel() >= 10 && getUserLevel() >= 35) {
        settlement.children.push({
            title: '취소 수기 입금',
            to: 'transactions-settle-cancel-deposits',
        })    
    }
    return settlement
}

const _getSettlementHistoryMenu = () => {
    const settle_histories = <any>{
        title: '정산 이력',
        icon: { icon: 'tabler:calendar-time' },
        children: [],
    }
    settle_histories.children.push({
        title: getUserLevel() === 10 ? '정산 이력' : '가맹점 정산 이력',
        to: 'transactions-settle-histories-merchandises',
    })

    if (getUserLevel() > 10) {
        settle_histories.children.push({
            title: '영업점 정산 이력',
            to: 'transactions-settle-histories-salesforces',
        })
    }
    if((getUserLevel() === 10 || getUserLevel() >= 35)) {
        if (getUserLevel() >= 35 && corp.use_different_settlement) {
            settle_histories.children.push({
                title: '차액 정산 이력',
                to: 'transactions-settle-histories-difference',
            })
        }
        if(corp.pv_options.paid.use_collect_withdraw) {
            settle_histories.children.push({
                title: '모아서 출금 이력',
                to: 'transactions-settle-histories-collect-withdraws',
            })            
        }
    }
    return settle_histories
}

export const getSettlementMenu = () => {
    const menu = <any[]>[
        _getSettlementMenu(),
        _getSettlementHistoryMenu(),
    ]
    return menu
}
