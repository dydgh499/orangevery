import { getUserLevel } from '@axios'

const _getOperaterMenu = () => {
    const operations = <any>{
        title: '운영 관리',
        icon: { icon: 'ph-buildings' },
        children: []
    }
    if (getUserLevel() >= 40) {
        operations.children.push({
            title: '서비스 관리',
            to: 'services-brands',
        })
        operations.children.push({
            title: 'PG사 관리',
            to: 'services-pay-gateways',
        })
    }
    operations.children.push({
        title: '공휴일 관리',
        to: 'services-holidays',
    })
    if (getUserLevel() >= 40) {
        operations.children.push({
            title: '운영자 관리',
            to: 'services-operators',
        })
    }
    operations.children.push({
        title: '대량 등록',
        to: 'services-bulk-register',
    })
    /*
        operations.children.push({
            title: '이전 전산 연동',
            to: 'services-computational-transfer',
        })
    */
    operations.children.push({
        title: '팝업 관리',
        to: 'popups',
    })
    operations.children.push({
        title: '민원 관리',
        to: 'complaints',
    })
    operations.children.push({
        title: '예약변경 관리',
        to: 'services-book-applies',
    })
}

export const getServiceMenu = () => {
    const menu = <any[]>[]
    if (getUserLevel() >= 35) {
        menu.push(_getOperaterMenu())
    }
    return menu
}
