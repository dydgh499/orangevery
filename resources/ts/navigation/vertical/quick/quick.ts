import { useQuickVuewStore } from '@/views/quick-view/useStore'
import { getUserLevel, isAbleModifyMcht } from '@axios'

const { getPaymentMenu } = useQuickVuewStore()

const getUserTap = () => {
    const users = []
    const children = [
        { title: '가맹점 목록', to: 'merchandises' },
        { title: '장비 관리', to: 'merchandises-terminals' }
    ]
    if (isAbleModifyMcht())
        children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules' })

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
    const payments = getPaymentMenu
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
        { heading: 'Service' },
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ]
})
export default getAbilitiesMenu

