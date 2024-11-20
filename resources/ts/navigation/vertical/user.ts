import { isFixplus } from '@/plugins/fixplus'
import { getUserLevel } from '@axios'
import corp from '@corp'

const getUserChildMenu = () => {
    const users = [
        { title: '가맹점 목록', to: 'merchandises'}
    ]
    const logs = []
    if(getUserLevel() >= 35) {
        logs.push({
            title: '수수료율 변경이력',
            to: 'merchandises-fee-change-histories',
        })
        if(corp.pv_options.paid.use_noti) {
            logs.push({
                title: '노티 발송이력',
                to: 'merchandises-noti-send-histories',
            },
            {
                title: '노티 목록',
                to: 'merchandises-noti-urls',
            })
        }
        if(corp.use_different_settlement) {
            logs.push({
                title: '하위사업자등록 결과',
                to: 'merchandises-sub-business-registrations',
                                
            })
        }
    }
    
    if(isFixplus() === false) 
        users.push({ title: '장비 관리', to: 'merchandises-terminals'})
    if(isFixplus() === false || (isFixplus() && getUserLevel() >= 35))
        users.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})
    if(getUserLevel() >= 35 && corp.pv_options.paid.use_bill_key)
        users.push({ title: '빌키 관리', to: 'merchandises-pay-modules-bill-keys'})
        
    users.push(...logs)
    return users
}

const getAbilitiesMenu = computed(() => {
    const sales = []
    const sales_child = []
    const users_child = getUserChildMenu()
    
    if(getUserLevel() >= 35) {
        sales_child.push({
            title: '수수료율 변경이력',
            to: 'salesforces-fee-change-histories',
        })
    }
    
    if(getUserLevel() >= 13) {
        sales.push({
            title: '영업점 관리',
            icon: { icon: 'ph:share-network' },
            children: [
                { title: '영업점 목록', to: 'salesforces'},
                ...sales_child,
            ]
        })
    }

    return [
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: users_child
        },
        ...sales,
    ]    
})

export default getAbilitiesMenu
