import type { VerticalNavItems } from '@/@layouts/types'
import appAndPages from './app-and-pages'
import charts from './charts'
import dashboard from './dashboard'
import forms from "./forms"
import others from './others'
import uiElements from './ui-elements'

import home from './home'
import service from './service'
import transaction from './transaction'
import user from './user'

export default [...home, ...user, ...transaction, ...service,
   ...dashboard, ...appAndPages, ...uiElements, ...forms, ...charts, ...others] as VerticalNavItems
