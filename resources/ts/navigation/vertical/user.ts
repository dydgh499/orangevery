import { user_info } from '@axios'

const getAbilitiesMenu = computed(() => {
    const logs = []
    const sales = []
    const sales_child = []

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
        sales_child.push({
            title: 'Fee change history management',
            to: 'salesforces-fee-change-histories',
        })
    }
    
    if(user_info.value.level >= 13) {
        sales.push({
            title: 'salesforce-management',
            icon: { icon: 'tabler-user' },
            children: [
                { title: 'salesforces', to: 'salesforces' },
                ...sales_child,
            ]
        })
    }

    return [
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
        ...sales,
    ]    
})

export default getAbilitiesMenu
