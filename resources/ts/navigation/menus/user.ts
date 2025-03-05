import { isFixplus } from '@/plugins/fixplus'
import { getUserLevel, user_info } from '@axios'

import corp from '@corp'

const getMchtChildMenu = () => {
    const users = {
        title: '가맹점 관리',
        icon: { icon: 'tabler-user' },
        children: [{ title: '가맹점 목록', to: 'merchandises'}]
    }

    if(isFixplus()) {
        if(getUserLevel() >= 35)
            users.children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})
    }
    else {
        // 영업점은 .. 고민해봐야함
        if(getUserLevel() === 10)
            users.children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})
        else if(getUserLevel() >= 35) {
            users.children.push({ title: '장비 관리', to: 'merchandises-terminals'})
            users.children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})
        }
        else {
            users.children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})
        }
    }

    if((getUserLevel() >= 35 || getUserLevel() == 10) && corp.pv_options.paid.use_bill_key)
        users.children.push({ title: '빌키 관리', to: 'merchandises-bill-keys'})

    if(getUserLevel() >= 35) {
        users.children.push({ title: '수수료율 변경이력', to: 'merchandises-fee-change-histories'})
    }
    if((getUserLevel() >= 35 || getUserLevel() === 10) && corp.pv_options.paid.use_noti) {
        users.children.push({ title: '노티 발송이력', to: 'merchandises-noti-send-histories'})
        users.children.push({ title: '노티 목록', to: 'merchandises-noti-urls' })
    }
    if(getUserLevel() >= 35 && corp.use_different_settlement)
        users.children.push({ title: '하위사업자등록 결과', to: 'merchandises-sub-business-registrations'})
    return users
}

const getSalesChildMenu = () => {
    const sales = <any>({
        title: '영업점 관리',
        icon: { icon: 'ph:share-network' },
        children: [{ title: '영업점 목록', to: 'salesforces'}]
    })
    if(getUserLevel() >= 35) {
        sales.children.push({title: '수수료율 변경이력', to: 'salesforces-fee-change-histories'})
        if(corp.pv_options.paid.sales_parent_structure)
            sales.children.push({title: '수수료율 테이블', to: 'salesforces-fee-table'})
    }
    return sales
}

const getShopMenu = () => {
    return {
        title: '미니 쇼핑몰',
        icon: { icon: 'tabler:shopping-cart' },
        class: 'shop()',
        params: user_info.value.shopping_mall[0]
    }
}

export const getUserMenu = () => {
    const menu = <any[]>[{ heading: 'User information' }]
    menu.push(getMchtChildMenu())
    if(getUserLevel() >= 13) 
        menu.push(getSalesChildMenu())
    if(corp.pv_options.paid.use_shop && getUserLevel() === 10) 
        menu.push(getShopMenu())
    return menu
}
