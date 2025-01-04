import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Operator, Options } from '@/views/types';
import { avatars } from '@/views/users/useStore';
import { axios, getUserLevel, user_info } from '@axios';
import corp from '@corp';

export const operator_levels:Options[] = [
    {id:35, title:'직원'},
    {id:40, title:'본사'},
]

export const useSearchStore = defineStore('operatorSearchStore', () => {    
    const store = Searcher('services/operators')
    const head  = Header('services/operators', '운영자 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'profile_img' : '프로필',
        'level'     : '등급',
        'user_name' : 'ID',
        'nick_name' : '성명',
        'phone_num' : '연락처',
        'is_2fa_use': '2FA 사용',
    }
    if(corp.pv_options.paid.use_realtime_deposit || corp.pv_options.paid.use_finance_van_deposit)
        headers['is_notice_realtime_warning'] = '송금 경고사항 알림'
    if(getUserLevel() >= 35) {
        headers['is_lock'] = '계정잠김여부'
        headers['locked_at'] = '계정잠금시간'
    }
    
    headers['is_active'] = '활성화 여부'
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'

    if(getUserLevel() >= 40)
        headers['extra_col'] = '더보기'

    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})

export const operatorActionAuthStore = defineStore('operatorActionAuthStore', () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const phoneNum2FAVertifyDialog = <any>inject('phoneNum2FAVertifyDialog')

    const headOfficeAuthValidate = async (message: string) => {  
        if(corp.pv_options.free.bonaeja?.is_use) {
            if(await alert.value.show(message)) {
                // 휴대폰 인증 후 재설정
                try {
                    const res = await axios.post('/api/v1/bonaejas/mobile-code-head-office-issuence', {
                        user_name: user_info.value.user_name
                    })
                    snackbar.value.show(res.data.message, 'success')
                    const token = await phoneNum2FAVertifyDialog.value.show(res.data.data.phone_num)
                    if(token === '')
                        return [false, '']
                    else
                        return [true, token]
                }
                catch(e:any) {
                    snackbar.value.show(e.response.data.message, 'error') 
                }
            }
            return [false, '']
        }
        else {
            snackbar.value.show('문자발송 시스템과 연동되어있지 않아 2FA 인증을 제외하였습니다.<br>문자발송 시스템과 연동하여 보안등급을 강화하는 것을 권고합니다.', 'warning')
            return [true, '']
        }
    }

    return { headOfficeAuthValidate }
})

export const defaultItemInfo = () => {
    const path  = 'services/operators'
    const item  = reactive<Operator>({
        level: 35,
        id: 0,
        user_name: '',
        user_pw: '',
        nick_name: '',
        phone_num: '',
        profile_img: avatars[Math.floor(Math.random() * avatars.length)],
        created_at: null,
        updated_at: null,
        is_notice_realtime_warning: 0,
        is_active: 1
    })
    return {
        path, item
    }
}
