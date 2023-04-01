import axios from 'axios';

const accessToken = localStorage.getItem('accessToken') || ''
const comagain = axios.create({
  timeout: 1000,
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'Accept': 'application/json',
    "Content-Type": "application/json",
    'Access-Control-Allow-Origin' : "*",
  },
  withCredentials: true,
})
export default comagain

