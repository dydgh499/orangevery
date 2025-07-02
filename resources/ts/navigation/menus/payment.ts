import corp from '@corp'

const _getPaymentMenu = () => {
    const payments:any = {
        title: '결제/이체',
        icon: { icon: 'tabler:cash' },
        to: 'pays-bulk-payments',
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

