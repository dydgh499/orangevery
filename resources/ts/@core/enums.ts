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
