import type { Options } from '@/views/types';
import corp from '@corp';
import axiosIns from 'axios';

export const getUserMutual = () => {
    return user_info.value.user_name
}

export const getUserType = () => {
    if (getUserLevel() <= 45) 
        return {id:2, link:''}
    else
        return {id:4, link:''}
}


export const allLevels = () => {
    const sales = []
    if(getUserLevel() >= 35) {
        sales.push(<Options>({id: 35, title: '직원'}))
        sales.push(<Options>({id: 40, title: '본사'}))
    }
    return sales
}

export const getUserLevel = () => {
    if(user_info.value) {
        return user_info.value.level
    }
    return 0
}

export const getViewType = () => {
    const level = getUserLevel()
    if(level >= 35)
        return 'virtuals-cms-transactions'
    else
        return ''
}
// -----------------------
export const isAbleUnlockMcht = () => {
    return getUserLevel() >= 35 || (getUserLevel() > 11 && user_info.is_able_unlock_mcht)
}

export const isAbleModiy = (id: number) => {    
    if(getUserLevel() >= 35)
        return true
    else
        return false
}

export const isAbleModiyV2 = (item: any, path: string) => {
    if(getUserLevel() >= 35)
        return true
    else
        return false
}

export const isAbleModifyPrimary = (id: number) => {
    if(getUserLevel() >= 35)
        return true
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
  withCredentials: false
})

axios.interceptors.request.use((config:any) => {
    // 해당 Interceptor에서 헤더를 설정하기 전에 pay_token.value를 사용하여 헤더 값을 동적으로 설정합니다.
    config.headers['Authorization'] = `Bearer ${pay_token.value}`;
    return config;
});

axios.interceptors.response.use((response) => {
    if(response.headers['token-expire-time']) {
        token_expire_time.value = response.headers['token-expire-time']
    }
    return response;
});

watchEffect(() => {
    localStorage.setItem('access-token', pay_token.value)
    localStorage.setItem('user_info', JSON.stringify(user_info.value))
})
