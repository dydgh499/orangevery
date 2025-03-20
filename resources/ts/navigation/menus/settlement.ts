import { getUserLevel } from '@axios'
import corp from '@corp'

const _getSettlementMenu = () => {
    const settlement = <any>{
        title: '정산 관리',
        icon: { icon: 'tabler:calculator' },
        children: [],
    }
    
    if(getUserLevel() >= 13) {
        settlement.children.push(
            {
                title: '지급이체(가맹점)',
                to: 'transactions-settle-merchandises',
            },
            {
                title: '지급이체(영업라인)',
                to: 'transactions-settle-salesforces',
            },
        )
    }

    if(getUserLevel() === 10 || getUserLevel() >= 35) {
        settlement.children.push({
            title: '취소건 수기입금',
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
        title: getUserLevel() === 10 ? '지급이체' : '지급이체(가맹점)',
        to: 'transactions-settle-histories-merchandises',
    })

    if (getUserLevel() >= 13) {
        settle_histories.children.push({
            title: '지급이체(영업라인)',
            to: 'transactions-settle-histories-salesforces',
        })
    }
    if (getUserLevel() >= 35 && corp.use_different_settlement) {
        settle_histories.children.push({
            title: '차액정산',
            to: 'transactions-settle-histories-difference',
        })
    }
        
    if((getUserLevel() === 10 || getUserLevel() >= 35) && corp.pv_options.paid.use_collect_withdraw) {
        settle_histories.children.push({
            title: '모아서출금',
            to: 'transactions-settle-histories-collect-withdraws',
        })
        if(getUserLevel() >= 35) {
            settle_histories.children.push({
                title: '모아서출금 통계조회',
                to: 'transactions-settle-collect-withdraws',
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
