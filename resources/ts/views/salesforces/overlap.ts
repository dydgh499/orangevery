import { getLevelByIndex, getUserLevel, user_info } from '@/plugins/axios'
import type { Merchandise, SalesFilter, Salesforce } from '@/views/types'
import { SALES_LEVEL_SIZE } from './useStore'

// 영업점 수수료 자동업데이트
export const autoUpdateMerchandiseParentSalesInfo = (merchandise: Merchandise, all_sales: Salesforce[][]) => {    
    const updateMchtFee = (idx: number, dest_sales: Salesforce, merchandise: Merchandise) => {
        merchandise[`sales${idx}_id`] = dest_sales.id
        merchandise[`sales${idx}_fee`] = dest_sales.sales_fee
        return merchandise
    }

    const idx = getLevelByIndex(getUserLevel())
    let dest_sales = user_info.value
    merchandise = updateMchtFee(idx, dest_sales, merchandise)

    for (let i = idx; i < SALES_LEVEL_SIZE-1; i++) 
    {
        let _dest_sales = all_sales[i+1].find(obj => obj.id === dest_sales.parent_id)
        if(_dest_sales) {
            merchandise = updateMchtFee(i+1, _dest_sales, merchandise)
            dest_sales = _dest_sales
        }
    }
}

export const autoUpdateSalesforceInfo = (salesforce: Salesforce) => {
    if(getUserLevel() <= 30) {
        salesforce.sales_fee = user_info.value.sales_fee
    }
}

export const overlap = (all_sales: Salesforce[][], sales: Ref<SalesFilter[]>[]) => {
    const recursionSalesChildFilter = (select_idx: number, params: any) => {
        const findOneParentChildSaleses = () => {
            let child_saleses = all_sales[select_idx - 1].filter(obj => obj.parent_id === params[`sales${select_idx}_id`])
            if(child_saleses) {
                if(params[`sales${select_idx - 1}_id`]) 
                    child_saleses = child_saleses.filter(obj => obj.id === params[`sales${select_idx - 1}_id`])
                
                return child_saleses.sort((a, b) => a.sales_name.localeCompare(b.sales_name))
            }
            else
                return null
        }
        const findManyParentChildSaleses = (parent_idx:number, parent_saleses: Salesforce[]) => {
            if(parent_idx < 1)
                return
            else {
                const parent_idxs = parent_saleses.map(obj => obj.id)
                let child_saleses = all_sales[parent_idx - 1].filter(obj => obj.parent_id !== undefined && parent_idxs.includes(obj.parent_id))

                if(child_saleses) {
                    if(params[`sales${parent_idx - 1}_id`]) 
                        child_saleses = child_saleses.filter(obj => obj.id === params[`sales${parent_idx - 1}_id`])
                    
                    sales[parent_idx - 1].value = child_saleses.sort((a, b) => a.sales_name.localeCompare(b.sales_name))
                    findManyParentChildSaleses(parent_idx - 1, child_saleses)
                }
                else {
                    sales[parent_idx - 1].value = [{ id: null, sales_name: '전체'}]
                    params[`sales${parent_idx - 1}_id`] = null
                }
            }
        }

        if(select_idx < 1)
            return
        else {
            const child_saleses = findOneParentChildSaleses()
            if(child_saleses) {
                sales[select_idx - 1].value = child_saleses
                findManyParentChildSaleses(select_idx - 1, child_saleses)
            }
            else {
                sales[select_idx - 1].value = []
                params[`sales${select_idx - 1}_id`] = null
            }
        }
    }

    const recursionSalesParentFilter = (select_idx: number, params: any) => {
        const findParentSales = () => {
            const select_sales = all_sales[select_idx].find(obj => obj.id === params[`sales${select_idx}_id`])
            if(select_sales) {
                const parent_sales = all_sales[select_idx + 1].find(obj => obj.id === select_sales.parent_id)
                if(parent_sales) {
                    return parent_sales
                }
                else
                    return null
            }
            else
                return null
        }
        if(select_idx > 4)
            return
        else {
            if(params[`sales${select_idx+1}_id`] === null || params[`sales${select_idx+1}_id`] === undefined) {
                const parent_sales = findParentSales()
                if(parent_sales) {
                    params[`sales${select_idx+1}_id`] = parent_sales.id
                    sales[select_idx + 1].value = [
                        { id: null, sales_name: '전체' },
                        { id: parent_sales.id, sales_name: parent_sales.sales_name },
                    ]
                    recursionSalesParentFilter(select_idx+1, params)
                }
                else {
                    sales[select_idx - 1].value = [{ id: null, sales_name: '전체'}]
                    params[`sales${select_idx - 1}_id`] = null
                }
            }
        }

    }

    const setSelectSalesFilter = (select_idx: number, params: any) => {
        let select_sales = all_sales[select_idx].find(obj => obj.id === params[`sales${select_idx}_id`])
        sales[select_idx].value = [{ id: null, sales_name: '전체' }]
        if(select_sales) {
            sales[select_idx].value.push({ id: select_sales.id, sales_name: select_sales.sales_name })
        }
    }

    const recursionSalesFilter = (select_idx: number, params: any) => {
        if(params[`sales${select_idx}_id`]) {
            for (let i = 0; i < SALES_LEVEL_SIZE - 1; i++) {
                const sales_key = `sales${i}`
                if(params[`${sales_key}_id`]) {
                    recursionSalesParentFilter(i, params)
                    break
                }
            }
            setSelectSalesFilter(select_idx, params)
    
            for (let i = 1; i < SALES_LEVEL_SIZE; i++) {
                const sales_key = `sales${i}`
                if(params[`${sales_key}_id`]) {
                    recursionSalesChildFilter(i, params)
                    break
                }
            }
        }
        else {
            if(select_idx === 5) {
                sales[5].value = [{ id: null, sales_name: '전체'}, ...all_sales[5].sort((a, b) => a.sales_name.localeCompare(b.sales_name))]
                params[`sales${5}_id`] = null              
            }
            if(select_idx !== 5) {
                for (let i = select_idx; i < SALES_LEVEL_SIZE - 1; i++) {
                    const sales_key = `sales${i}`
                    if(params[`${sales_key}_id`]) {
                        recursionSalesParentFilter(i, params)
                        break
                    }
                }
                for (let i = SALES_LEVEL_SIZE - 1; i > 0; i--) {
                    recursionSalesChildFilter(i, params)
                    break
                }
            }
        }
    }
    return {
        recursionSalesFilter
    }
}
