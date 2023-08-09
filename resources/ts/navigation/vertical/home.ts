import { user_info } from '@axios'

export default [
  { heading: '' },
  {
    title: '홈',
    icon: { icon: 'tabler-smart-home' },
    to: user_info.value.level == 10 ? 'quick-view' : 'dashboards-home',
  }
]
