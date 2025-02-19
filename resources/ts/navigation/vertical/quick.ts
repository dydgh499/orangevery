import { useQuickViewStore } from '@/views/quick-view/useStore'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'


const getHeader = () => {
    return [
        { heading: '' },
        {
            title: '홈',
            icon: { icon: 'tabler-smart-home' },
            to: 'quick-view',
        },
    ]
}

const getMchtChildMenu = () => {
    const users = (<any>([
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [{ title: '가맹점 목록', to: 'merchandises'}]
        }
    ]))
    users[1].children.push({ title: '장비 관리', to: 'merchandises-terminals' })
    if(corp.pv_options.paid.sales_parent_structure === false) 
        users[1].children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules' })

    if(corp.pv_options.paid.use_bill_key && getUserLevel() === 10)
        users[1].children.push({ title: '빌키 관리', to: 'merchandises-bill-keys'})
    if(corp.pv_options.paid.use_noti && (getUserLevel() === 10 && user_info.value.use_noti)) {
        users[1].children.push({
            title: '노티 발송이력',
            to: 'merchandises-noti-send-histories',
        },{
            title: '노티 목록',
            to: 'merchandises-noti-urls',
        })
    }
    
    if(getUserLevel() > 10) {
        users.push({
            title: '영업점 관리',
            icon: { icon: 'ph:share-network' },
            children: [
                { title: '영업점 목록', to: 'salesforces' },
            ]
        })
    }
    if(corp.pv_options.paid.use_shop && getUserLevel() === 10) {
        users.push({
            title: '미니 쇼핑몰',
            icon: { icon: 'tabler:shopping-cart' },
            class: 'shop()',
            params: user_info.value.shopping_mall[0]
        })
    }

    return users
}

const getTransactionMenu = () => {
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
    if(corp.pv_options.paid.use_shop && (getUserLevel() >= 35 || getUserLevel() === 10)) {
        transactions.children.push({
            title: '주문 관리',
            to: 'transactions-orders',
        })
    }
    return transactions
}

const getSettlementMenu = () => {
    const settlements = {
        title: '정산 이력',
        icon: { icon: 'tabler:calendar-time' },
        children: [
            {
                title: '정산 이력',
                to: 'transactions-settle-histories-merchandises',
            }
        ],
    }
    if(getUserLevel() === 10 && corp.pv_options.paid.use_collect_withdraw) {
        settlements.children.push({
            title: '모아서 출금 이력',
            to: 'transactions-settle-histories-collect-withdraws',
        })
    }
    return settlements
}

const getServiceMenu = () => {
    const services = <any>([
        { heading: 'Service' },
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ])
    if(getUserLevel() === 10) {
        services.push({
            title: '민원관리',
            icon: { icon: 'ic-round-sentiment-dissatisfied' },
            to: 'complaints',
        })
    }
    else {
        if(corp.pv_options.paid.brand_mode === 1 && getUserLevel() === 13) {
            services.push({
                title: '추천인코드관리',
                icon: { icon: 'tabler:heart-code' },
                class: 'recommandCode()'
            })
        }
    }
    services.push({
        title: '설치하기',
        icon: { icon: 'tabler:download' },
        class: 'install()'
    })
    return services
}

const getAbilitiesMenu = computed(() => {
    const menu = [
        ...getHeader(),
        ...getMchtChildMenu(),
        { heading: 'Transaction' },
    ]
    if(getUserLevel() === 10) {
        const { getPaymentMenu } = useQuickViewStore()
        if(getPaymentMenu?.children && getPaymentMenu?.children?.length > 0) {
            menu.push(getPaymentMenu)
        }
    }
    menu.push(        
        getTransactionMenu(),
        getSettlementMenu(),
        ...getServiceMenu()
    )
    return menu
})
export default getAbilitiesMenu

