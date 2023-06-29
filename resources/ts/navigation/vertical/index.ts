import type { VerticalNavItems } from '@/@layouts/types'

import home from './home'
import service from './service'
import transaction from './transaction'
import user from './user'

const combinedNavItems = computed(() => {
    return [...home, ...user.value, ...transaction.value, ...service.value] as VerticalNavItems;
});

export default combinedNavItems;
