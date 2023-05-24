export default [
    {
        heading: 'Service'
    },
    {
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
        ]
    },
    {
        title: 'Notice',
        icon: { icon: 'fe-notice-active' },
        to: 'posts',
    },
    {
        title: 'Complaint',
        icon: { icon: 'ic-round-sentiment-dissatisfied' },
        to: 'complaints',
    },
    {
        title: 'Log management',
        icon: { icon: 'mingcute-history-line' },
        to: 'merchandises',
    },
    {
        title: 'Calendar',
        icon: { icon: 'tabler-calendar' },
        to: 'calendars',
    },
]
