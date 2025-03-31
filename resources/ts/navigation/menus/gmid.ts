
export const getGmidMenu = () => {
    return [
        { heading: 'User information' },
        {
            title: '가맹점 관리',
            icon: { icon: 'tabler-user' },
            children: [
                { title: '가맹점 목록', to: 'merchandises'},
                { title: '장비 관리', to: 'merchandises-terminals'},
                { title: '결제모듈 관리', to: 'merchandises-pay-modules'},
                { title: '노티 발송이력', to: 'merchandises-noti-send-histories'},
                { title: '노티 목록', to: 'merchandises-noti-urls' }
            ]
        },
        { heading: 'Transaction' },
        {
            title: '매출 관리',
            icon: { icon: 'ic-outline-payments' },
            children: [
                { title: '상세 조회', to: 'transactions' },
                { title: '통계 조회', to: 'transactions-summary' },
                { title: '이상거래 이력', to: 'transactions-dangers' },
                { title: '결제실패 이력', to: 'transactions-fails' },
                { title: '주문 이력', to: 'transactions-orders' }
            ]
        },
        {
            title: '정산 관리',
            icon: { icon: 'tabler:calculator' },
            children: [
                { title: '지급이체(가맹점)', to: 'transactions-settle-merchandises', },
                { title: '취소건 수기입금', to: 'transactions-settle-cancel-deposits',}
            ],
        },
        {
            title: '정산 이력',
            icon: { icon: 'tabler:calendar-time' },
            children: [
                { title: '지급이체', to: 'transactions-settle-histories-merchandises',},
                { title: '모아서출금', to: 'transactions-settle-histories-collect-withdraws', },
                { title: '모아서출금 통계조회', to: 'transactions-settle-collect-withdraws',}
            ],
        }
    ]
}
