import corp from '@/plugins/corp'
import { getUserLevel } from '@axios'

const getAbilitiesMenu = computed(() => {
    const securities:any[] = []
    if (getUserLevel() >= 35) {
        securities.push({ heading: 'Security' })
        securities.push({
            title: '보안 관리',
            icon: { icon: 'ph-buildings' },
            children: []
        })
        securities[1].children.push({
            title: '운영자 활동이력',
            to: 'services-operator-histories',
        })
        securities[1].children.push({
            title: '이상접속 이력',
            to: 'services-abnormal-connection-histories',
        })
        securities[1].children.push({
            title: '문자발송 이력',
            to: 'services-bonaejas',
        })
        if(corp.pv_options.paid.use_head_office_withdraw) {
            securities[1].children.push({
                title: '본사 지정계좌 이체',
                to: 'services-head-office-withdraw',
            })
        }
        if(corp.pv_options.paid.use_mcht_blacklist) {
            securities[1].children.push({
                title: '가맹점 블랙리스트',
                to: 'services-mcht-blacklists',
            })
        }
    }

    return [
        ...securities,
    ]
})
export default getAbilitiesMenu
