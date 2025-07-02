/*
import { getGmidMenu } from '../menus/gmid'
import { getHomeMenu } from '../menus/home'
import { getPayWindowMenu } from '../menus/pay-windows'
import { getSettlementMenu } from '../menus/settlement'
import { getTransactionMenu } from '../menus/transactions'
import { getUserMenu } from '../menus/user'
*/
import type { VerticalNavItems } from '@/@layouts/types'
import { getUserLevel } from '@axios'
import { getOtherMenu } from '../menus/other'
import { getSecurityMenu } from '../menus/security'
import { getServiceMenu } from '../menus/service'
import { getPaymentMenu } from '../menus/payment'
import { getVirtualMenu } from '../menus/virtual'

const combinedNavItems = computed(() => {
    if(getUserLevel() >= 35) {
        return [
            ...getServiceMenu(),
            ...getPaymentMenu(),
            ...getVirtualMenu(),
            ...getSecurityMenu(),
            ...getOtherMenu(),
        ] as VerticalNavItems
    }
    else {
        return [] as VerticalNavItems;
    }
});

export default combinedNavItems;
