
import router from '@/router';
import { Merchandise, Salesforce } from '@/views/types';
import { getLevelByIndex, getUserLevel, user_info } from '@axios';

export const IS_FIXPLUS_AGCY1_MODIFY_ABLE = ref(<boolean>(false))
export const IS_FIXPLUS_AGCY2_MODIFY_ABLE = ref(<boolean>(false))
// 픽스플러스 영업점 업데이트 가능 여부
export const isFixplusSalesAbleUpdate = (id: number) => {   
    const path = router.currentRoute.value.path
    if(path.includes('/merchandises')) {
        if(getUserLevel() === 30) { //총판
            return true
        }
        else if(getUserLevel() === 25) {    //지사
            return id ? true : false    //수정가능, 등록불가
        }
        else {  //대리점
            if(id) {
                if(getUserLevel() === 20) { // 대리점 1일 경우, 대리점 2가 있는지 여부에 따라 2가 있으면 업데이트 못함
                    // 하위 대리점이 없을 경우
                    if(IS_FIXPLUS_AGCY1_MODIFY_ABLE.value) 
                        return IS_FIXPLUS_AGCY2_MODIFY_ABLE.value   //매출이 발생했는지 확인
                    else    //하위 대리점이 있을 경우 (수정 불가)
                        IS_FIXPLUS_AGCY1_MODIFY_ABLE.value
                }
                else    //대리점 2일 경우 가맹점 정보 수정 가능
                    return IS_FIXPLUS_AGCY2_MODIFY_ABLE.value
            }
            else
                return true
        }
    }
    else if(path.includes('/salesforces')) {
        if(getUserLevel() === 30) { //총판
            return true
        }
        else if(getUserLevel() === 25) {    //지사
            return true
        }
        else {  //대리점
            return true                
        }
    }
    else
        return false
}

// 계정정보 자동업데이트(ID: 사업자, PW: 휴대폰)
export const autoUpdateMerchandiseAccount = (merchandise: Merchandise) => {    
    if( merchandise.business_num.length >= 10)
        merchandise.user_name = merchandise.business_num
    if (merchandise.phone_num.length >= 8)
        merchandise.user_pw = merchandise.phone_num
} 

// 영업점 수수료 자동업데이트
export const autoUpdateMerchandiseAgencyInfo = (merchandise: Merchandise, all_sales: Salesforce[][]) => {    
    const idx = getLevelByIndex(getUserLevel())
    let dest_sales = user_info.value
    for (let i = idx; i < 5; i++) 
    {
        merchandise[`sales${i}_id`] = dest_sales.id
        merchandise[`sales${i}_fee`] = dest_sales.sales_fee
        
        dest_sales = all_sales[i+1].find(obj => obj.id === dest_sales.parent_id)
        if(dest_sales) {
            merchandise[`sales${i+1}_id`] = dest_sales.id
            merchandise[`sales${i+1}_fee`] = dest_sales.sales_fee
        }
        else
            break
    }
}

// 영업점 정보 자동업데이트
export const autoUpdateSalesforceInfo = (salesforce: Salesforce) => {
    salesforce.settle_cycle = 0
    salesforce.settle_day = null
    salesforce.settle_tax_type = 0
    salesforce.view_type = 1
}
