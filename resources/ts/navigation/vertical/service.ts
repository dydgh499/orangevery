import corp from '@/plugins/corp';
import { getUserLevel } from '@axios';

const getAbilitiesMenu = computed(() => {
    const operations:any[] = []
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
        if (getUserLevel() >= 40) {
            operations[0].children.push({
                title: '운영자 관리',
                to: 'services-operators',
            })    
        }
        operations[0].children.push({
            title: '대량 등록',
            to: 'services-bulk-register',
        })
        /*
            operations[0].children.push({
                title: '이전 전산 연동',
                to: 'services-computational-transfer',
            })
        */
        operations[0].children.push({
            title: '팝업 관리',
            to: 'popups',
        })
        operations[0].children.push({
            title: '민원 관리',
            to: 'complaints',
        })
        operations[0].children.push({
            title: '예약변경 관리',
            to: 'services-book-applies',
        })
    }
    const menu = [
        { heading: 'Service' },
        ...operations,
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ]
    if(corp.pv_options.paid.use_p2p_app && getUserLevel() > 10 && getUserLevel() < 35) {
        menu.push({
            title: '추천인코드관리',
            icon: { icon: 'tabler:heart-code' },
            class: 'recommandCode()'
        })
    }
    return menu
})


export default getAbilitiesMenu
