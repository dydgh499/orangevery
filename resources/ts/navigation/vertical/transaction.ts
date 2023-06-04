export default [
  { heading: 'Transaction' },
  {
    title: 'Payment',
    icon: { icon: 'fluent-payment-32-regular' },
    children: [
      {
        title: 'Hand payment',
        to: 'transactions-hand',
      },
      {
        title: 'Auth payment',
        to: 'transactions-auth',
      },
      {
        title: 'Simple payment',
        to: 'transactions-simple',
      },
      {
        title: 'Cancel payment',
        to: 'transactions-cancel',
      },
    ]
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
