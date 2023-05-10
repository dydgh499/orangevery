export interface UserParams {
  q: string,
  role: string,
  plan: string,
  status: string,
  perPage: number,
  currentPage: number,
}

export interface IUserCreate {
  level   : number,
  email   : string,
  password: string,
  nickName: string,
  address : string,
  feesRate: number,
  //--    
  mobile: number,
  businessNum: string,
  residentNum: string,
  //--    
  acctNum : number,
  acctNm  : number,
  bank    : number,
  bankbook: File[],
  idCard  : File[],
  contact : File[],
}

export interface IUserModel extends IUserCreate {
  id: number,
}
