import { user_info } from '@axios'

export default [
  { heading: 'Forms' },
  {
    title: 'Home',
    icon: { icon: 'tabler-smart-home' },
    to: user_info.value.level == 10 ? 'quick-view' : 'dashboards-home',
  }
]
