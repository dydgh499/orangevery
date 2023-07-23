import { user_info } from '@axios'

const getAbilitiesMenu = computed(() => {
    const operations:any[] = []
    const complaints = []

    if (user_info.value.level >= 35) {
        operations.push({
            title: '운영 관리',
            icon: { icon: 'ph-buildings' },
            children: [
                {
                    title: '서비스 관리',
                    to: 'services-brands',
                },
                {
                    title: 'PG사 관리',
                    to: 'services-pay-gateways',
                },
                {
                    title: '운영자 관리',
                    to: 'services-operators',
                },
                {
                    title: '대량 등록',
                    to: 'services-bulk-register',
                },
                {
                    title: '이전 전산 연동',
                    to: 'services-computational-transfer',
                },
            ]
        })
        complaints.push({
            title: '민원 관리',
            icon: { icon: 'ic-round-sentiment-dissatisfied' },
            to: 'complaints',
        })
    }
    if (user_info.value.level >= 50) {
        operations[0].children.push({
            title: '로그 관리',
            class: 'log()',
            params: '',
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
