import { useRequestStore } from '@/views/request'
import { getLevelByIndex } from '@/views/salesforces/useStore'
import { useSearchStore } from '@/views/transactions/useStore'
import { SettlesHistories } from '@/views/types'
import { axios } from '@axios'
import corp from '@corp'

export function settlementHistoryFunctionCollect(store: any) {
    const { post, get } = useRequestStore()
    const { printer } = useSearchStore()
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    
    const deposit = async (item: SettlesHistories, is_mcht: boolean) => {
        const deposit_after_text = item.status ? '입금취소처리' : '입금처리'
        if (await alert.value.show('정말 ' + deposit_after_text + ' 하시겠습니까?')) {
            const type = is_mcht ? 'merchandises' : 'salesforces'
            const url = '/api/v1/manager/transactions/settle-histories/' + type + '/' + item.id.toString() + '/deposit'
            const res = await post(url, {})
            if(res.status === 201) {
                snackbar.value.show('성공하였습니다.', 'success')
                store.setTable()
            }
            else
                snackbar.value.show(res.data.message, 'error')
        }
    }

    const batchDeposit = async(selected:number[], is_mcht: boolean) => {
        const type = is_mcht ? 'merchandises' : 'salesforces'
        if (await alert.value.show('정말 일괄 입금/입금취소처리 하시겠습니까?')) {
            const promises = []
            for (let i = 0; i < selected.length; i++) {
                const item:SettlesHistories = store.items.find(obj => obj['id'] === selected[i])
                if(item) {
                    const url = '/api/v1/manager/transactions/settle-histories/' + type + '/' + item.id.toString() + '/deposit'
                    promises.push(post(url, {}))
                }
            }
            const results = await Promise.all(promises)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
    }

    const cancel = async (item: SettlesHistories, is_mcht: boolean) => {
        if (await alert.value.show('정말 정산취소 하시겠습니까?')) {
            const type = is_mcht ? 'merchandises' : 'salesforces'
            const url = '/api/v1/manager/transactions/settle-histories/' + type + '/' + +item.id.toString()
            const res = await axios({
                url: url,
                method: 'delete',
                params: {level: is_mcht ? 10 : item.level},
            })
            if(res.status === 201) {
                snackbar.value.show('성공하였습니다.', 'success')
                store.setTable()
            }
            else
                snackbar.value.show(res.data.message, 'error')
        }
    }
    
    const batchCancel = async(selected:number[], is_mcht: boolean) => {
        const type = is_mcht ? 'merchandises' : 'salesforces'
        if (await alert.value.show('정말 일괄 입금/입금취소처리 하시겠습니까?')) {
            const promises = []
            for (let i = 0; i < selected.length; i++) {
                const item:SettlesHistories = store.items.find(obj => obj['id'] === selected[i])
                if(item) {
                    const url = '/api/v1/manager/transactions/settle-histories/' + type + '/' + item.id.toString()
                    promises.push(axios({
                        url: url,
                        method: 'delete',
                        params: {level: is_mcht ? 10 : item.level},
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
