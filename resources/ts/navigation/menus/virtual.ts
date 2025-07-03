import corp from '@corp'

const _getVirtualMenu = () => {
    const virtuals:any = {
        title: '이체 관리',
        icon: { icon: 'tabler:building-bank' },
        children: []
    }
    virtuals.children.push({
        title: '이체하기',
        to: 'virtuals-bulk-cms-transactions',
    })
    virtuals.children.push({
        title: '이체 예약현황',
        to: 'virtuals-cms-transaction-books',
    })
    virtuals.children.push({
        title: '이체 상세이력',
        to: 'virtuals-cms-transactions',
    })
    return virtuals
}

export const getVirtualMenu = () => {
    const menu = <any[]>[]
    // TODO: 백앤드단 권한 설정 필요
    if (Boolean(corp.ov_options.paid.yn_delivery_mode)) {
        menu.push(_getVirtualMenu())
    }
    return menu
}

