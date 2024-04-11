import { useRequestStore } from '@/views/request'
import { useSearchStore } from '@/views/transactions/useStore'
import { SettlesHistory } from '@/views/types'
import { axios, getLevelByIndex } from '@axios'
import corp from '@corp'

export function settlementHistoryFunctionCollect(store: any) {
    const { post, get } = useRequestStore()
    const { printer } = useSearchStore()
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))

    const rootUrlBuilder = (is_mcht: boolean, id: number) => {
        const type = is_mcht ? 'merchandises' : 'salesforces'
        return '/api/v1/manager/transactions/settle-histories/' + type + '/' + id.toString()
    }

    const deposit = async (item: SettlesHistory, is_mcht: boolean, params: any) => {
        const deposit_after_text = item.deposit_status ? '입금취소처리' : '입금처리'
        if (await alert.value.show('정말 ' + deposit_after_text + ' 하시겠습니까?')) {
            params.current_status = Number(item.deposit_status)

            await post(rootUrlBuilder(is_mcht, item.id) + '/deposit', params, true)
            store.setTable()
        }
    }

    const batchDeposit = async (selected: number[], is_mcht: boolean, params: any) => {
        if (await alert.value.show('정말 일괄 입금/입금취소처리 하시겠습니까?')) {
            params.data = []
            for (let i = 0; i < selected.length; i++) {
                const item: SettlesHistory = store.items.find(obj => obj['id'] === selected[i])
                params.data.push({
                    id: item.id,
                    current_status: Number(item.deposit_status),
                })
            }
            await post('/api/v1/manager/transactions/settle-histories/' + (is_mcht ? 'merchandises' : 'salesforces') + '/batch-deposit', params, true)
            store.setTable()
        }
    }

    const cancel = async (item: SettlesHistory, is_mcht: boolean) => {
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
                if (res.status === 201) {
                    snackbar.value.show('성공하였습니다.', 'success')
                    store.setTable()
                }
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
            }
        }
    }

    const batchCancel = async (selected: number[], is_mcht: boolean) => {
        if (selected.length > 20 && is_mcht === false)
            snackbar.value.show('일괄정산취소는 한번에 최대 20개씩 처리 가능합니다.', 'warning')
        else {
            if (await alert.value.show('정말 일괄 정산취소처리 하시겠습니까?')) {
                for (let i = 0; i < selected.length; i++) {
                    const item: SettlesHistory = store.items.find(obj => obj['id'] === selected[i])
                    if (item) {
                        let params = {
                            level: is_mcht ? 10 : item.level,
                            current_status: Number(item.deposit_status),
                            use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
                        }
                        await axios.delete(rootUrlBuilder(is_mcht, item.id), { params: params })
                    }
                }
                snackbar.value.show('성공하였습니다.', 'success')
                store.setTable()
            }
        }
    }

    const download = async (item: SettlesHistory, is_mcht: boolean) => {
        if (await alert.value.show('정산매출을 다운로드 하시겠습니까?')) {
            const params: Record<string, string | number> = {
                page: 1,
                page_size: 9999999,
                level: is_mcht ? 10 : item.level,
                use_realtime_deposit: Number(corp.pv_options.paid.use_realtime_deposit)
            };
            if (is_mcht)
                params['mcht_settle_id'] = item.id
            else {
                const idx = getLevelByIndex(item.level)
                params['sales' + idx + '_settle_id'] = item.id
            }
            const res = await get('/api/v1/manager/transactions', { params: params })
            if (res.status == 200) {
                snackbar.value.show('엑셀 출력중 입니다..', 'success')
                printer(1, res.data.content)
                snackbar.value.show('성공하였습니다.', 'success')
            }
            else
                snackbar.value.show(res.data.message, 'error')
        }
    }

    const addDeduct = async (addDeductDialog: any, item: SettlesHistory, is_mcht: boolean) => {
        const deduct_amount = await addDeductDialog.show(item)
        if (deduct_amount) {
            if (await alert.value.show(`정말 ${parseInt(deduct_amount).toLocaleString()}원을 차감하시겠습니까?`)) {
                await post(rootUrlBuilder(is_mcht, item.id) + '/add-deduct', { 'deduct_amount': deduct_amount }, true)
                store.setTable()
            }
        }
    }

    const linkAccount = async (item: SettlesHistory, is_mcht: boolean) => {
        const dest_name = is_mcht ? '가맹점' : '영업점'
        if (await alert.value.show(`정말 해당 ${dest_name}의 계좌정보를 동기화하시겠습니까?`)) {
            await post(rootUrlBuilder(is_mcht, item.id) + '/link-account', {}, true)
            store.setTable()
        }
    }

    const batchLinkAccount = async (selected: number[], is_mcht: boolean) => {
        const params:any = {}
        const dest_name = is_mcht ? '가맹점' : '영업점'
        if (await alert.value.show(`정말 선택한 ${dest_name}들의 계좌정보를 동기화하시겠습니까?`)) {
            params.data = selected
            await post('/api/v1/manager/transactions/settle-histories/' + (is_mcht ? 'merchandises' : 'salesforces') + '/batch-link-account', params, true)
            store.setTable()
        }
    }

    return {
        deposit, batchDeposit, cancel, batchCancel, download, addDeduct, linkAccount, batchLinkAccount
    }
}
