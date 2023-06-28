import { user_info } from '@axios'
import corp from '@corp'

const payments = []
const settles = []
const risks = []

if (corp.pv_options.free.use_hand_pay) {
    payments.push({
        title: 'Hand payment',
        to: 'transactions-hand',
    })
}
if (corp.pv_options.free.use_auth_pay) {
    payments.push({
        title: 'Auth payment',
        to: 'transactions-auth',
    })
}
if (corp.pv_options.free.use_simple_pay) {
    payments.push({
        title: 'Simple payment',
        to: 'transactions-simple',
    })
}
payments.push({
    title: 'Cancel payment',
    to: 'transactions-cancel',
})

if (user_info.value.level > 10) {
    settles.push({
        title: 'settle',
        icon: { icon: 'tabler-calculator' },
        children: [
            {
                title: 'merchandise-settle',
                to: 'transactions-settle-merchandises',
            },
            {
                title: 'salesforce-settle',
                to: 'transactions-settle-salesforces',
            },
        ]
    },
    {
        title: 'settle-histories',
        icon: { icon: 'tabler:calendar-time' },
        children: [
            {
                title: 'merchandise-settle-histories',
                to: 'transactions-settle-histories-merchandises',
            },
            {
                title: 'salesforce-settle-histories',
                to: 'transactions-settle-histories-salesforces',
            },
        ]
    })
}
if (user_info.value.level > 10) {
    risks.push({
        title: 'Abnormal transaction',
        icon: { icon: 'jam-triangle-danger' },
        to: 'transactions-dangers',
    },
    {
        title: 'Fail transaction',
        icon: { icon: 'carbon:ai-status-failed' },
        to: 'transactions-fails',
    })
}
export default [
    { heading: 'Transaction' },
    {
        title: 'Payment',
        icon: { icon: 'fluent-payment-32-regular' },
        children: payments
    },
    {
        title: 'transactions-management',
        icon: { icon: 'ic-outline-payments' },
        to: 'transactions',
    },
    ...settles,
    ...risks,
]
