import { isEmpty, isEmptyArray, isNullOrUndefined } from './index';

const checkDirectObject = (name: string) => {
    //name의 마지막 음절의 유니코드(UTF-16) 
    const charCode = name.charCodeAt(name.length - 1);    
    //유니코드의 한글 범위 내에서 해당 코드의 받침 확인
    const consonantCode = (charCode - 44032) % 28;    
    if(consonantCode === 0){
        //0이면 받침 없음 -> 를
        return `${name}를`;
    }
    //1이상이면 받침 있음 -> 을
    return `${name}을`;
}

export const requiredValidatorV2 = (value: unknown, name:string) => {
    const message = checkDirectObject(name)+' 입력해주세요.'
    if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
        return message
    return !!String(value).trim().length || message
}
// 👉 Email Validator
export const emailValidator = (value: unknown) => {
    if (isEmpty(value))
        return true

    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

    if (Array.isArray(value))
        return value.every(val => re.test(String(val))) || '올바른 이메일을 입력해주세요.'

    return re.test(String(value)) || '올바른 이메일을 입력해주세요.'
}

// 👉 Password Validator
export const passwordValidator = (password: string) => {
    const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/

    const validPassword = regExp.test(password)

    return (
        // eslint-disable-next-line operator-linebreak
        validPassword ||
        '최소 8자의 대문자, 소문자, 특수 문자 및 숫자가 하나 이상 포함되어야 합니다.'
    )
}

// 👉 Password Validator
export const passwordValidatorV2 = (password: string) => {
    const regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{10,}/

    const validPassword = regExp.test(password)

    return (
        // eslint-disable-next-line operator-linebreak
        validPassword ||
        '최소 10자의 대문자, 소문자, 특수 문자 및 숫자가 하나 이상 포함되어야 합니다.'
    )
}

// 👉 Confirm Password Validator
export const confirmedValidator = (value: string, target: string) =>

    value === target || '비밀번호 및 비밀번호가 일치하지 않습니다.'

// 👉 Between Validator
export const betweenValidator = (value: unknown, min: number, max: number) => {
    const valueAsNumber = Number(value)

    return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
}

// 👉 URL Validator
export const urlValidator = (value: unknown) => {
    if (isEmpty(value))
        return true

    const re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/

    return re.test(String(value)) || 'URL이 잘못되었습니다.'
}

// 👉 Length Validator
export const lengthValidator = (value: unknown, length: number) => {
    if (isEmpty(value))
        return true

    return String(value).length >= length || `최소 ${length}자 이상이어야 합니다.`
}
// 👉 Length Validator
export const lengthValidatorV2 = (value: unknown, length: number) => {
    if (isEmpty(value))
        return true
    return String(value).length === length || `${length}자 이어야 합니다.`
}

// custom
export const businessNumValidator = (value: string) => {
    return (/^[0-9]{10}$/.test(value)) || '올바른 사업자등록번호를 입력하세요.'
}

export const extensionValidator = (files: File[], values: string[]) => {
    if (files.length == 0)
        return true
    else {
        const file = files[0];
        const fileExtension = file.name.split('.').pop()?.toLowerCase() || '';
        const isValid = values.includes(fileExtension);
        return isValid ? true : `확장자는 ${values.join(',')}만 등록 가능합니다.`;    
    }
}

export const amountValidator = (value: number, name:string, amount: number) => {
    return value > amount || checkDirectObject(name)+` 최소 ${amount}이상이어야합니다.` 
}
