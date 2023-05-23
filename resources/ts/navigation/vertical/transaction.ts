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
    title: 'settlements',
    icon: { icon: 'tabler-calculator' },
    children: [
        {
          title: 'merchandise-settlements',
          to: 'transactions-settlements-merchandise',
        },
        {
          title: 'salesforce-settlements',
          to: 'transactions-settlements-salesforce',
        },
      ]
  },  {
    title: 'settlements-histories',
    icon: { icon: 'tabler:calendar-time' },
    children: [
        {
            title: 'merchandise-settlements-histories',
            to: 'transactions-settlements-histories-merchandise',
          },
          {
            title: 'salesforce-settlements-histories',
            to: 'transactions-settlements-histories-salesforce',
          },
      ]
  },
  {
    title: 'Abnormal transaction',
    icon: { icon: 'jam-triangle-danger' },
    to: 'transactions-dangers',
  },
]
