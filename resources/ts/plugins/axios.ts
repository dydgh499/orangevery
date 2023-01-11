import axios from 'axios';

const accessToken = localStorage.getItem('accessToken') || ''
const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  //baseURL: 'http://localhost',
  timeout: 1000,
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'Accept': 'application/json',
    "Content-Type": "application/json",
  },
  withCredentials: true
})
export default axiosIns
