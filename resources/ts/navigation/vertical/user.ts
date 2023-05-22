export default [
  { heading: 'User information' },
  {
    title: 'Md management',
    icon: { icon: 'tabler-user' },
    children: [
      { title: 'Md list', to: 'merchandises' },
      { title: 'Paymodule management', to: 'pay-modules'},
      { title: 'Calculate', to: 'md-calculates' },
    ]
  },
  {
    title: 'Sf management',
    icon: { icon: 'tabler-user' },
    children: [
        { title: 'Sf list', to: 'salesforces'},
        { title: 'Unders treeview', to: 'treeview'},
        { title: 'Calculate', to: 'sf-calculates' },
        //{ title: 'Calculate', to: 'calculates' },
    ]
  },
]
