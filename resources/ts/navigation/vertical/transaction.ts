export default [
  { heading: 'Transaction' },
  {
    title: 'Sales management',
    icon: { icon: 'ic-outline-payments' },
    to: 'apps-transaction-sales-management',
  },
  {
    title: 'Settlement management',
    icon: { icon: 'tabler-calculator' },
    to: 'apps-transaction-settlement-management',
  },
  {
    title: 'Settlement details',
    icon: { icon: 'tabler:calendar-time' },
    to: 'apps-transaction-settlement-details',
  },
  {
    title: 'Payment',
    icon: { icon: 'fluent-payment-32-regular' },
    children: [
      {
        title: 'Hand payment',
        to: 'apps-transaction-settlement-details',
      },
      {
        title: 'Auth payment',
        to: 'apps-transaction-settlement-details',
      },
      {
        title: 'Simple payment',
        to: 'apps-transaction-settlement-details',
      },
    ]
  },
  {
    title: 'Abnormal transaction',
    icon: { icon: 'jam-triangle-danger' },
    to: 'apps-transaction-settlement-details',
  },
]
