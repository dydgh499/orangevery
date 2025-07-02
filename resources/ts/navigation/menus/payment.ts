import corp from '@corp'

const _getPaymentMenu = () => {
    const payments:any = {
        title: '정산 관리',
        icon: { icon: 'tabler:cash' },
        children: [
            {
                title: '정산하기',
                to: 'pays-bulk-payments',
            },
            {
                title: '정산현황',
                to: 'pays-transactions',
            }
        ]
    }
    return payments
}

export const getPaymentMenu = () => {
    const menu = <any[]>[]
    if (corp.ov_options.paid.use_pay_module) {
        menu.push({ heading: 'Service' })
        menu.push(_getPaymentMenu())
    }
    return menu
}

