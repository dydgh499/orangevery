import { useQuickViewStore } from '@/views/quick-view/useStore'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'


const getMchtChildMenu = () => {
    const users = (<any>([
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [{ title: '가맹점 목록', to: 'merchandises'}]
        }
    ]))
    if(corp.pv_options.paid.sales_parent_structure === false) {
        users[1].children.push({ title: '장비 관리', to: 'merchandises-terminals' })
        users[1].children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules' })
    }
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
    return transactions
}

const getSettlementMenu = () => {
    return {
        title: '정산 이력',
        icon: { icon: 'tabler:calendar-time' },
        to: 'transactions-settle-histories-merchandises',
    }
}

const getAbilitiesMenu = computed(() => {
    const { getPaymentMenu } = useQuickViewStore()
    const services = [
        { heading: 'Service' },
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ]    
    if(getUserLevel() === 10) {
        services.push({
            title: '민원관리',
            icon: { icon: 'ic-round-sentiment-dissatisfied' },
            to: 'complaints',
        })
    }
    return [
        { heading: '' },
        {
            title: '홈',
            icon: { icon: 'tabler-smart-home' },
            to: 'quick-view',
        },
        ...getMchtChildMenu(),
        { heading: 'Transaction' },
        getPaymentMenu,
        getTransactionMenu(),
        getSettlementMenu(),
        ...services,
    ]
})
export default getAbilitiesMenu

