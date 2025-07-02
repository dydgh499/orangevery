const _getOtherMenu = () => {
    const menu = []
    menu.push({
        title: '설치하기',
        icon: { icon: 'tabler:download' },
        class: 'install()'
    })
    return menu
}

export const getOtherMenu = () => {
    const menu = <any[]>[
        { heading: 'ETC' },
        ..._getOtherMenu(),
    ]
    return menu
}
