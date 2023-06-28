import { user_info } from '@axios'

const logs = []
const sales = []

if(user_info.value.level >= 35) {
    logs.push({
        title: 'Fee change history management',
        to: 'merchandises-fee-change-histories',
    },
    {
        title: 'Noti send history',
        to: 'merchandises-noti-send-histories',
    })
}
if(user_info.value.level >= 35) {
    sales.push({
        title: 'Fee change history management',
        to: 'salesforces-fee-change-histories',
    })
}

export default [
    { heading: 'User information' },
    {
        title: 'merchandises-management',
        icon: { icon: 'tabler-user' },
        children: [
            { title: 'merchandises', to: 'merchandises' },
            { title: 'terminal-management', to: 'merchandises-terminals' },
            { title: 'paymodule-management', to: 'merchandises-pay-modules' },
            ...logs,
        ]
    },
    {
        title: 'salesforce-management',
        icon: { icon: 'tabler-user' },
        children: [
            { title: 'salesforces', to: 'salesforces' },
            ...sales,
        ]
    },
]
