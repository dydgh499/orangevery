import { isFixplus } from '@/plugins/fixplus'
import { useQuickViewStore } from '@/views/quick-view/useStore'
import { getUserLevel, isAbleModiy, user_info } from '@axios'
import corp from '@corp'


const getUserTap = () => {
    const users = []
    const children = []

    children.push({ title: '가맹점 목록', to: 'merchandises' })    
    if(isFixplus() === false) {
        children.push({ title: '장비 관리', to: 'merchandises-terminals' })
    }
    if (isAbleModiy(0) && isFixplus() === false)
        children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules' })
    
    if(corp.pv_options.paid.use_noti && (getUserLevel() === 10 && user_info.value.use_noti)) {
        children.push({
            title: '노티 발송이력',
            to: 'merchandises-noti-send-histories',
        })
        children.push({
            title: '노티 목록',
            to: 'merchandises-noti-urls',
        })
    }
    
    users.push({ heading: 'User information' })
    users.push({
        title: '가맹점 관리',
        icon: { icon: 'tabler-user' },
        children: children,
    })

    if(getUserLevel() > 10) {
        users.push({
            title: '영업점 관리',
            icon: { icon: 'ph:share-network' },
            children: [
                { title: '영업점 목록', to: 'salesforces' },
            ]
        })
    }
    return users
}

const getAbilitiesMenu = computed(() => {
    const { getPaymentMenu } = useQuickViewStore()
    const payments = getPaymentMenu
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
        ...getUserTap(),
        { heading: 'Transaction' },
        ...payments,
        ...services,
    ]
})
export default getAbilitiesMenu

