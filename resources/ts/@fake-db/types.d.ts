import type { UserAbility } from '@/plugins/casl/AppAbility';

// 👉 Help center
export type HelpCenterSubcategoryArticlesType = {
  slug: string
  title: string
  content: string
}
export type HelpCenterSubcategoriesType = {
  icon: string
  slug: string
  title: string
  articles: HelpCenterSubcategoryArticlesType[]
}
export type HelpCenterCategoriesType = {
  icon: string
  slug: string
  title: string
  avatarColor: string
  subCategories: HelpCenterSubcategoriesType[]
}
export type HelpCenterArticlesOverviewType = {
  img: string
  slug: string
  title: string
  subtitle: string
}

export interface Faq {
  question: string
  answer: string
}

export interface FaqCategory {
  faqTitle: string
  faqIcon: string
  faqSubtitle: string
  faqs: Faq[]
}

export type ProfileChip = {
  title: string
  color: string
}

export type ProfileTabCommon = {
  icon: string
  value: string
  property: string
}
export type ProfileTeams = ProfileTabCommon & { color: string }

export type ProfileConnections = {
  name: string
  avatar: string
  isFriend: boolean
  connections: string
}

export type ProfileAvatarGroup = {
  name: string
  avatar: string
}

export type ProfileTeamsTech = {
  title: string
  avatar: string
  members: number
  chipText: string
  ChipColor: string
}

export type ConnectionsTab = {
  name: string
  tasks: string
  avatar: string
  projects: string
  connections: string
  designation: string
  isConnected: boolean
  chips: ProfileChip[]
}

export type ProfileTab = {
  teams: ProfileTeams[]
  about: ProfileTabCommon[]
  contacts: ProfileTabCommon[]
  overview: ProfileTabCommon[]
  teamsTech: ProfileTeamsTech[]
  connections: ProfileConnections[]
}

export type ProfileHeader = {
  fullName: string
  coverImg: string
  location: string
  profileImg: string
  joiningDate: string
  designation: string
  designationIcon?: string
}

export type ProjectTableRow = {
  id: number
  date: string
  name: string
  leader: string
  status: number
  avatar?: string
  avatarGroup: string[]
  avatarColor?: string
}

export type ProjectsTab = {
  hours: string
  tasks: string
  title: string
  budget: string
  client: string
  avatar: string
  members: string
  daysLeft: number
  comments: number
  deadline: string
  startDate: string
  totalTask: number
  budgetSpent: string
  description: string
  chipColor: string
  completedTask: number
  avatarColor?: string
  avatarGroup: ProfileAvatarGroup[]
}

export type TeamsTab = {
  title: string
  avatar: string
  description: string
  extraMembers: number
  chips: ProfileChip[]
  avatarGroup: ProfileAvatarGroup[]
}

export type ProfileTab = {
  teams: ProfileTeams[]
  about: ProfileTabCommon[]
  contacts: ProfileTabCommon[]
  overview: ProfileTabCommon[]
  teamsTech: ProfileTeamsTech[]
  connections: ProfileConnections[]
}

// SECTION
// 👉 JWT

export interface User {
  id: number
  fullName?: string
  username: string
  password: string
  avatar?: string
  email: string
  role: string
  abilities: UserAbility[]
}

export interface UserOut {
  abilities: User['abilities']
  accessToken: string
  userData: Omit<User, 'abilities' | 'password'>
}

export interface LoginResponse {
  accessToken: string
  userData: AuthUserOut
  abilities: UserAbility[]
}

export interface RegisterResponse {
  accessToken: string
  userData: AuthUserOut
  abilities: UserAbility[]
}

// !SECTION

// SECTION
// App: User
export interface baseProperties {
    id: number
    created_at: datetime
}
export interface CommonProperties extends baseProperties {
    brand_id: number
    group_id: number
    user_name: string
    // user_pw: string
    nick_name: string
    birth_date: date
}
export interface PrivacyProperties extends CommonProperties {
    addr: string
    phone_num: string
    email: string
    resident_num:string
    business_num:string
    sector:string
    passbook_img:string
    id_img:string
    passbook_img:string
    contract_img:string
    bsin_lic_img:string
    acct_num:string
    acct_nm:string
    acct_bank_nm:string
    acct_bank_cd:string
}
export interface UserProperties extends CommonProperties {
    login_type: number
    level: number
}
export interface MerchandiseProperties extends PrivacyProperties {
    mcht_name: string
    abnormal_trans_limit:number
    pay_day_limit:number
    pay_year_limit:number
    use_dupe_trx:boolean
    is_show_fee:boolean
}
export interface SalesForceProperties extends PrivacyProperties {
    tax_type:number
    trx_fee:float
}
// !SECTION

// App: Payment Modules
export interface PaymentModuleProperties {
    id:number
    brand_id:number
    mcht_id:number
    pg_sec_id:number
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
    begin_date:date
    ship_out_date:date
    ship_out_stat:number
    note:string
    pay_limit:number
    use_hand_pay_old:boolean
    use_saleslip_prov:boolean
    use_saleslip_sell:boolean
    installment_limit:number
}

// SECTION App: Calendar
export interface CalendarEvent {
  id: string
  url: string
  title: string
  start: string
  end: string
  allDay: boolean
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  extendedProps: Record<string, any>
}
// !SECTION

// SECTION App: Invoice

// 👉 Client
export interface Client {
  address: string
  company: string
  companyEmail: string
  country: string
  contact: string
  name: string
}

// 👉 Invoice
export interface Invoice {
    id: number,
    issuedDate: string
    client: Client
    service: string
    total: number
    avatar: string
    invoiceStatus: string
    balance: string | number
    dueDate: string
}

// 👉 PaymentDetails
export interface PaymentDetails {
  totalDue: string
  bankName: string
  country: string,
  iban: string,
  swiftCode: string,
}

// !SECTION App: Invoice

// SECTION App: Email

export type EmailFolder = 'inbox' | 'sent' | 'draft' | 'spam'
export type EmailFilter = EmailFolder | 'trashed' | 'starred'
export type EmailLabel = 'personal' | 'company' | 'important' | 'private'

export interface EmailTo {
  email: string
  name: string
}

export interface EmailFrom {
  email: string
  name: string
  avatar: any
}

export interface EmailAttachment {
  fileName: string
  thumbnail: any
  url: string
  size: string
}

/*
  - You can have draft mail in your inbox
    - We can have flag isDraft for mail
  - You can't move sent mail to inbox
  - You can move sent mail to inbox

  --- above are gmail notes

  - We will provide inbox, spam & sent as folders
    - You can't move any mail in sent folder. Sent mail can be deleted or retrieved back
  - We will provide isDraft, isSpam, isTrash as flags
  - draft is flag
  - trash is flag
  - spam email can be moved to inbox only
  - We will provide isDeleted flag

  === this is too confusing 😔

  // this is final now 💯
  folders => inbox, sent, draft, spam
  flags: starred, trash
*/
export interface Email {
  id: number
  to: EmailTo[]
  from: EmailFrom
  subject: string
  cc: string[]
  bcc: string[]
  message: string
  attachments: EmailAttachment[]
  time: string
  replies: Email[]

  labels: EmailLabel[]

  folder: EmailFolder

  // Flags 🚩
  isRead: boolean
  isStarred: boolean
  isDeleted: boolean
}

export interface FetchEmailsPayload {
  q?: string
  filter?: EmailFilter
  label?: EmailLabel
}

// !SECTION Apps: Email

// SECTION App: Chat
export type ChatStatus = 'online' | 'offline' | 'busy' | 'away'

export interface ChatContact {
  id: number
  fullName: string
  role: string
  about: string
  avatar: string
  status: ChatStatus
}

export interface ChatMessage {
  message: string
  time: string
  senderId: number
  feedback: {
    isSent: boolean
    isDelivered: boolean
    isSeen: boolean
  }
}

export interface Chat {
  id: number
  userId: number
  unseenMsgs: number
  messages: ChatMessage[]
}

// ℹ️ This is chat type received in response of user chat
export interface ChatOut {
  id: Chat['id']
  unseenMsgs: Chat['unseenMsgs']
  messages: ChatMessage[]
  lastMessage: ChatMessage[number]
}

export interface ChatContactWithChat extends ChatContact {
  chat: ChatOut
}
// !SECTION App: Chat


// 👉 Template Search
//----------------
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

// SECTION Dashboard: Analytics
export interface ProjectsAnalytics {
 logo: string
  name: string
  date: string
  leader: string
  team: string[]
  status: number
}

// !SECTION
