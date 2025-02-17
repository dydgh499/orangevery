<script setup lang="ts">
import corp from '@corp'

const visible = ref(false)
const errors = ref(<any>([]))

const show = () => {
    visible.value = true
}
const getErrorContent = (code: string, message: string, reason: string, resolve: string) => {
    const error_prefix = import.meta.env.VITE_ERROR_PREFIX
    return {
        id: error_prefix + code,
        message: message,
        reason: reason,
        resolve: resolve
    }
}

const setErrorCode = () => {
    errors.value = []
    if(corp.pv_options.paid.use_online_pay)
        errors.value.push(getErrorContent('401', 'pay key가 존재하지 않습니다.' ,'', ''))

    errors.value.push(...[
        getErrorContent('403', '잘못된 접근입니다.', '이상접근', ''),
        getErrorContent('405', '지원하지 않는 PG사입니다.', '이상접근', ''),
        getErrorContent('406', '가맹점을 찾을 수 없습니다.', '결제모듈 삭제 또는 정보 수정 후 기존 결제링크 사용시 발생<br><b class="text-error">해당 에러는 결제실패 관리 탭에서 확인할 수 없습니다.</b>', '결제링크 재생성'),
        getErrorContent('407', '취소 가능시간이 초과되어 취소가 불가합니다.', '취소 불가능 시간대에 취소시도', '해당 결제모듈에서 취소가능시간 수정'),
        getErrorContent('408', '결제한도를 초과하였습니다. (총 시도액: 000원)', '일,월,연 결제한도 초과', '해당 결제모듈에서 결제한도 수정'),
        getErrorContent('409', '지금은 결제할 수 있는 시간이 아닙니다.', '결제 불가능 시간대에 결제시도', '해당 결제모듈에서 결제가능시간 수정'),
    ])
    if(corp.pv_options.paid.use_dup_pay_validation)
        errors.value.push(getErrorContent('411', '동일카드는 하루에 최대 n번만 결제가 가능합니다.', '동일카드 ', '해당 결제모듈에서 동일카드 결제허용 회수 수정'))
    errors.value.push(getErrorContent('412', '사용불가 가맹점 입니다.', '사용불가 가맹점', '해당 가맹점에서 가맹점 상태 수정'))

    errors.value.push(...[
        getErrorContent('413', '결제 정보가 잘못되었습니다. 상위 PG사에 문의하세요.', '', '결제정보 재확인'),
        getErrorContent('414', '카드비밀번호 또는 생년월일(사업자번호)를 입력해주세요.', '구인증 수기결제에서 생년월일 또는 비밀번호 누락', '생년월일 또는 비밀번호 확인'),
        getErrorContent('415', '결제금액이 너무 낮습니다.', '', '결제금액 확인'),
    ])

    if(corp.pv_options.paid.use_regular_card)
        errors.value.push(getErrorContent('416', '등록된 카드가 아니므로 결제할 수 없습니다.', '', '해당 가맹점에서 단골고객 카드등록'))
    if(corp.pv_options.paid.use_collect_withdraw)
        errors.value.push(getErrorContent('417', '출금 가능 금액보다 취소 금액이 더 큽니다.', '', ''))
    
    errors.value.push(...[
        getErrorContent('418', '결제 키(api key) 값이 이상합니다. 결제 키를 확인하신 후 시도해주세요.', '', '해당 결제모듈에서 API KEY 재확인'),
        getErrorContent('419', '결제 키(sub key) 값이 이상합니다. 결제 키를 확인하신 후 시도해주세요.', '', '해당 결제모듈에서 SUB KEY 재확인'),
        getErrorContent('420', '이용할 수 없는 카드사입니다.', '', '해당 결제모듈에서 카드사 필터 수정'),
        getErrorContent('421', '중복결제 방지를 위해 n시부터 결제 가능합니다.', '', '해당 결제모듈에서 결제 허용 간격 수정'),
        getErrorContent('422', '파라미터 누락', '', '파라미터 재확인'),
    ])
    if(corp.pv_options.paid.use_mcht_blacklist) 
        errors.value.push(getErrorContent('423', '이용할 수 없는 카드입니다.', '가맹점 블랙리스트에 등록된 카드정보', ''))
     
    
    if(corp.pv_options.paid.use_specified_limit) {
        errors.value.push(...[
            getErrorContent('424', '지금은 결제할 수 없습니다.', '지정결제제한시간대에 결제시도', '해당 가맹점 지정결제제한시간 수정'),
            getErrorContent('425', '단건 결제한도를 초과하였습니다.', '단건 결제한도 하향 시간대에 이상금액 결제시도', '해당 가맹점 단건 결제한도 하향금 수정'),
        ])
    }

    errors.value.push(...[
        getErrorContent('426', '계약기간이 만료되었습니다.', '', '결제모듈 정보 -> 계약 종료일 연장'),
        getErrorContent('427', '시스템 점검시간입니다.(06:00 ~ 06:05)', '점검시간대에 결제 시도', '06:05 이후 결제가능'),
        getErrorContent('428', '오늘은 결제할 수 없습니다.', '결제금지타입 검증사항', '결제금지타입 수정'),
    ])

    if(corp.pv_options.paid.use_realtime_deposit) {
        errors.value.push(...[
            getErrorContent('450', '개인정보 또는 실시간 모듈 정보가 매칭되지 않았습니다.', '실시간 이체 시 개인정보 누락', '해당 가맹점에서 은행정보 기입'),
            getErrorContent('451', '이체 예약취소에 실패한 거래건들이 존재합니다.', '이미 이체 예약 취소한 거래건', ''),
        ])
    }
    if(corp.pv_options.paid.use_bill_key)  {
        errors.value.push(...[
            getErrorContent('452', '빌키사용이 불가한 결제모듈입니다.', '', '결제모듈 타입을 빌키결제로 수정'),
            getErrorContent('470', '빌키가 존재하지 않습니다.', '빌키 조회 실패', '요청 MID 및 bill key 정보 확인'),
        ])
    }
    errors.value.push(...[
        getErrorContent('499', '해당 기능은 사용할 수 없습니다.', '접근 금지 기능', ''),
        getErrorContent('500', 'PG사와 통신과정에서 문제가 발생했습니다.', '상위 PG사 에러사항', ''),
        {id: '이외 에러코드', message: '', reason: '상위 PG사에 문의', resolve: '상위 PG사에 문의'},
    ])
}

onMounted(() => {
    setErrorCode()
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="1300">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="결제/취소 에러코드">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>에러코드</th>
                            <th class='list-square'>에러 메세지</th>
                            <th class='list-square'>에러 사유</th>
                            <th class='list-square'>수정 방법</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(error, key) in errors" :key="key">
                            <td class='list-square'><b v-html="error.id"></b></td>
                            <td class='list-square'><span v-html="error.message"></span></td>
                            <td class='list-square'><span v-html="error.reason"></span></td>
                            <td class='list-square'><span v-html="error.resolve"></span></td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
