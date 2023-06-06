import corp from '@corp'

let payment_child = []
if (corp.pv_options.free.use_hand_pay) {
    payment_child.push({
        title: 'Hand payment',
        to: 'transactions-hand',
      })    
}
if (corp.pv_options.free.use_auth_pay) {
    payment_child.push({
        title: 'Auth payment',
        to: 'transactions-auth',
    })
}
if (corp.pv_options.free.use_simple_pay) {
    payment_child.push({
        title: 'Simple payment',
        to: 'transactions-simple',
    })
}
payment_child.push({
    title: 'Cancel payment',
    to: 'transactions-cancel',
})

export default [
  { heading: 'Transaction' },
  {
    title: 'Payment',
    icon: { icon: 'fluent-payment-32-regular' },
    children: payment_child
  },
  {
    title: 'transactions-management',
    icon: { icon: 'ic-outline-payments' },
    to: 'transactions',
  },
  {
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
  },  {
    title: 'settle-history',
    icon: { icon: 'tabler:calendar-time' },
    children: [
        {
            title: 'merchandise-settle-history',
            to: 'transactions-settle-history-merchandises',
          },
          {
            title: 'salesforce-settle-history',
            to: 'transactions-settle-history-salesforces',
          },
      ]
  },
  {
    title: 'Abnormal transaction',
    icon: { icon: 'jam-triangle-danger' },
    to: 'transactions-dangers',
  },
  {
    title: 'Fail transaction',
    icon: { icon: 'carbon:ai-status-failed' },
    to: 'transactions-fails',
  },
]
