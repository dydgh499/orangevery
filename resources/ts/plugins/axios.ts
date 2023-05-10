import axiosIns from 'axios';

const pay_token = localStorage.getItem('payvery-token') || ''
const com_token = localStorage.getItem('com-token') || ''
const axios = axiosIns.create({
  // You can add your headers here
  // ================================  timeout: 3000,
  headers: {
    'Authorization': `Bearer ${pay_token}`,
    'X-COM-Authorization': `Comagain ${com_token}`,
    'Accept': 'application/json',
    "Content-Type": "application/json",
  },
  withCredentials: true
})
export default axios
