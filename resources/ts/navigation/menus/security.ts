import { getUserLevel } from '@axios'
import corp from '@corp'

const _getSecurityMenu = () => {
    const securities:any = {
        title: '보안 관리',
        icon: { icon: 'tabler:lock-dollar' },
        children: []
    }
    securities.children.push({
        title: '운영자 활동이력',
        to: 'services-operator-histories',
    })
    securities.children.push({
        title: '이상접속 이력',
        to: 'services-abnormal-connection-histories',
    })
    securities.children.push({
        title: '문자발송 이력',
        to: 'services-bonaejas',
    })
    if(corp.pv_options.paid.use_head_office_withdraw) {
        securities.children.push({
            title: '가상계좌 입출금',
            to: 'services-cms-transactions',
        })
    }
    if(corp.pv_options.paid.use_mcht_blacklist) {
        securities.children.push({
            title: '가맹점 블랙리스트',
            to: 'services-mcht-blacklists',
        })
    }
    if (getUserLevel() >= 40) {
        securities.children.push({
            title: '예외 작업시간 관리',
            to: 'services-exception-work-times',
        })
    }
    return securities
}

export const getSecurityMenu = () => {
    const menu = <any[]>[
        _getSecurityMenu(),
    ]
    return menu
}

