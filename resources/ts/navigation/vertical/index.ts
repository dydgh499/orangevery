import type { VerticalNavItems } from '@/@layouts/types'
import { getViewType } from '@axios'

import home from './home'
import security from './security'
import service from './service'
import transaction from './transaction'
import user from './user'

import quick from './quick/quick'

const combinedNavItems = computed(() => {
    if(getViewType() == 'quick-view')
        return [...quick.value, ] as VerticalNavItems;
    else if(getViewType() == 'dashboards-home') {
        return [
            ...home, 
            ...user.value, 
            ...transaction.value, 
            ...service.value, 
            ...security.value
        ] as VerticalNavItems;
    }
    else
        return []
});

export default combinedNavItems;
