import { user_info } from '@axios'

const operations = []
const complaints = []
if(user_info.value.level >= 35) {
    operations.push({
        title: 'Operation management',
        icon: { icon: 'ph-buildings' },
        children: [            
            {
                title: 'Service management',
                to: 'services-brands',
            },
            {
                title: 'PG management',
                to: 'services-pay-gateways',
            },
            {
                title: 'Operator management',
                to: 'services-operators',
            },
            {
                title: 'Bulk registration',
                to: 'services-bulk-registration',                
            },
        ]
    })
    complaints.push({
        title: 'Complaint',
        icon: { icon: 'ic-round-sentiment-dissatisfied' },
        to: 'complaints',
    },
    {
        title: 'Calendar',
        icon: { icon: 'tabler-calendar' },
        to: 'calendars',
    })
}
export default [
    { heading: 'Service' },
    ...operations,
    {
        title: 'Notice',
        icon: { icon: 'fe-notice-active' },
        to: 'posts',
    },
    ...complaints,
]
