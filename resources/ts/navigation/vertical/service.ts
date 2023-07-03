import { user_info } from '@axios'

const getAbilitiesMenu = computed(() => {
    const operations = []
    const complaints = []
    if (user_info.value.level >= 35) {
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
                    to: 'services-bulk-register',
                },
                {
                    title: 'computational transfer',
                    to: 'services-computational-transfer',
                },
            ]
        })
        complaints.push({
            title: 'Complaint',
            icon: { icon: 'ic-round-sentiment-dissatisfied' },
            to: 'complaints',
        })
    }
    return [
        { heading: 'Service' },
        ...operations,
        {
            title: 'Notice',
            icon: { icon: 'fe-notice-active' },
            to: 'posts',
        },
        ...complaints,
    ]
})


export default getAbilitiesMenu
