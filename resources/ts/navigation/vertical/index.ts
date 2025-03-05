import type { VerticalNavItems } from '@/@layouts/types'

import { getHomeMenu } from '../menus/home'
import { getOtherMenu } from '../menus/other'
import { getSecurityMenu } from '../menus/security'
import { getServiceMenu } from '../menus/service'
import { getSettlementMenu } from '../menus/settlement'
import { getTransactionMenu } from '../menus/transactions'
import { getUserMenu } from '../menus/user'

const combinedNavItems = computed(() => {
    const menus = [
        ...getHomeMenu(),
        ...getUserMenu(), 
        ...getTransactionMenu(), 
        ...getSettlementMenu(), 
        ...getServiceMenu(), 
        ...getSecurityMenu(),
        ...getOtherMenu(),
    ] as VerticalNavItems;
    console.log(menus)
    return menus
});

export default combinedNavItems;
