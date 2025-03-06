import { isFixplus, isFixplusSalesAbleUpdate } from '@/plugins/fixplus';
import type { Options } from '@/views/types';
import corp from '@corp';
import axiosIns from 'axios';

const levels = corp.pv_options.auth.levels

export const getUserType = () => {
    if (getUserLevel() == 10) 
        return {id:0, link:'/merchandises/edit/' + user_info.value.id}
    else if (getUserLevel() <= 30)
        return {id:1, link:'/salesforces/edit/' + user_info.value.id}
    else if (getUserLevel() <= 45) 
        return {id:2, link:''}
    else
        return {id:3, link:''}
}

export const getSalesLevelByCol = (key: string) => {
    switch(key) {
        case 'sales5_id':
            return 5;
        case 'sales4_id':
            return 4;
        case 'sales3_id':
            return 3;
        case 'sales2_id':
            return 2;
        case 'sales1_id':
            return 1;
        case 'sales0_id':
            return 0;
        default:
            return -1;
    }
}

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
    }
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

export const getViewType = () => {
    const level = getUserLevel()
    if(level === 10)
        return 'quick-view'
    else if(level <= 30 && user_info.value.view_type === 0)
        return 'quick-view'
    else if(level > 10)
        return 'dashboards-home'
    else
        return ''
}
// -----------------------
export const salesAuthLevelValidate = (id: number) => {
    if(id === 0 && user_info.value.auth_level >= 1)
        return true
    else if(id !== 0 && user_info.value.auth_level == 2)
        return true
    else
        return false
}

export const isAbleModiy = (id: number) => {    
    if(getUserLevel() >= 35)
        return true
    else if(getUserLevel() >= 13) {
        if(isFixplus())
            return isFixplusSalesAbleUpdate(id)
        else
            return salesAuthLevelValidate(id)
    }
    else
        return false
}

export const isAbleModiyV2 = (item: any, path: string) => {    
    if(getUserLevel() >= 35)
        return true
    else if(getUserLevel() >= 13) {
        if(isFixplus())
            return isFixplusSalesAbleUpdate(item.id)
        else {
            if(path === 'merchandises' || path === 'merchandises/noti-urls')
                return salesAuthLevelValidate(item.id)
            else if(path === 'salesforces') {
                if(item.level < user_info.value.level)
                    return salesAuthLevelValidate(item.id)
                else
                    return false
            }
            else
                return false
        }
    }
    else
        return false
}

export const isAbleModifyPrimary = (id: number) => {
    if(getUserLevel() >= 35)
        return true
    else if(getUserLevel() >= 13) {
        if(isFixplus())
            return isFixplusSalesAbleUpdate(id)
        else    // TODO: back 보완 필요
            return id ? false : true
    }
    else
        return false
}

const currentTimeFormat = () => {
    const date = new Date()
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hour = String(date.getHours()).padStart(2, '0')
    const min = String(date.getMinutes()).padStart(2, '0')
    const sec = String(date.getSeconds()).padStart(2, '0')
    return `${year}-${month}-${day} ${hour}:${min}:${sec}`;
}

export const token_expire_time = ref(<string>(localStorage.getItem('token-expire-time') || currentTimeFormat()))
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
