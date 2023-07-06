import { user_info } from '@axios'

const getAbilitiesMenu = computed(() => {
    const logs = []
    const sales = []
    const sales_child = []

    if(user_info.value.level >= 35) {
        logs.push({
            title: '수수료율 변경이력',
            to: 'merchandises-fee-change-histories',
        },
        {
            title: '노티 발송이력',
            to: 'merchandises-noti-send-histories',
        })
    }
    
    if(user_info.value.level >= 35) {
        sales_child.push({
            title: '수수료율 변경이력',
            to: 'salesforces-fee-change-histories',
        })
    }
    
    if(user_info.value.level >= 13) {
        sales.push({
            title: '영업점 관리',
            icon: { icon: 'ph:share-network' },
            children: [
                { title: '영업점 목록', to: 'salesforces' },
                ...sales_child,
            ]
        })
    }

    return [
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [
                { title: '가맹점 목록', to: 'merchandises' },
                { title: '장비 관리', to: 'merchandises-terminals' },
                { title: '결제모듈 관리', to: 'merchandises-pay-modules' },
                ...logs,
            ]
        },
        ...sales,
    ]    
})

export default getAbilitiesMenu
