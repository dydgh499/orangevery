import { getUserLevel } from '@axios'

const _getOperaterMenu = () => {
    const operations = <any>{
        title: '운영 관리',
        icon: { icon: 'ph-buildings' },
        children: []
    }
    if (getUserLevel() >= 40) {
        operations.children.push({
            title: '운영사 관리',
            to: 'services-brands',
        })
        operations.children.push({
            title: '운영자 관리',
            to: 'services-operators',
        })
    }
    operations.children.push({
        title: '금융VAN 관리',
        to: 'services-pay-gateways',
    })
    return operations
}

export const getServiceMenu = () => {
    const menu = <any[]>[]
    if (getUserLevel() >= 35) {
        menu.push({ heading: 'Operate' })
        menu.push(_getOperaterMenu())
    }
    return menu
}
