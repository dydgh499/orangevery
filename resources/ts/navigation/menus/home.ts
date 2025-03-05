import { getViewType } from "@/plugins/axios"

export const getHomeMenu = () => {
    const menu = <any[]>[
        { heading: '' },
        {
            title: '홈',
            icon: { icon: 'tabler-smart-home' },
            to: getViewType(),
        },
    ]
    return menu
}
