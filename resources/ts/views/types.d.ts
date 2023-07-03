
export type SearchItem = {
    id: number
    url: { name:string, params?: object}
    icon: string
    title: string
    category: string
}
  
export type SearchHeader = {
    header: string
    title:string
}
//------------------------------
export interface Options {
    id: number,
    title: string,
}
export interface StringOptions {
    id: string,
    title: string,
}

export interface CustomFilter {
    pg_id: number | null,
    ps_id: number | null,
    settle_type: number | null,
    terminal_id: number | null,
    custom_id: number | null,
}

export interface SalesForceFilter {
    sales5_id: number | null,
    sales4_id: number | null,
    sales3_id: number | null,
    sales2_id: number | null,
    sales1_id: number | null,
    sales0_id: number | null,
}

export interface SearchParams {
    page: number,
    page_size: number,
    s_dt: Date,
    e_dt: Date,
    custom: CustomFilter,
    sales: SalesForceFilter,
}

export interface Pagenation {
    total_count: number,
    total_page: number,
}
export interface FilterItem {
    ko: string;
    visible: boolean | null;
    idx: number | null;
}
export interface Filter {
    [key: string]: Filter | FilterItem;
}
export interface Tab {
    icon: string,
    title: string,
}
//----------------- 검색 -------------
export interface Bank {
    acct_bank_name: string,
    acct_bank_code: string | null,
    acct_num: string,
    acct_name: string,
}

export interface Contract {
    id_img: string | null,
    passbook_img: string | null,
    contract_img: string | null,
    bsin_lic_img: string | null,
    id_file: File | undefined,
    passbook_file: File | undefined,
    contract_file: File | undefined,
    bsin_lic_file: File | undefined,
}

export interface BasePropertie {
    id: number,
    user_name: string,
    user_pw: string,
    nick_name: string,
    phone_num: string,
    created_at: datetime | null,
    updated_at: datetime | null,
}
export interface UserPropertie extends BasePropertie, Bank, Contract {
    addr: string,
    resident_num: string,
    business_num: string,
}

export interface MerchandisePropertie {
    sales5_id: number | null,
    sales4_id: number | null,
    sales3_id: number | null,
    sales2_id: number | null,
    sales1_id: number | null,
    sales0_id: number | null,
    sales5_fee: float,
    sales4_fee: float,
    sales3_fee: float,
    sales2_fee: float,
    sales1_fee: float,
    sales0_fee: float,
    sales5_name?: string | null,
    sales4_name?: string | null,
    sales3_name?: string | null,
    sales2_name?: string | null,
    sales1_name?: string | null,
    sales0_name?: string | null,
    mcht_name: string,
    sector: string,
    trx_fee: float,
    hold_fee: float,
    // option
    enabled: boolean,
    custom_id: number | null,
    use_saleslip_prov: boolean,
    use_saleslip_sell: boolean,
    is_show_fee: boolean,
    note: string,
}

export interface SalesforcePropertie {
    sales_name: string,
    settle_tax_type: number,
    settle_cycle: number,
    settle_day: number | null,
    level: number,
    view_type: boolean,
    note: string,
}

export interface Merchandise extends MerchandisePropertie, UserPropertie {}

export interface Salesforce extends SalesforcePropertie, UserPropertie {}

export interface Operator extends BasePropertie{
    level: number
}

export interface PayModule {
    id: number,
    mcht_name? : string,
    mcht_id: number | null,
    pg_id: number | null,
    ps_id: number | null,
    settle_type: number | null,
    settle_fee: number | null,
    terminal_id: number | null,
    module_type: number,
    api_key: string,
    sub_key: string,
    mid: string,
    tid: string,
    serial_num: string,
    comm_settle_fee: number,
    comm_settle_type: number,
    comm_calc_level: number,
    under_sales_amt: number,
    begin_dt: date | null,
    ship_out_dt: date | null,
    ship_out_stat: number | null,
    is_old_auth: boolean,    
    installment: number,
    pay_dupe_limit:number,
    abnormal_trans_limit: number,
    pay_year_limit: number,
    pay_month_limit: number,
    pay_day_limit: number,
    pay_disable_s_tm: date | null,
    pay_disable_e_tm: date | null,
    show_pay_view: boolean,
    note: string,
}

export interface PayGateway {
    id: number | null,
    pg_type: number | null,
    pg_name: string | null,
    rep_name: string,
    company_name: string,
    business_num: string,
    phone_num: string,
    addr:string,
}

export interface PaySection {
    id: number | null,
    pg_id: number,
    name: string,
    trx_fee: float,
    is_delete: boolean,
}

export interface Classification {
    id: number | null,
    name: string,
    type: number,
}

interface FreeOption {
    use_hand_pay: boolean,
    use_auth_pay: boolean,
    use_simple_pay: boolean,
    sales_slip: {
        merchandise: {
            comepany_name: string,
            rep_name: string,
            phone_num: string,
            business_num: string,
            addr: string,
        }
    }
}
interface PaidOption {
    use_acct_verification: boolean, // 예금주 검증
    use_hand_pay_drct: boolean, // 수기결제 직접입력(가맹점)
    use_hand_pay_sms: boolean, // 수기결제 SMS
    use_realtime_deposit: boolean,  // 실시간 결제모듈
    use_issuer_filter: boolean, // 카드사 필터링
    use_dup_pay_validation: boolean,    // 중복결제 검증 사용 여부
    use_forb_pay_time: boolean,   // 결제금지시간 지정 사용 여부
    use_pay_limit: boolean,   // 결제한도 지정 사용 여부
    subsidiary_use_control: boolean, // 하위 영업점 전산 사용 ON/OFF

}
interface AuthOption {
    levels: {
        dev_use:boolean,
        dev_name:string,
        sales5_use:boolean,
        sales5_name:string,
        sales4_use:boolean,
        sales4_name:string,
        sales3_use:boolean,
        sales3_name:string,
        sales2_use:boolean,
        sales2_name:string,
        sales1_use:boolean,
        sales1_name:string,
        sales0_use:boolean,
        sales0_name:string,
    }
}
interface ThemeCSS {
    main_color: string,
}


export interface Brand extends Contract {
    id: number,
    dns: string,
    name:string,
    theme_css: ThemeCSS,
    // 운영 이미지
    logo_img: string | null,
    favicon_img: string | null,
    og_img:string | null,
    logo_file: File | undefined,
    favicon_file: File | undefined,
    og_file: File | undefined,
    // 운영 정보
    og_description: string,
    note: string,
    company_name: string,
    pvcy_rep_name: string,
    ceo_name: string,
    //
    addr: string,
    business_num: string,
    phone_num: string,
    fax_num: string,
    pv_options: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
    is_transfer: boolean,
    deposit_day: number,
    deposit_amount: number,    
    last_dpst_at: datetime,
    updated_at: datetime,
    created_at: datetime,
}

export interface DeductionHeader {
    deduction: {
        input?: string;
        amount?: string;
    };
};

export interface Transaction {
    id: number,
    mcht_id: number | null,

    sales5_name?: string,
    sales5_id: number | null,
    sales5_fee: float,
    sales5_settle_id: Date | null,
    
    sales4_name?: string,
    sales4_id: number | null,
    sales4_fee: float,
    sales4_settle_id: Date | null,

    sales3_name?: string,
    sales3_id: number | null,
    sales3_fee: float,
    sales3_settle_id: Date | null,

    sales2_name?: string,
    sales2_id: number | null,
    sales2_fee: float,
    sales2_settle_id: Date | null,

    sales1_name?: string,
    sales1_id: number | null,
    sales1_fee: float,
    sales1_settle_id: Date | null,

    sales0_name?: string,
    sales0_id: number | null,
    sales0_fee: float,
    sales0_settle_id: Date | null,

    custom_id: number | string | null,
    mcht_name?: string,
    mcht_fee: float,
    hold_fee: float,
    mid: string,
    tid: string,
    //    
    module_type: number | string | null,
    pg_id: number | string | null,
    pmod_id: number | string | null,
    ps_id: number | string | null,
    terminal_id: number | string | null,
    //
    ps_fee: Float,
    mcht_settle_type: number | null,
    mcht_settle_fee: number, 
    mcht_settle_id: Date | null,
    //
    trx_dt: Date | null,
    trx_tm: Date | null,
    cxl_dt: Date | null,
    cxl_tm: Date | null,
    trx_dttm?: Date,
    cxl_dttm?: Date,
    is_cancel: boolean,
    amount: number,
    ord_num: string,
    trx_id: string,
    //
    ori_trx_id: string,
    //
    card_num: string,
    installment: number | string | null,
    //
    issuer: string,
    acquirer: string,
    appr_num: string,
    buyer_name: string,
    buyer_phone: string,
    item_name: string,
    //
    nick_name?: string,
    addr?: string,
    resident_num?: string,
    business_num?: string,
}

export interface SalesSlip extends Transaction{
    mcht: {
       addr: string, 
       business_num: string, 
       resident_num: string, 
       id: number, 
       mcht_name: string, 
       nick_name: string, 
       is_show_fee: boolean, 
       use_saleslip_prov: boolean, 
       use_saleslip_sell: boolean, 
       user_name: string, 
    }
}

export interface Danger {
    id: number,
    mcht_name: number,
    module_type: number | string,
    item_name: string,
    amount: number,
    ord_num: string,
    trx_id: string,
    ori_trx_id: string,
    mid: string,
    tid: string,
    pg_id: number | string,
    ps_id: number | string,
    issuer: string,
    acquirer: string,
    card_num: string,
    trx_dttm: Date,
    terminal_id: number | string
    installment: number | string,
    danger_type: number | string,
    is_checked: boolean | string,
    created_at: datetime,
    updated_at:datetime,
}
export interface FailTransaction {
    id: number,
    mcht_name: string,
    pg_id: number | string,
    ps_id: number | string,
    module_type: number | string,
    trx_dttm: Date,
    amount: number,
    resuld_cd: string,
    result_msg: string,
}

export interface Settle extends Bank {
    id: number,
    level: number,
    user_name: string,
    mcht_name: string,
    count: number,
    amount: number,
    trx_amount: number,
    settle_fee: number,
    total_trx_amount: number,
    profit: number,
    appr: {
        count: number,
        amount: number,
        trx_amount: number,
        hold_amount: number,
        settle_fee: number,
        total_trx_amount: number,
        profit: number,
    },
    cxl: {
        count: number,
        amount: number,
        trx_amount: number,
        hold_amount: number,
        settle_fee: number,
        total_trx_amount: number,
        profit: number,  
    },
    deduction: {
        amount: number,
    },
    settle: {
        amount:number,       // 정산금액
        deposit:number,      // 입금금액
        transfer: string,    // 이체금액    
    },
    terminal: {
        amount: number,
    },
    sector: string,
    rep_name: string,
    phone_num: string,
    resident_num: string,
    business_num: string,
    addr: string,
}

export interface SettlesHistories extends Bank{
    id: number,
    mcht_id: number
    mcht_name: string,    
    sales_id: number,
    level: number,
    user_name: string,
    settle_amount: number,
    deduct_amount: number,
    appr_amount: number,
    cxl_amount: number,
    total_amount: number,
    acct_name: string,
    acct_num: string,
    acct_bank_name: string,
    acct_bank_code: string,
    settle_dt: Date,
    deposit_dt: Date,
    status: Boolean,
}

export interface Post {
    id: number,
    title: string,
    content: string,
    type:number,
    writer: string,
    replies?: object[],
    parent_id: number | null,
    is_reply: boolean,
}

export interface Complaint {
    id: number,
    mcht_id: number | null,
    mcht_name: string | null,
    tid: string,
    cust_name: string,
    appr_dt: Date | null,
    appr_num: string,
    phone_num: string,
    hand_cust_name: string,
    hand_phone_num: string,
    issuer: number | null,
    pg_id: number | null,
    pg_name: string | null,
    type: number | null,
    entry_path: string,
    is_deposit: boolean,
    note: string,
}

export interface FeeChangeHistory {
    id: number,
    mcht_id: number,
    bf_trx_fee: float,
    aft_trx_fee: float,
    created_at: Date,
    change_status: boolean,
}

export interface MchtFeeChangeHistory extends FeeChangeHistory {
    bf_hold_fee: float,
    aft_hold_fee: float,    
}


export interface SalesFeeChangeHistory extends FeeChangeHistory {
    bf_sales_id: number,
    aft_sales_id: number,
    level: number,
}

export interface NotiSendHistory {
    id: number,
    trans_id: number,
    http_code: number,
    retry_count: number,
    message: string,
    send_url: string,
    created_at: datetime,
}

export interface BasePay {
    pmod_id: number,
    amount: number,
    item_name: string,
    buyer_name: string,
    installment: number,
    phone: string,
    only: boolean,
    temp?: string,
}

export interface HandPay extends BasePay{
    is_old_auth: boolean,
    card_num: string,
    yymm: string,
    auth_num?: string,
    card_pw?: string,
}

export interface AuthPay extends BasePay{
    return_url: string,
}

export interface SimplehPay extends BasePay {
    route: string,
    return_url: string,
}
export interface CancelPay {
    pmod_id: number,
    amount: number,
    trx_id: string,
    only: boolean,
    temp?: string,
}
//----------------------------
export interface MchtRecentTransaction {
    date: Date,
    appr_amount: number,
    cxl_amount: number,
    total_amount: number,
    settle_amount: number,
}
