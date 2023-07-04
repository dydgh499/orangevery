import axiosIns from 'axios';

const getUserLevel = () => {
    if(user_info.value) {
        if(user_info.value.mcht_name) {
            user_info.value.level = 10
        }
        return user_info.value.level
    }
    return 0
}

export const getViewType = () => {
    const level = getUserLevel()
    if(level == 10)
        return 'quick-view'
    else if(level <= 30 && user_info.value.view_type == 0)
        return 'quick-view'
    else
        return 'dashboards-home'
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
