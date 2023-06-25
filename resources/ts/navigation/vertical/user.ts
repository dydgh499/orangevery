export default [
    { heading: 'User information' },
    {
        title: 'merchandises-management',
        icon: { icon: 'tabler-user' },
        children: [
            { title: 'merchandises', to: 'merchandises' },
            { title: 'terminal-management', to: 'merchandises-terminals' },
            { title: 'paymodule-management', to: 'merchandises-pay-modules' },
            {
                title: 'Fee change history management',
                to: 'merchandises-fee-change-histories',
            },
            {
                title: 'Noti send history',
                to: 'merchandises-noti-send-histories',
            },
        ]
    },
    {
        title: 'salesforce-management',
        icon: { icon: 'tabler-user' },
        children: [
            { title: 'salesforces', to: 'salesforces' },
            {
                title: 'Fee change history management',
                to: 'salesforces-fee-change-histories',
            },
        ]
    },
]
