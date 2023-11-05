import corp from '@/plugins/corp'
import { getUserLevel } from '@axios'

const getAbilitiesMenu = computed(() => {
    const operations:any[] = []
    const complaints = []
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
            title: '운영자 관리',
            to: 'services-operators',
        })
        operations[0].children.push({
            title: '운영자 활동이력',
            to: 'services-operator-histories',
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
        operations[0].children.push({
            title: '이전 전산 연동',
            to: 'services-computational-transfer',
        })
        if (getUserLevel() >= 50) {
            operations[0].children.push({
                title: '전산 서버 로그',
                class: 'log(1)',
                params: '',
            })
            operations[0].children.push({
                title: '노티/결제 서버 로그',
                class: 'log(2)',
                params: '',
            })
        }        
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
        ...complaints,
    ]
})


export default getAbilitiesMenu
