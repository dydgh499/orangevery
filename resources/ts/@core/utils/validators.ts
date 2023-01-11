import { isEmpty, isEmptyArray, isNullOrUndefined } from './index'

// 👉 Required Validator
export const requiredValidator = (value: unknown) => {
  if (isNullOrUndefined(value) || isEmptyArray(value) || value === false)
    return '이 필드는 필수로 입력이 요구됩니다.'

  return !!String(value).trim().length || '이 필드는 필수로 입력이 요구됩니다.'
}

// 👉 Email Validator
export const emailValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

  if (Array.isArray(value))
    return value.every(val => re.test(String(val))) || '이메일 필드는 유효한 이메일이어야 합니다.'

  return re.test(String(value)) || '이메일 필드는 유효한 이메일이어야 합니다.'
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

// 👉 Confirm Password Validator
export const confirmedValidator = (value: string, target: string) =>

  value === target || '비밀번호 및 비밀번호가 일치하지 않습니다.'

// 👉 Between Validator
export const betweenValidator = (value: unknown, min: number, max: number) => {
  const valueAsNumber = Number(value)

  return (Number(min) <= valueAsNumber && Number(max) >= valueAsNumber) || `Enter number between ${min} and ${max}`
}

// 👉 Integer Validator
export const integerValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  if (Array.isArray(value))
    return value.every(val => /^-?[0-9]+$/.test(String(val))) || '이 필드는 정수여야 합니다.'

  return /^-?[0-9]+$/.test(String(value)) || '이 필드는 정수여야 합니다.'
}

// 👉 Regex Validator
export const regexValidator = (value: unknown, regex: RegExp | string): string | boolean => {
  if (isEmpty(value))
    return true

  let regeX = regex
  if (typeof regeX === 'string')
    regeX = new RegExp(regeX)

  if (Array.isArray(value))
    return value.every(val => regexValidator(val, regeX))

  return regeX.test(String(value)) || '정규식 필드 형식이 잘못되었습니다.'
}

// 👉 Alpha Validator
export const alphaValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  return /^[A-Z]*$/i.test(String(value)) || 'Alpha 필드는 알파벳 문자만 포함할 수 있습니다.'
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

  return String(value).length === length || `최소 문자 필드는 ${length}자 이상이어야 합니다.`
}
// 👉 Length Validator
export const lengthValidatorV2 = (value: unknown, length: number) => {
  if (isEmpty(value))
    return true

  return String(value).length === length || `${length}자 이어야 합니다.`
}
// 👉 Alpha-dash Validator
export const alphaDashValidator = (value: unknown) => {
  if (isEmpty(value))
    return true

  const valueAsString = String(value)

  return /^[0-9A-Z_-]*$/i.test(valueAsString) || '모든 문자가 유효하지 않습니다.'
}

// custom
export const businessNumValidator = (value: unknown) => {
  return /^[0-9]{3}[0-9]{2}[0-9]{5}$/.test(String(value)) || '유효한 사업자 번호를 입력하세요.'
}
