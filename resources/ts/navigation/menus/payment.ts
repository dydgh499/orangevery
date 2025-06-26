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
    /*
    payments.children.push({
        title: '결제 요청이력',
        to: 'services-payments-histories',
    })
    */
    return payments
}

export const getPaymentMenu = () => {
    const menu = <any[]>[]
    if (corp.pv_options.paid.use_bill_key) {
        menu.push(_getPaymentMenu())
    }
    return menu
}

