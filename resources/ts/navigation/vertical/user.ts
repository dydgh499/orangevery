import { isFixplus } from '@/plugins/fixplus'
import { getUserLevel } from '@axios'
import corp from '@corp'

const getMchtChildMenu = () => {
    const users = {
        title: '가맹점 관리',
        icon: { icon: 'tabler-user' },
        children: [{ title: '가맹점 목록', to: 'merchandises'}]
    }
    
    if(isFixplus() === false) 
        users.children.push({ title: '장비 관리', to: 'merchandises-terminals'})
    if(isFixplus() === false || (isFixplus() && getUserLevel() >= 35))
        users.children.push({ title: '결제모듈 관리', to: 'merchandises-pay-modules'})

    if(getUserLevel() >= 35) {
	    if(corp.pv_options.paid.use_bill_key)
    	    users.children.push({ title: '빌키 관리', to: 'merchandises-pay-modules-bill-keys'})
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
    const sales = <any>({})
    if(getUserLevel() >= 13) {
        sales.title = '영업점 관리'
        sales.icon  = { icon: 'ph:share-network' },
        sales.children  = [{ title: '영업점 목록', to: 'salesforces'}]
        if(getUserLevel() >= 35) {
            sales.children.push({title: '수수료율 변경이력', to: 'salesforces-fee-change-histories'})
        }
    }
    return sales
}

const getAbilitiesMenu = computed(() => {
    const mchts = getMchtChildMenu()
    const sales = getSalesChildMenu()

    return [
        { heading: 'User information' },
        mchts,
        sales,
    ]    
})

export default getAbilitiesMenu
