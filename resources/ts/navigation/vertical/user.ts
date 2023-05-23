export default [
  { heading: 'User information' },
  {
    title: 'merchandises-management',
    icon: { icon: 'tabler-user' },
    children: [
      { title: 'merchandises', to: 'merchandises' },
      { title: 'paymodule-management', to: 'merchandises-pay-modules'},
    ]
  },
  {
    title: 'salesforce-management',
    icon: { icon: 'tabler-user' },
    children: [
        { title: 'salesforces', to: 'salesforces'},
        { title: 'treeview', to: 'salesforces-treeview'},
    ]
  },
]
