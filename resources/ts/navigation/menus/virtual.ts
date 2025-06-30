import { getUserLevel } from '@axios'

const _getVirtualMenu = () => {
    const virtuals:any = {
        title: '가상계좌 관리',
        icon: { icon: 'tabler:cash' },
        children: []
    }
    virtuals.children.push({
        title: '가상계좌 대량출금',
        to: 'virtuals-bulk-cms-transactions',
    })
    virtuals.children.push({
        title: '은행계좌 관리',
        to: 'virtuals-bank-accounts',
    })
    virtuals.children.push({
        title: '가상계좌 출금예약 관리',
        to: 'virtuals-cms-transaction-books',
    })
    virtuals.children.push({
        title: '가상계좌 출금이력',
        to: 'virtuals-cms-transactions',
    })
    return virtuals
}

export const getVirtualMenu = () => {
    const menu = <any[]>[]
    if (getUserLevel() >= 35) {
        menu.push(_getVirtualMenu())
    }
    return menu
}

