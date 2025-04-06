import { getUserLevel, user_info } from '@/plugins/axios'
import corp from '@corp'
import { lengthValidator, passwordValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators'
import { Options, UserPropertie } from '../types'

export const banks = [
    { code: "001", title: "한국은행" }, { code: "002", title: "산업은행" }, { code: "003", title: "기업은행" },
    { code: "004", title: "국민은행" }, { code: "005", title: "외환은행" }, { code: "007", title: "수협은행" },
    { code: "008", title: "수출입은행" }, { code: "011", title: "농협은행" }, { code: "012", title: "농협회원조합" },
    { code: "020", title: "우리은행" }, { code: "023", title: "SC제일은행" }, { code: "026", title: "서울은행" },
    { code: "027", title: "한국씨티은행" }, { code: "031", title: "대구은행" }, { code: "032", title: "부산은행" },
    { code: "034", title: "광주은행" }, { code: "035", title: "제주은행" }, { code: "037", title: "전북은행" },
    { code: "039", title: "경남은행" }, { code: "045", title: "새마을금고연합회" }, { code: "048", title: "신협중앙회" },
    { code: "050", title: "상호저축은행" }, { code: "051", title: "기타 외국계은행" }, { code: "052", title: "모건스탠리은행" },
    { code: "054", title: "HSBC은행" }, { code: "055", title: "도이치은행" }, { code: "056", title: "알비에스피엘씨은행" },
    { code: "057", title: "제이피모간체이스은행" }, { code: "058", title: "미즈호코퍼레이트은행" }, { code: "059", title: "미쓰비시도쿄UFJ은행" },
    { code: "060", title: "BOA" }, { code: "061", title: "비엔피파리바은행" }, { code: "062", title: "중국공상은행" },
    { code: "063", title: "중국은행" }, { code: "064", title: "산림조합" }, { code: "065", title: "대화은행" },
    { code: "071", title: "우체국" }, { code: "076", title: "신용보증기금" }, { code: "077", title: "기술신용보증기금" },
    { code: "081", title: "하나은행" }, { code: "088", title: "신한은행" }, { code: "089", title: "케이뱅크" },
    { code: "090", title: "카카오뱅크" }, { code: "092", title: "토스뱅크" },  { code: "094", title: "서울보증보험" }, 
    { code: "101", title: " 한국신용정보원"}, { code: "103", title: "SBI저축은행"}, { code: "105", title: "웰컴저축은행"},
    // 임시
    { code: "190", title: "융창저축은행"},
    { code: "191", title: "청주저축은행"},
    { code: "192", title: "금화저축은행"},
    { code: "193", title: "저축은행"},
    //
    { code:"209", title: "유안타증권" }, { code:"218", title: "KB증권" }, { code:"221", title: "상상인증권" }, { code:"222", title: "한양증권" },
    { code:"223", title: "리딩투자증권" },{ code:"224", title: "BNK투자증권" },{ code:"225", title: "IBK투자증권" }, { code:"227", title: "다올투자증권" },
    { code:"238", title: "미래에셋증권" },{ code:"240", title: "삼성증권" }, { code:"243", title: "한국투자증권" }, { code:"247", title: "NH투자증권" },
    { code:"261", title: "교보증권" }, { code:"262", title: "하이투자증권" }, { code:"263", title: "현대차증권" }, { code:"264", title: "키움증권" },
    { code:"265", title: "이베스트투자증권" }, { code:"266", title: "SK증권" }, { code:"267", title: "대신증권" }, { code:"269", title: "한화투자증권" },
    { code:"270", title: "하나증권" }, { code:"271", title: "토스증권" }, { code:"272", title: "NH선물" }, { code:"273", title: "코리아에셋투자증권" },
    { code:"274", title: "DS투자증권" }, { code:"275", title: "흥국증권" }, { code:"278", title: "신한투자증권" }, { code:"279", title: "DB금융투자" },
    { code:"280", title: "유진투자증권" }, { code:"287", title: "메리츠증권" }, { code:"288", title: "카카오페이증권" }, { code:"290", title: "부국증권" },
    { code:"291", title: "신영증권" }, { code:"292", title: "케이프투자증권" }, { code:"293", title: "한국증권금융" }, { code:"294", title: "한국포스증권" }, 
    { code:"295", title: "우리종합금융" }, { code:"299", title: "우리금융캐피탈" }, 
    { code:"301", title: "DGB캐피탈" }, { code:"302", title: "BNK캐피탈" }, { code:"303", title: "롯데캐피탈" }, { code:"304", title: "현대캐피탈" }, 
    { code:"305", title: "JB우리캐피탈" }, { code:"306", title: "NH농협캐피탈" }, { code:"307", title: "애큐온캐피탈" }, { code:"308", title: "KB캐피탈" }, 
    { code:"309", title: "한국캐피탈" }, { code:"310", title: "하나캐피탈" }, { code:"311", title: "미래에셋캐피탈" }, { code:"312", title: "오케이캐피탈" },
    { code:"313", title: "한국투자캐피탈" },
    { code:"361", title: "BC카드" }, { code:"364", title: "광주카드" }, { code:"365", title: "삼성카드" }, { code:"366", title: "신한카드" }, 
    { code:"367", title: "현대카드" }, { code:"368", title: "롯데카드" }, { code:"369", title: "수협카드" }, { code:"370", title: "씨티카드" }, 
    { code:"371", title: "NH농협카드" }, { code:"372", title: "전북카드" }, { code:"373", title: "제주카드" }, { code:"374", title: "하나카드" },
    { code:"381", title: "국민카드" }, { code:"401", title: "하나생명보험" }, { code:"402", title: "동양생명" }, { code:"403", title: "흥국화재" },
    { code:"404", title: "BNP파리바" }, { code:"405", title: "AIG손해보험" }, { code:"406", title: "하나손해보험" }, { code:"431", title: "미래에셋생명" },
    { code:"432", title: "한화생명" }, { code:"433", title: "교보라이프플래닛생명" }, { code:"434", title: "푸본현대생명" }, { code:"435", title: "라이나생명" },
    { code:"436", title: "교보생명" }, { code:"437", title: "에이비엘생명" }, { code:"438", title: "신한라이프생명보험" }, { code:"439", title: "KB생명보험" },
    { code:"440", title: "NH농협생명" }, { code:"441", title: "삼성화재" }, { code:"442", title: "현대해상" }, { code:"443", title: "DB손해보험" },
    { code:"444", title: "KB손해보험" }, { code:"445", title: "롯데손해보험" }, { code:"447", title: "악사손해보험" }, { code:"448", title: "메리츠화재" },
    { code:"449", title: "NH농협손해보험" }, { code:"450", title: "푸르덴셜생명" }, { code:"452", title: "삼성생명" }, { code:"453", title: "흥국생명" },
    { code:"454", title: "한화손해보험" }, { code:"455", title: "AIA생명" }, { code:"456", title: "DGB생명보험" }, { code:"457", title: "DB생명보험" },
    { code:"458", title: "KDB생명보험" }, { code:"459", title: "에이스아메리칸화재해상보험" }, { code:"460", title: "처브라이프생명보험" }, { code:"494", title: "한국자산관리공사" },
]
export const avatars = [
    '/utils/avatars/1.svg',
    '/utils/avatars/2.svg',
    '/utils/avatars/3.svg',
    '/utils/avatars/4.svg',
    '/utils/avatars/5.svg',
    '/utils/avatars/6.svg',
    '/utils/avatars/7.svg',
    '/utils/avatars/8.svg',
    '/utils/avatars/9.svg',
    '/utils/avatars/10.svg',
    '/utils/avatars/11.svg',
    '/utils/avatars/12.svg',
    '/utils/avatars/13.svg',
    '/utils/avatars/14.svg',
    '/utils/avatars/15.svg',
    '/utils/avatars/16.svg',
    '/utils/avatars/17.svg',
    '/utils/avatars/18.svg',
    '/utils/avatars/19.svg',
    '/utils/avatars/20.svg',
    '/utils/avatars/21.svg',
    '/utils/avatars/22.svg',
    '/utils/avatars/23.svg',
    '/utils/avatars/24.svg',
    '/utils/avatars/25.svg',
]

export const business_types = <Options[]>([
    {id:0, title:'개인사업자'}, {id:1, title:'법인사업자'}, {id:2, title:'비사업자'}
])

export const getUserTypeName = (type: number) => {
    if(type === 0)
        return ['가맹점', 'merchandises']
    else if(type === 1)
        return ['영업자', 'salesforces']
    else if(type === 2)
        return ['운영자', 'services/operators']
    else if(type === 3)
        return ['GMID', 'gmids']
    else
        return ['', '']
}

export const getUserIdValidate = (user_type: number, user_name: string) => {
    if(user_type === 0 || user_type === 1) {
        let id_level = user_type === 0 ? 'mcht_id_level' : 'sales_id_level';
        if(corp.pv_options.free.secure[id_level] === 0)
            return [requiredValidatorV2(user_name, '아이디')]
        else if(corp.pv_options.free.secure[id_level] === 1)
            return [requiredValidatorV2(user_name, '아이디'), lengthValidator(user_name, 8)]
    }
    else
        return [requiredValidatorV2(user_name, '아이디'), lengthValidator(user_name, 8)]
}

export const getUserPasswordValidate = (user_type: number, password: string) => {
    if(user_type === 0 || user_type === 1) {
        let pw_level = user_type === 0 ? 'mcht_pw_level' : 'sales_pw_level';
        if(corp.pv_options.free.secure[pw_level] === 0)
            return [requiredValidatorV2(password, '패스워드')]
        else if(corp.pv_options.free.secure[pw_level] === 1)
            return [requiredValidatorV2(password, '패스워드'), lengthValidator(password, 8)]
        else if(corp.pv_options.free.secure[pw_level] === 2)
            return [requiredValidatorV2(password, '패스워드'), passwordValidator]
    }
    else
        return [requiredValidatorV2(password, '새 패스워드'), passwordValidatorV2]
}

export const getOnlyNumber = (value: string) => {
    return value.replace(/\D/g, '').trim()
}

export const getRegidentNum = (item: UserPropertie, is_mcht: boolean) => {
    const brandMode2Method = () => {
        if(getUserLevel() >= 30) 
            return `${item.resident_num_front} - ${item.resident_num_back}`
        else {
            if(is_mcht && getUserLevel() === 10 && item.id === user_info.value.id)
                return `${item.resident_num_front} - ${item.resident_num_back}`
            else if(is_mcht === false && getUserLevel() > 10 && item.id === user_info.value.id)
                return `${item.resident_num_front} - ${item.resident_num_back}`
            else
                return `${item.resident_num_front} - *******`
        }
    }
    if(corp.pv_options.free.resident_num_masking) {
        if(corp.pv_options.paid.brand_mode === 2) 
            return brandMode2Method()
        else
            return `${item.resident_num_front} - *******`
    }
    else
        return `${item.resident_num_front} - ${item.resident_num_back}`
}
