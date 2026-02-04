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
        to: 'virtuals-cms-transactions',
    })
    return virtuals
}

export const getVirtualMenu = () => {
    const menu = <any[]>[]
    menu.push(_getVirtualMenu())
    return menu
}
