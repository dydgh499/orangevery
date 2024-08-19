import { PayModule } from '@/views/types';
import { axios } from '@axios';
import * as CryptoJS from 'crypto-js';

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
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px'; // 화면에서 벗어나게 위치 설정
                document.body.appendChild(textarea);
                textarea.select();

                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) 
                    snackbar.value.show('생성하신 결제링크가 클립보드에 복사되었습니다.', 'success');
                else 
                    throw new Error('Copy command was unsuccessful');
            }
            catch (err) {
                snackbar.value.show('결제링크 복사를 실패하였습니다.<br>결제링크를 길게눌러 복사하세요.', 'error')
            }
        });
    }

    const send = async (params: any, buyer_phone: string) => {
        try {
            const r = await axios.post('/api/v1/bonaejas/sms-link-send', params)
            snackbar.value.show(buyer_phone+'으로 결제링크를 전송하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }

    const extend = async(window_code: string) => {
        return await axios.post(`/api/v1/quick-view/pay-modules/pay-windows/${window_code}/extend`, {})
    }

    const getPayWindowUrl = (payment_module: PayModule, param_code: string) => {
        let type = '';
        if(payment_module.module_type === 1)
            type = 'hand'
        else if(payment_module.module_type === 2)
            type = 'auth'
        else if(payment_module.module_type === 3)
            type = 'simple'
    
        let url = window.location.origin + '/pay/window' + '?wc=' + payment_module.pay_window?.window_code 
        if(param_code.length > 0)
            url += '&pc=' + param_code
        return url
    }

    const renewPayWindow = async (payment_module: PayModule, params={}) => {
        return await axios.get(`/api/v1/quick-view/pay-modules/${payment_module.id}/pay-window-renew`, {
            params: params
        })
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
        move, copy, extend, send, getPayWindowUrl, renewPayWindow, multiplePayMove
    }
}
