import { getUserLevel } from '@axios'

export default [
  { heading: '' },
  {
    title: '홈',
    icon: { icon: 'tabler-smart-home' },
    to: getUserLevel() === 10 ? 'quick-view' : 'dashboards-home',
  }
]
