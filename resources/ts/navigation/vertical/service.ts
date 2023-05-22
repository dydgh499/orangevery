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
                to: 'brands',
            },
            {

                title: 'PG management',
                to: 'pay-gateways',
            },
            {
                title: 'Operator management',
                to: 'operators',
            },
        ]
    },
    {
        title: 'Notice',
        icon: { icon: 'fe-notice-active' },
        to: 'apps-service-notice-management',
    },
    {
        title: 'Log management',
        icon: { icon: 'mingcute-history-line' },
        to: 'apps-service-log-management',
    },
    {
        title: 'Calendar',
        icon: { icon: 'tabler-calendar' },
        to: 'apps-calendar',
    },
]
