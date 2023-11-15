import { useQuickVuewStore } from '@/views/quick-view/useStore'

const { getPaymentMenu } = useQuickVuewStore()

const getAbilitiesMenu = computed(() => {
    const payments = getPaymentMenu
    return [
        { heading: '' },
        {
            title: '홈',
            icon: { icon: 'tabler-smart-home' },
            to: 'quick-view',
        },
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [
                { title: '가맹점 목록', to: 'merchandises' },
                { title: '장비 관리', to: 'merchandises-terminals' },
                //{ title: '결제모듈 관리', to: 'merchandises-pay-modules' },
            ]
        },
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

