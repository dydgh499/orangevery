import type { VerticalNavItems } from '@/@layouts/types'
import { getUserLevel } from '@axios'
import { getGmidMenu } from '../menus/gmid'
import { getHomeMenu } from '../menus/home'
import { getOtherMenu } from '../menus/other'
import { getPayWindowMenu } from '../menus/pay-windows'
import { getSecurityMenu } from '../menus/security'
import { getServiceMenu } from '../menus/service'
import { getSettlementMenu } from '../menus/settlement'
import { getTransactionMenu } from '../menus/transactions'
import { getUserMenu } from '../menus/user'

const combinedNavItems = computed(() => {
    if(getUserLevel() === 11) {
        return [
            ...getGmidMenu(),
            ...getOtherMenu(),
        ] as VerticalNavItems
    }
    else {
        return [
            ...getHomeMenu(),
            ...getUserMenu(), 
            ...getTransactionMenu(), 
            ...getSettlementMenu(), 
            ...getPayWindowMenu(),
            ...getServiceMenu(), 
            ...getSecurityMenu(),
            ...getOtherMenu(),
        ] as VerticalNavItems;
    }
});

export default combinedNavItems;
