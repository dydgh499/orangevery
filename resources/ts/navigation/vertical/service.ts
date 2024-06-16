import corp from '@/plugins/corp'
import { getUserLevel } from '@axios'

const getAbilitiesMenu = computed(() => {
    const operations:any[] = []
    const complaints = []
    const popups = []
    if (getUserLevel() >= 35) {
        operations.push({
            title: '운영 관리',
            icon: { icon: 'ph-buildings' },
            children: []
        })
        if (getUserLevel() >= 40) {
            operations[0].children.push({
                title: '서비스 관리',
                to: 'services-brands',
            })
            operations[0].children.push({
                title: 'PG사 관리',
                to: 'services-pay-gateways',
            })    
        }
        operations[0].children.push({
            title: '공휴일 관리',
            to: 'services-holidays',
        })
        operations[0].children.push({
            title: '운영자 관리',
            to: 'services-operators',
        })
        operations[0].children.push({
            title: '운영자 활동이력',
            to: 'services-operator-histories',
        })
        operations[0].children.push({
            title: '이상접속 이력',
            to: 'services-abnormal-connection-histories',
        })
        operations[0].children.push({
            title: '문자발송 이력',
            to: 'services-bonaejas',
        })
        operations[0].children.push({
            title: '대량 등록',
            to: 'services-bulk-register',
        })
        if(corp.pv_options.paid.use_head_office_withdraw) {
            operations[0].children.push({
                title: '본사 지정계좌 이체',
                to: 'services-head-office-withdraw',
            })
        }
        if(corp.pv_options.paid.use_mcht_blacklist) {
            operations[0].children.push({
                title: '가맹점 블랙리스트',
                to: 'services-mcht-blacklists',
            })
        }
        operations[0].children.push({
            title: '이전 전산 연동',
            to: 'services-computational-transfer',
        })
        popups.push({
            title: '팝업 관리',
            icon: { icon: 'carbon:popup' },
            to: 'popups',
        })
        complaints.push({
            title: '민원 관리',
            icon: { icon: 'ic-round-sentiment-dissatisfied' },
            to: 'complaints',
        })
    }
    return [
        { heading: 'Service' },
        ...operations,
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
        ...popups,
        ...complaints,
    ]
})


export default getAbilitiesMenu
