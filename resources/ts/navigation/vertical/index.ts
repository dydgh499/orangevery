import type { VerticalNavItems } from '@/@layouts/types'

import home from './home'
import service from './service'
import transaction from './transaction'
import user from './user'

export default [...home, ...user, ...transaction, ...service] as VerticalNavItems
