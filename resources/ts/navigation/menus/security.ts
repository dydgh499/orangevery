import { getUserLevel } from '@axios'

const _getSecurityMenu = () => {
    const securities:any = {
        title: '보안 관리',
        icon: { icon: 'tabler:lock-dollar' },
        children: []
    }
    securities.children.push({
        title: '활동이력',
        to: 'services-activity-histories',
    })
    if (getUserLevel() >= 40) {
        securities.children.push({
            title: '이상접속 이력',
            to: 'services-abnormal-connection-histories',
        })
        securities.children.push({
            title: '예외 작업시간 관리',
            to: 'services-exception-work-times',
        })
    }
    return securities
}

export const getSecurityMenu = () => {
    const menu = <any[]>[]
    if (getUserLevel() >= 35) {
        menu.push(_getSecurityMenu())
    }
    return menu
}

