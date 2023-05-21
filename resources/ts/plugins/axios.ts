import axiosIns from 'axios';

export const pay_token = ref<string>(localStorage.getItem('payvery-token') || '')
export const com_token = ref<string>(localStorage.getItem('com-token') || '')
export const axios = axiosIns.create({
  // You can add your headers here
  // ================================  timeout: 3000,
  headers: {
    'Authorization': `Bearer ${pay_token.value}`,
    'X-COM-Authorization': `Comagain ${com_token.value}`,
    'Accept': 'application/json',
    "Content-Type": "application/json",
  },
  withCredentials: true
})
watchEffect(() => {
    localStorage.setItem('payvery-token', pay_token.value)
})
