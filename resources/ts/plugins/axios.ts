import type { Options } from '@/views/types';
import corp from '@corp';
import axiosIns from 'axios';


const levels = corp.pv_options.auth.levels

export const getLevelByIndex = (level:number) => {
    switch(level) {
        case 13:
            return 0;
        case 15:
            return 1;
        case 17:
            return 2;
        case 20:
            return 3;
        case 25:
            return 4;
        case 30:
            return 5;
        default:
            return -1;
    }
}

export const getIndexByLevel = (idx:number) => {
    switch(idx) {
        case 0:
            return 13;
        case 1:
            return 15;
        case 2:
            return 17;
        case 3:
            return 20;
        case 4:
            return 25;
        case 5:
            return 30;
        default:
            return 0;
    }
}

export const salesLevels = () => {
    const sales = <Options[]>([]);
    if(levels.sales0_use && getUserLevel() >= 13)
        sales.push({id: 13, title: levels.sales0_name})
    if(levels.sales1_use && getUserLevel() >= 15)
        sales.push({id: 15, title: levels.sales1_name})
    if(levels.sales2_use && getUserLevel() >= 17)
        sales.push({id: 17, title: levels.sales2_name})
    if(levels.sales3_use && getUserLevel() >= 20)
        sales.push({id: 20, title: levels.sales3_name})
    if(levels.sales4_use && getUserLevel() >= 25)
        sales.push({id: 25, title: levels.sales4_name})
    if(levels.sales5_use && getUserLevel() >= 30)
        sales.push({id: 30, title: levels.sales5_name})
    return sales
}

export const allLevels = () => {
    const sales = salesLevels()
    if(getUserLevel() >= 10)
        sales.unshift(<Options>({id: 10, title: '가맹점'}))
    if(getUserLevel() >= 35) {
        sales.push(<Options>({id: 35, title: '직원'}))
        sales.push(<Options>({id: 40, title: '본사'}))
        sales.push(<Options>({id: 45, title: '협력사'}))
    }
    if(levels.dev_use && getUserLevel() >= 35)
        sales.push(<Options>({id: 50, title: levels.dev_name}))
    return sales
}

export const getUserLevel = () => {
    if(user_info.value) {
        if(user_info.value.mcht_name)
            user_info.value.level = 10
        return user_info.value.level
    }
    return 0
}

export const isAbleModifyMcht = () => {
    if(getUserLevel() > 10 && getUserLevel() < 35)
        return user_info.value.is_able_modify_mcht ? true : false
    else
        return false
}

export const getViewType = () => {
    const level = getUserLevel()
    if(level == 10)
        return 'quick-view'
    else if(level <= 30 && user_info.value.view_type == 0)
        return 'quick-view'
    else if(level > 10)
        return 'dashboards-home'
    else
        return ''
}

export const pay_token  = ref<string>(localStorage.getItem('access-token') || '')
export const user_info  = ref<any>(JSON.parse(localStorage.getItem('user_info') || '{}'))

export const axios = axiosIns.create({
  // You can add your headers here
  // ================================  timeout: 3000,
  headers: {
    'Authorization': `Bearer ${pay_token.value}`,
    'Accept': 'application/json',
    "Content-Type": "application/json",
  },
  withCredentials: true
})

axios.interceptors.request.use((config:any) => {
    // 해당 Interceptor에서 헤더를 설정하기 전에 pay_token.value를 사용하여 헤더 값을 동적으로 설정합니다.
    config.headers['Authorization'] = `Bearer ${pay_token.value}`;
    return config;
});

watchEffect(() => {
    localStorage.setItem('access-token', pay_token.value)
    localStorage.setItem('user_info', JSON.stringify(user_info.value))
})
