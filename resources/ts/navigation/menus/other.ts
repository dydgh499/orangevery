import corp from '@/plugins/corp';
import { getUserLevel } from '@axios';

const _getOtherMenu = () => {
    const menu = []
    menu.push({
        title: '공지사항',
        icon: { icon: 'fe-notice-active' },
        to: 'posts',
    })
    menu.push({
        title: '민원관리',
        icon: { icon: 'ic-round-sentiment-dissatisfied' },
        to: 'complaints',
    })
    if(corp.pv_options.paid.brand_mode === 1 && getUserLevel() === 13) {
        menu.push({
            title: '추천인코드관리',
            icon: { icon: 'tabler:heart-code' },
            class: 'recommandCode()'
        })
    }
    menu.push({
        title: '설치하기',
        icon: { icon: 'tabler:download' },
        class: 'install()'
    })
    return menu
}

export const getOtherMenu = () => {
    const menu = <any[]>[
        { heading: 'Service' },
        ..._getOtherMenu(),
    ]
    return menu
}
