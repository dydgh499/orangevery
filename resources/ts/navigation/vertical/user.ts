export default [
  { heading: 'User information' },
  {
    title: 'Md management',
    icon: { icon: 'tabler-user' },
    children: [
      { title: 'Md list', to: 'merchandises' },
      { title: 'Paymodule management', to: 'pay-modules'},
      { title: 'Calculate', to: 'calculates' },
    ]
  },
  {
    title: 'Sf management',
    icon: { icon: 'tabler-user' },
    children: [
        { title: 'Sf list', to: 'salesforces'},
        { title: 'Unders treeview', to: 'under-treeview'},
        //{ title: 'Calculate', to: 'calculates' },
    ]
  },
]
