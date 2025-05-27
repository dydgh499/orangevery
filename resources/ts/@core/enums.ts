export const Skins = {
  Default: 'default',
  Bordered: 'bordered',
} as const

export const RouteTransitions = {
  'Scroll X': 'scroll-x-transition',
  'Scroll X Reverse': 'scroll-x-reverse-transition',
  'Scroll Y': 'scroll-y-transition',
  'Scroll Y Reverse': 'scroll-y-reverse-transition',
  'Slide X': 'slide-x-transition',
  'Slide X Reverse': 'slide-x-reverse-transition',
  'Slide Y': 'slide-y-transition',
  'Slide Y Reverse': 'slide-y-reverse-transition',
  'Fade': 'fade-transition',
  'None': 'none',
} as const

export const StatusColors = {
    Default : 0,
    Primary : 1,
    Success : 2,
    Info    : 3,
    Warning : 4,
    Error   : 5,
    Cancel  : 6,
    Timeout : 7,
    Processing : 8,
    DepositCancelJob : 9,
} as const

export const transactionColors = {
    Book        : 0,
    Cancel      : 1,
    Success     : 2,
    PartOffset  : 3,
    AllOffset   : 4,
    Error       : 5,
    NA          : 6,
    ReSettleAble : 7,
    Timeout     : 8,
    BookCancel  : 9,
} as const

export const DateFilters = {
    NOT_USE: null,
    DATE : 0,
    DATE_RANGE : 1,
    SETTLE_RANGE : 2,
} as const


export const ItemTypes = {
    Merchandise : 0,
    Salesforce : 1,
    PaymentModule : 2,
    Transaction: 3,
    NotiUrl: 4,
} as const

export const PayParamTypes = {
    None : 0,
    SMS : 1,
    SHOP : 2,
} as const

export const HistoryTargetNames = {
    'gmids': 'GMID',
    'salesforces': '영업라인',
    'salesforces/fee-change-histories': '영업라인 수수료율',
    'merchandises': '가맹점',
    'merchandises/pay-modules': '결제모듈',
    'merchandises/noti-urls': '노티 URL',
    'merchandises/fee-change-histories': '가맹점 수수료율',
    'services/pay-gateways': 'PG사',
    'services/pay-sections': '구간',
    'services/classifications': '구분 정보',
    'services/finance-vans': '금융 VAN',
    'services/brands': '운영사',
    'transactions': '매출',
} as const
