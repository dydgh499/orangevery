
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
    id: number | null,
    title: string,
}
export interface StringOptions {
    id: string,
    title: string,
}

export interface Pagenation {
    total_count: number,
    total_page: number,
    total_range: number,
}
export interface FilterItem {
    ko: string;
    visible: boolean | null;
    idx: number | null;
}
export interface Filter {
    [key: string]: Filter | FilterItem;
}
export interface SubFilter {
    ko: string,
    width: number,
    s_col: string,
    e_col: string,
    type: string,
}

export interface Tab {
    icon: string,
    title: string,
}
//----------------- 검색 -------------
export interface Bank {
    acct_bank_name: string,
    acct_bank_code: string | number | null;
    acct_num: string,
    acct_name: string,
}

export interface Contract {
    id_img: string,
    passbook_img: string,
    contract_img: string,
    bsin_lic_img: string,
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
    profile_file?: File | null,
    profile_img?: string,
    is_lock?: number,
    locked_at?: string,
    is_2fa_use?: boolean,
    password_change_at?: string,
    created_at: datetime | null,
    updated_at: datetime | null,
}
export interface UserPropertie extends BasePropertie, Bank, Contract {
    addr: string,
    business_num: string,
    business_type: number,
    note: string,
}

export interface Operator extends BasePropertie {
    level: number
    token?: string,
    result?: number,
    appr_num?: number,
    above_phone_num?: number,
    is_notice_realtime_warning: number,
    is_active: number,
}

export interface PayModule {
    id: number,
    api_key: string,
    sub_key: string,
    module_type: number,
    note: string,
    mid: string,
    tid: string,
    pg_id: number | null,
    ps_id: number | null,
    is_old_auth: number,
}

export interface PayGateway {
    id: number | null,
    pg_type: number | null,
    pg_name: string,
    rep_name: string,
    company_name: string,
    business_num: string,
    phone_num: string,
    addr: string,
}

export interface PaySection {
    id: number | null,
    pg_id: number,
    name: string,
    trx_fee: float,
    is_delete: boolean,
}

export interface FinanceVan {
    id: number,
    finance_company_num: number | null,
    api_key: string,
    sub_key: string,
    enc_key: string,
    iv: string,
    nick_name: string,

    corp_code: string,
    corp_name: string,
    bank_code: string,
    withdraw_acct_num: string,

    balance_status?: number,
    balance?: number,
}

interface BrandBaseInfo {
    company_name: string,
    rep_name: string,
    phone_num: string,
    business_num: string,
    addr: string,
}

interface FreeOption {
    use_account_number_duplicate: number,
    bonaeja: {
        user_id: string,
        api_key: string,
        sender_phone: string,
        receive_phone: string,
        min_balance_limit: number,
        is_use?: boolean,
    },
}
interface PaidOption {
    yn_delivery_mode: boolean,
}
interface AuthOption {
}

interface ThemeCSS {
    main_color?: string,
}

export interface IdentityDesign {
    name:string,
    dns: string,
    theme_css: ThemeCSS,
    og_description: string,

    logo_img: string,
    favicon_img: string,
    og_img: string,
    login_img: string,

    logo_file: File | undefined,
    favicon_file: File | undefined,
    og_file: File | undefined,
    login_file: File | undefined,    
}

export interface Brand extends Contract, IdentityDesign {
    id: number,

    note: string,
    company_name: string,
    ceo_name: string,
    //
    addr: string,
    business_num: string,
    phone_num: string,
    fax_num: string,
    ov_options: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
    updated_at: datetime,
    created_at: datetime,
}

export interface OperatorIp {
    id: number | string,
    brand_id: number,
    enable_ip: string,
    token?: string,
}

export interface Transaction {
    id: number,
    mid: string,
    tid: string,
    //    
    module_type: number | string | null,
    pg_id: number | string | null,
    pmod_id: number | string | null,
    ps_id: number | string | null,
    //
    ps_fee: Float,
    is_cancel: number,
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
    addr?: string,
    nick_name?: string,
    
    created_at?: string,
}

export interface UserPayInfo {    
    buyer_name: string,
    buyer_phone: string,
    delivery_type: boolean,
    addr?: string,
    detail_addr?: string,
    note?: string,
    option_groups?: string,
    resident_num_front?: string,
}

export interface BasePayInfo extends UserPayInfo {
    pmod_id: number | null,
    amount: number,
    user_agent: string,
    item_name: string,
    installment: number,
    ord_num: string,
    temp?: string,
}

export interface HandPay extends BasePayInfo {
    card_num: string,
    yymm: string,
    auth_num?: string,
    card_pw?: string,
}

export interface AuthPay extends BasePayInfo {
    return_url: string,
    route?: string,
}

export interface BillPay extends BasePayInfo {}

// --------------------------
interface Series {
    name: string,
    data: number[]
}

export interface LockedUser {
    id: number,
    level: number,
    user_name: string,
    nick_name: string,
    phone_num: string,
    locked_at: string,
}

export interface ActivityHistory {
    id: number,
    user_id: number,
    level: number,
    target_id: number,
    nick_name: string,
    profile_img: string,
    history_type: number,
    history_title: string,
    history_target: string,
    before_history_detail: any,
    after_history_detail: any,
    created_at: string,
}

export interface AbnormalConnectionHistory {
    id: number,
    brand_id: number,
    connection_type: number,
    action: string,
    target_key: string,
    target_value: string,
    target_level: number,
    request_ip: string,
    request_detail: string,
    mobile_type: string,
    comment: string,
    created_at: string,
}

export interface ExceptionWorkTime {
    id: number,
    oper_id: number|null,
    work_s_at: string,
    work_e_at: string,
}

export interface BillKey {
    id: number,
    pmod_id: number | null,
    buyer_name: string,
    buyer_phone: string,
    issuer: string,
    nick_name: string,
    card_num: string,
}

export interface BillKeyCreate extends BillKey {
    yymm: string,
    auth_num: string,
    card_pw: string,
}

export interface Withdraw {
    id: number,
    fin_id: number | null,

    acct_bank_name: string, // 입금 은행명 
    acct_num: number, // 입금 계좌번호
    acct_name: string, // 예금주명
    acct_bank_code: string, // 은행코드
    withdraw_amount: string, // 출금 금액
    withdraw_book_time: string, // 출금 예약 시간
}

export interface CmsTransaction {
    id: number,
    brand_id: number,
    amount: number | null,
    acct_num: number, // 계좌번호
    acct_name: string, // 예금주명
    acct_bank_code: string, // 은행코드
    acct_bank_name: string, // 입금 은행명
    withdraw_book_time: string, // 출금 예약 시간
    withdraw_status: number, // 상태
    withdraws: CmsTransactionHistory[],
}

export interface CmsTransactionHistory extends CmsTransaction {
    id: number,
    brand_id: number,
    ct_id: number,
    fin_id: number,
    message: string,
    result_code: string,
    trans_seq_num: string,
    created_at: string,
}

export interface BankAccount {
    id: number,
    brand_id: number | null,

    acct_bank_name: string, // 입금 은행명 
    acct_num: number, // 입금 계좌번호
    acct_name: string, // 예금주명
    acct_bank_code: string, // 은행코드
}

export interface Popup {
    id: number,
    user_name: string,
    profile_img: string,
    popup_title: string,
    popup_content: string,
    open_s_dt: string,
    open_e_dt: string,
    open_range?: string,
    visible?: boolean,
    is_hide?: boolean,
}

export interface Transaction {
    id: number,
    pmod_id: number,
    pg_id: number,
    ps_id: number,
    ps_fee: number,
    trx_at: string,
    is_cancel: number,
    cxl_seq: number,
    amount: number,
    module_type: number,
    mid: string,
    tid: string,
    trx_id: string,
    ori_trx_id: string,
    issuer: string,
    acquirer: string,
    appr_num: string,
    installment: string,
    buyer_name: string,
    buyer_phone: string,
    item_name: string,
}
