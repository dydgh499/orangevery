import corp from '@corp'

const _getPaymentMenu = () => {
    const payments:any = {
        title: '결제 관리',
        icon: { icon: 'tabler:cash' },
        children: []
    }
    payments.children.push({
        title: '빌키 관리',
        to: 'services-bill-keys',
    })
    payments.children.push({
        title: '결제모듈 관리',
        to: 'services-pay-modules',
    })
    payments.children.push({
        title: '대량결제',
        to: 'services-bulk-payments',
    })
    return payments
}

export const getPaymentMenu = () => {
    const menu = <any[]>[]
    if (corp.ov_options.paid.use_pay_module) {
        menu.push(_getPaymentMenu())
    }
    return menu
}

