import { useRequestStore } from '@/views/request'
import { useSearchStore } from '@/views/transactions/useStore'
import { SettlesHistories } from '@/views/types'
import { axios, getLevelByIndex } from '@axios'
import corp from '@corp'

export function settlementHistoryFunctionCollect(store: any) {
    const { post, get } = useRequestStore()
    const { printer } = useSearchStore()
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    
    const rootUrlBuilder = (is_mcht: boolean, id: number) => {
        const type  = is_mcht ? 'merchandises' : 'salesforces'
        return '/api/v1/manager/transactions/settle-histories/' + type + '/' + id.toString()
    }

    const deposit = async (item: SettlesHistories, is_mcht: boolean, params: any) => {
        const deposit_after_text = item.deposit_status ? '입금취소처리' : '입금처리'
        if (await alert.value.show('정말 ' + deposit_after_text + ' 하시겠습니까?')) {         
            params.current_status = Number(item.deposit_status)
            
            const res = await post(rootUrlBuilder(is_mcht, item.id) + '/deposit', params, true)
            if(res.status === 201)
                store.setTable()
        }
    }

    const batchDeposit = async(selected:number[], is_mcht: boolean, params:any) => {
        if (await alert.value.show('정말 일괄 입금/입금취소처리 하시겠습니까?')) {
            const promises = []
            for (let i = 0; i < selected.length; i++) {
                const item:SettlesHistories = store.items.find(obj => obj['id'] === selected[i])
                params.current_status = Number(item.deposit_status)
                if(item) {
                    promises.push(post(rootUrlBuilder(is_mcht, item.id) + '/deposit', params))
                    // 실시간 정산 이체 사용시 동시처리건수 타임아웃 예외처리
                    if(params.use_finance_van_deposit)
                        await setTimeout(() => console.log("Exceptions to the number of concurrent processing cases ..(0.1 sec)"), 100);
                }
            }
            const results = await Promise.all(promises)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
    }

    const cancel = async (item: SettlesHistories, is_mcht: boolean) => {
        if (await alert.value.show('정말 정산취소 하시겠습니까?')) {
            try {
                const res = await axios({
                    url: rootUrlBuilder(is_mcht, item.id),
                    method: 'delete',
                    params: {
                        level: is_mcht ? 10 : item.level,
                        current_status: Number(item.deposit_status),
                        use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
                    },
                })
                if(res.status === 201) {
                    snackbar.value.show('성공하였습니다.', 'success')
                    store.setTable()
                }    
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
            }
        }
    }
    
    const batchCancel = async(selected:number[], is_mcht: boolean) => {
        if (await alert.value.show('정말 일괄 정산취소처리 하시겠습니까?')) {
            const promises = []
            for (let i = 0; i < selected.length; i++) {
                const item:SettlesHistories = store.items.find(obj => obj['id'] === selected[i])
                if(item) {
                    promises.push(axios({
                        url: rootUrlBuilder(is_mcht, item.id),
                        method: 'delete',
                        params: {
                            level: is_mcht ? 10 : item.level,
                            current_status: Number(item.deposit_status),
                            use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
                        },
                    }))
                }
            }
            const results = await Promise.all(promises)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
    }
    
    const download = async (item: SettlesHistories, is_mcht: boolean) => {
        if (await alert.value.show('정산매출을 다운로드 하시겠습니까?')) {
            const params:Record<string, string|number> = {
                page: 1,
                page_size: 9999999,
                level: is_mcht ? 10 : item.level,
                use_realtime_deposit: Number(corp.pv_options.paid.use_realtime_deposit)
            };
            if(is_mcht)
                params['mcht_settle_id'] = item.id
            else {
                const idx = getLevelByIndex(item.level)
                params['sales'+idx+'_settle_id'] = item.id
            }
            const res = await get('/api/v1/manager/transactions', { params: params })
            if(res.status == 200) {
                snackbar.value.show('엑셀 출력중 입니다..', 'success')
                printer(1, res.data.content)
                snackbar.value.show('성공하였습니다.', 'success')    
            }
            else
                snackbar.value.show(res.data.message, 'error')
        }
    }
    return {
        deposit, batchDeposit, cancel, batchCancel, download
    }
}
