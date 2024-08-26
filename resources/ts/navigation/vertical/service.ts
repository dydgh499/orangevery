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
        operations[0].children.push({
            title: '운영자 관리',
            to: 'services-operators',
        })
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
    }
    return [
        { heading: 'Service' },
        ...operations,
        {
            title: '공지사항',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
    ]
})


export default getAbilitiesMenu
