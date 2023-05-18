
interface ErrorResponse {
    message: string,
}
interface SearchParams {
    page: number,
    page_size: number,
    s_dt: Date,
    e_dt: Date,
    search: string,
}

interface Pagenation {
    total_count : number,
    total_page  : number,
}
interface Filter { 
    key: string,
    ko: string,
    hidden: boolean,
}
//----------------- 검색 -------------
export interface UserPropertie {
    id: number
    created_at: datetime
    //    
    brand_id: number
    group_id: number
    user_name: string
    user_pw: string
    // user_pw: string
    nick_name: string
    //
    addr: string
    phone_num: string
    email: string
    resident_num:string
    business_num:string
    sector:string
    passbook_img:string
    id_img:string
    contract_img:string
    bsin_lic_img:string
    acct_num:string
    acct_nm:string
    acct_bank_nm:string
    acct_bank_cd:string
}

export interface MerchandisePropertie {
    mcht_name: string
    trx_fee:float
    hold_amt_fee:float
    abnormal_trans_limit:number
    pay_day_limit:number
    pay_year_limit:number
    use_dupe_trx:boolean
    is_show_fee:boolean
}

export interface Merchandise extends MerchandisePropertie, UserPropertie{

}

export interface SalesforcePropertie {
    tax_type:number
    trx_fee:float
}

export interface Salesforce extends SalesforcePropertie, UserPropertie {

}
// !SECTION

// App: Payment Modules
export interface PayModule
 {
    id:number
    brand_id:number
    mcht_id:number
    pg_id:number
    pg_sec_id:number
    withdraw_id:number
    module_type:number
    api_key:string
    sub_key:string
    mid:string
    tid:string
    serial_num:string
    comm_pr:number
    comm_calc_day:number
    comm_calc_id:number
    under_sales_amt:number
    begin_dt:date
    ship_out_dt:date
    ship_out_stat:number
    note:string
    is_old_auth:boolean
    use_saleslip_prov:boolean
    use_saleslip_sell:boolean
    installment_limit:number
    note:string
}
