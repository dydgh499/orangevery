
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
    resident_num: string,
    business_num: string,

    resident_num_front: string,
    resident_num_back: string,
    use_syslink?: boolean,
    syslink?: any,
}

export interface MerchandisePropertie {
    dev_fee: float,
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
    enabled: number,
    tax_category_type: number,
    custom_id: number | null,
    use_saleslip_prov: number,
    use_saleslip_sell: number,
    use_noti: number,
    is_show_fee: number,
    use_regular_card: number,
    use_collect_withdraw: number,
    collect_withdraw_fee: number,
    withdraw_fee: number,
    settle_hold_s_dt: string,
    settle_hold_reason: string,
    is_hide_account: number,
    note: string,

    website_url: string,
    email: string,
}

export interface SalesforcePropertie {
    sales_name: string,
    settle_tax_type: number,
    settle_cycle: number,
    settle_day: number | null,
    level: number,
    view_type: number,
    note: string,
}

export interface RegularCreditCard {
    id: number,
    mcht_id: number,
    mcht_name?: string,
    yymm?: string,
    card_num: string,
    note: string,
}

export interface Product {
    id: number,
    mcht_id: number,
    product_name: string,
}

export interface Merchandise extends MerchandisePropertie, UserPropertie {    
    contact_num: string,
    regular_credit_cards?: RegularCreditCard[],
    use_pay_verification_mobile: number,
    use_multiple_hand_pay: number,
    phone_auth_limit_s_tm?: string,
    phone_auth_limit_e_tm?: string,
    phone_auth_limit_count?: number,
    single_payment_limit_s_tm?: string,
    single_payment_limit_e_tm?: string,
    specified_time_disable_limit?: number,
    specified_time_disable_limit_payments?: SpecifiedTimeDisablePayment[],
    products?: Product[],
}

export interface Salesforce extends SalesforcePropertie, UserPropertie {
    parent_id?: number,
    sales_fee?: number,
    mcht_batch_fee?: number,
    is_able_modify_mcht: number,
    is_able_unlock_mcht: number,
    is_able_under_modify: number,
    under_auto_settings?: UnderAutoSetting[],
    childs?: Salesforce[],
}


export interface UnderAutoSetting {
    id: number,
    sales_id: number,
    sales_fee: number,
    note: string,
}

export interface Operator extends BasePropertie {
    level: number
    token?: string,
    result?: number,
    appr_num?: number,
    above_phone_num?: number,
}

export interface PayWindow {
    pmod_id: number,
    pin_code: string,
    window_code: string,
    holding_able_at: string,
}

export interface PayModule {
    id: number,
    mcht_name? : string,
    mcht_id: number | null,
    pg_id: number | null,
    ps_id: number | null,
    settle_type: number | null,
    settle_fee: number,
    terminal_id: number | null,
    module_type: number,
    api_key: string,
    sub_key: string,
    p_mid: string,
    mid: string,
    tid: string,
    serial_num: string,
    comm_settle_fee: number,
    comm_settle_day: number,
    comm_settle_type: number,
    comm_calc_level: number,
    under_sales_amt: number,
    under_sales_type: number,
    under_sales_limit: number,
    begin_dt: date | null,
    ship_out_dt: date | null,
    ship_out_stat: number | null,
    is_old_auth: number,    
    installment: number,
    pay_dupe_limit:number,
    abnormal_trans_limit: number,
    pay_dupe_least: number,
    pay_year_limit: number,
    pay_month_limit: number,
    pay_day_limit: number,
    pay_single_limit: number,
    pay_disable_s_tm: string | null,
    pay_disable_e_tm: string | null,
    contract_s_dt: date | null,
    contract_e_dt: date | null,
    show_pay_view: number,
    note: string,
    filter_issuers: string[],
    fin_id: number | null,
    fin_trx_delay: number,
    cxl_type: number,
    use_realtime_deposit: number,
    payment_term_min: number,
    pay_key?: string,
    sign_key?: string,
    pay_window: PayWindow | null,
}

export interface PayGateway {
    id: number | null,
    pg_type: number | null,
    pg_name: string,
    rep_name: string,
    company_name: string,
    business_num: string,
    phone_num: string,
    addr:string,
    settle_type:number,
    p_mid: string,
    mid: string,
    api_key: string,
    sub_key: string,
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

export interface FinanceVan {
    id: number | null,
    finance_company_num: number | null,
    is_agency_van: number,
    api_key: string,
    sub_key: string,
    enc_key: string,
    iv: string,
    nick_name: string,

    dev_fee: number,
    corp_code: string,
    corp_name: string,
    bank_code: string,
    withdraw_acct_num: string,

    min_balance_limit: number,
    deposit_type: number,
    balance_status?: number,
    balance?: number,
}

export interface RealtimeHistory {
    id: number,
    mcht_name: string,
    appr_num: string,
    trans_id: number,
    result_code: string,
    request_type: number,
    message: string,
    amount: number,
    acct_num: string,
    acct_bank_name: string,
    acct_bank_code: string,
    trans_seq_num: string,
    created_at: string,
    updated_at: string,
}
interface BrandBaseInfo {
    company_name: string,
    rep_name: string,
    phone_num: string,
    business_num: string,
    addr: string,
}

interface FreeOption {
    use_hand_pay: boolean,
    use_auth_pay: boolean,
    use_simple_pay: boolean,
    use_search_date_detail: boolean,
    use_tid_duplicate: boolean,
    use_mid_duplicate: boolean,
    use_fix_table_view: boolean,
    fix_table_size: number,
    init_search_filter: boolean,
    resident_num_masking: boolean,
    pay_module_detail_view: boolean,
    sales_slip: {
        merchandise: BrandBaseInfo
    },
    bonaeja: {
        user_id: string,
        api_key: string,
        sender_phone: string,
        receive_phone: string,
        min_balance_limit: number,
    },
    default: {
        installment: number,
        abnormal_trans_limit: number,
        is_show_fee: number,
    },
    secure: {
        mcht_id_level: number,
        mcht_pw_level: number,
        account_lock_limit: number,
        sales_id_level: number,
        sales_pw_level: number,
        login_only_operate: number,
    }
}
interface PaidOption {
    use_acct_verification: boolean, // 예금주 검증
    subsidiary_use_control: boolean, // 하위 영업점 전산 사용 ON/OFF
    use_hand_pay_sms: boolean, // 수기결제 SMS
    use_realtime_deposit: boolean,  // 실시간 결제모듈
    use_issuer_filter: boolean, // 카드사 필터링
    use_dup_pay_validation: boolean,    // 중복결제 검증 사용 여부
    use_forb_pay_time: boolean,   // 결제금지시간 지정 사용 여부
    use_pay_limit: boolean,   // 결제한도 지정 사용 여부
    use_online_pay: boolean,    // 통합 API KEY
    use_tid_create: boolean,    // TID 생성버튼
    use_mid_create: boolean,    // MID 생성버튼
    use_pmid: boolean,
    use_pay_verification_mobile: boolean, //휴대폰 인증 
    use_regular_card: boolean,
    use_collect_withdraw: boolean, // 가맹점 모아서 출금
    use_collect_withdraw_scheduler: boolean, // 모아서 출금 스케줄링
    use_withdraw_fee: boolean,  // 출금 수수료
    use_head_office_withdraw: boolean,
    use_noti: boolean,
    use_finance_van_deposit: boolean, //금융 VAN 송금
    use_before_brand_info: boolean, // 이전 서비스 정보 사용
    use_multiple_hand_pay: boolean, // 다중 수기결제
    use_mcht_blacklist: boolean, // 가맹점 블랙리스트
    use_part_cancel: boolean, // 부분취소 사용여부
    use_settle_hold: boolean, // 지급보류
    use_hide_account: boolean, // 계좌 숨김
    use_settle_count: boolean, // 정산시 개수 노출
    sales_parent_structure: boolean, // 영업점 종속구조
    use_specified_limit: boolean,   // 지정시간 제한
    use_syslink: boolean,
    use_product: boolean,   // 수기단말기 상품선택
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
        [key: string]: string|boolean,
    },
    visibles: {
        abnormal_trans_sales: boolean,
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
    og_img: string | null,
    login_img: string | null,

    logo_file: File | undefined,
    favicon_file: File | undefined,
    og_file: File | undefined,
    login_file: File | undefined,
    // 운영 정보
    og_description: string,
    note: string,
    company_name: string,
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
    is_transfer: number,
    deposit_day: number,
    deposit_amount: number,
    dev_fee: number,
    dev_settle_type: number,
    extra_deposit_amount: number,
    curr_deposit_amount: number,
    last_dpst_at: datetime,
    use_different_settlement: number,
    before_brand_infos: BeforeBrandInfo[],
    different_settlement_infos: DifferentSettlementInfo[],
    operator_ips: OperatorIp[],
    updated_at: datetime,
    created_at: datetime,
}

export interface BeforeBrandInfo extends BrandBaseInfo {
    id: number,
    apply_s_dt: string,
    apply_e_dt: string,
}

export interface DifferentSettlementInfo {
    id: number,
    pg_type: number,
    rep_mid: string,
    sftp_id: string,
    sftp_password: string,
}

export interface OperatorIp {
    id: number,
    brand_id: number,
    enable_ip: string,
}

export interface DeductionHeader {
    deduction: {
        input?: string;
        amount?: string;
    };
    cancel_deposit?: {
        amount?: string;
    }
};

export interface Transaction {
    id: number,
    mcht_id: number | null,

    dev_settle_type: number,
    dev_realtime_fee: float
    dev_fee: float,
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
    mcht_settle_id: number | null,
    mcht_settle_amount: number,
    //
    trx_dt: string | null,
    trx_tm: string | null,
    cxl_dt: string | null,
    cxl_tm: string | null,
    trx_dttm?: string,
    cxl_dttm?: string,
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
    nick_name?: string,
    addr?: string,
    resident_num?: string,
    business_num?: string,
    realtimes?: RealtimeHistory[],
    cancel_deposits?: CancelDeposit[],
    use_realtime_deposit?: number,
    cxl_type?: number,
    fin_trx_delay?: number
    realtime_result?: string,
    use_collect_withdraw?: number,
    profit?: number,
    trx_amount?: number,    
    pg_settle_type: number,
}

export interface SalesSlip extends Transaction {
    result_cd?: string,
    result_msg?: string,
    is_show_fee: number, 
    use_saleslip_prov: number, 
    use_saleslip_sell: number, 
    tax_category_type: number,
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

export interface TotalSettle {
    count: number,
    amount: number,
    trx_amount: number,
    hold_amount: number,
    settle_fee: number,
    total_trx_amount: number,
    profit: number,
}
export interface Settle extends TotalSettle, Bank {
    id: number,
    level: number,
    user_name: string,
    mcht_name?: string,
    sales_name?: string,
    appr: TotalSettle,
    cxl: TotalSettle,
    total: TotalSettle,
    deduction: {
        amount: number,
    },
    settle: {
        amount:number,       // 정산금액
        deposit:number,      // 입금금액
        transfer: number,    // 이체금액
        cancel_deposit_amount?: number,
    },
    terminal: {
        amount: number,
        under_sales_amount: number,
        settle_pay_module_idxs: number[],
    },
    sector: string,
    rep_name: string,
    phone_num: string,
    resident_num: string,
    business_num: string,
    addr: string,
    use_collect_withdraw: number,    
    cancel_deposit_idxs: number[],
    settle_transaction_idxs: number[],
    settle_hold_s_dt?: string,
    settle_hold_reason?: string,
}

export interface SettlesHistory extends Bank{
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
    settle_dt: Date,
    deposit_dt: Date,
    deposit_status: Boolean,
}
export interface Post {
    id: number,
    title: string,
    level: number,
    content: string,
    type:number,
    writer: string,
    replies?: object[],
    parent_id: number | null,
    is_reply: number,
    updated_at: string,
}

export interface Complaint {
    id: number,
    mcht_id: number | null,
    mcht_name: string | null,
    tid: string,
    cust_name: string,
    appr_dt: string | null,
    appr_num: string,
    phone_num: string,
    hand_cust_name: string,
    hand_phone_num: string,
    issuer: string | null,
    pg_id: number | null,
    pg_name: string | null,
    type: number | null,
    entry_path: string,
    is_deposit: number,
    complaint_status: number,
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
    user_agent: string,
    item_name: string,
    buyer_name: string,
    buyer_phone: string,
    installment: number,
    ord_num: string,
    temp?: string,
}

export interface HandPay extends BasePay {
    card_num: string,
    yymm: string,
    auth_num?: string,
    card_pw?: string,
}

export interface AuthPay extends BasePay{
    return_url: string,
}

export interface SimplePay extends BasePay {
    route: string,
    return_url: string,
}

export interface MultipleHandPay extends HandPay {
    status_color?: string,
    status_icon?: string,
}
//----------------------------
export interface MchtRecentTransaction {
    appr_amount: number,
    appr_count: number,
    cxl_amount: number,
    cxl_count: number,
    profit: number,
    day?: string,
    mcht_name?: string,
}
export interface MchtRecentTransactions {
    monthly : {
        [key: string]: MchtRecentTransaction        
    },
    daily : MchtRecentTransaction[],
    mchts : MchtRecentTransaction[],
}

export interface TransWeekChart {
    [key: string]: TransChart
}
// --------------------------
interface Series {
    name: string,
    data: number[]
}

export interface TransChartData {
    amount: number
    count: number
    hold_amount: number
    profit: number
    settle_fee: number
    total_trx_amount: number
    trx_amount: number
    profit_rate?: number
    amount_rate?: number
    week?: TransWeekChart
}

export interface TransChart extends TransChartData {
    appr: TransChartData
    cxl: TransChartData
    modules: {
        terminal_count: number
        hand_count: number
        auth_count: number
        simple_count: number
    }
    [key: string]: TransChart; // Index signature
}

export interface MonthlyTransChart {
    [key: string]: TransChart
}
export interface UpSideChartData {
    add_rate: number,
    del_rate: number,
}
export interface UpSideChart {
    total: number,
    cur_increase_rate: number,
    graph:{
        [key: string]: UpSideChartData
    }
}

export interface LockedUser {
    id: number,
    level: number,
    user_name: string,
    nick_name: string,
    phone_num: string,
    locked_at: string,
}

export interface NotiUrl {
    id: number,
    mcht_id: number | null,
    pmod_id: number | null,
    mcht_name?: string,
    send_url: string,
    noti_status: number,
    note: string,
}

export interface OperatorHistory {
    id: number,
    nick_name: string,
    profile_img: string,
    history_type: number,
    history_title: string,
    history_target: string,
    before_history_detail: any,
    after_history_detail: any,
    created_at: string,
}

export interface NotiFormat {
    send_url: string,
    mid: string,
    tid: string,
    trx_id: string,
    amount: number,
    ord_num: string,
    appr_num: string,
    item_name: string,
    buyer_name: string,
    buyer_phone: string,
    acquirer: string,
    issuer: string,
    card_num: string,
    installment: string,
    trx_dttm: string,
    cxl_dttm: string,
    is_cancel: boolean,
    temp: string,
}

export interface HeadOffceAccount extends Bank {
    id: number,
}

export interface CancelDeposit {
    id: number,
    trans_id: number,
    mcht_name?: string,
    deposit_amount: number,
    deposit_history: string,
    deposit_dt: string,
    created_at: string,
    updated_at: string,
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

export interface MchtBlacklist {
    id: number,    
    mcht_name: string,
    nick_name: string,
    phone_num: string,
    business_num: string,
    resident_num: string,
    addr: string,
    card_num: string,
    block_reason: string,
}

export interface Holiday {
    id: number,
    rest_name: string,
    rest_dt: string,
    rest_type: number,
}

export interface SubBusinessRegistration {
    id: number,
    business_num: string,
    pg_type: number,
    registration_msg: number,
    registration_type: number,
    registration_result: number,
    card_company_name: number,
}

export interface SpecifiedTimeDisablePayment {
    id: number,
    mcht_id: number,
    disable_s_tm: string | null,
    disable_e_tm: string | null,
    disable_type: number,
}

export interface AbnormalConnectionHistory {
    id: number,
    brand_id: number,
    connection_type: number,
    action: string,
    target_key: string,
    target_value: string,
    target_level: string,
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
