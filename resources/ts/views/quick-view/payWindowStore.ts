import { PayModule } from '@/views/types';
import { axios } from '@axios';
import * as CryptoJS from 'crypto-js';
import { cloneDeep } from 'lodash';

export const payWindowStore = () => {
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))

    const move = (url : string) => {
        location.href = url
    }

    const copy = (value : string) => {
        navigator.clipboard.writeText(value).then(() => {
            snackbar.value.show('생성하신 결제링크가 클립보드에 복사되었습니다.', 'success')
        }).catch(err => {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = value;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                snackbar.value.show('생성하신 결제링크가 클립보드에 복사되었습니다.', 'success')    
            }
            catch (err) {
                snackbar.value.show('결제링크 복사를 실패하였습니다.', 'error')
            }
        });
    }

    const send = async (params: any, phone_num: string) => {
        try {
            const r = await axios.post('/api/v1/bonaejas/sms-link-send', params)
            snackbar.value.show(phone_num+'으로 결제링크를 전송하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }

    const getPayWindowUrl = (payment_module: PayModule, pay_info: any) => {
        let type = '';
        if(payment_module.module_type === 1)
            type = 'hand'
        else if(payment_module.module_type === 2)
            type = 'auth'
        else if(payment_module.module_type === 3)
            type = 'simple'
    
        const base_url = window.location.origin + '/pay/window' + '?wc=' + payment_module.pay_window?.window_code
        if(Object.keys(pay_info).length) {
            const p = cloneDeep(pay_info)
            const sub_query = Object.keys(p).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(p[key])}`).join('&')
            return base_url+"&"+sub_query    
        }
        else
            return base_url
    }

    const renewPayWindow = async (payment_module: PayModule) => {
        return await axios.get(`/api/v1/quick-view/pay-modules/${payment_module.id}/renew`)
    }

    const multiplePayMove = (pay: PayModule) => {
        const query = encodeURIComponent(
            CryptoJS.AES.encrypt(
                JSON.stringify(
                    {
                        m: pay.mcht_id,
                        p: pay.id,
                        o: Boolean(pay.is_old_auth),
                        i: pay.installment,
                        t: Date.now() % 10000,
                        g: pay.pg_id,
                    }
                )
                , '^^_masking_^^').toString())
        return window.location.origin + '/pay/multiple-hand?e=' + query
    }

    return {
        move, copy, send, getPayWindowUrl, renewPayWindow, multiplePayMove
    }
}

export const multiplePayStore = () => {

}
