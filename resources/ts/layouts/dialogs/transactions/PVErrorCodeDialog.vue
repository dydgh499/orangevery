<script setup lang="ts">
import corp from '@corp'

const visible = ref(false)
const errors = ref(<any>([]))

const show = () => {
    visible.value = true
}

const setErrorCode = () => {
    errors.value = []
    if(corp.pv_options.paid.use_online_pay)
        errors.value.push({id: 'PV401', message: 'pay key가 존재하지 않습니다.'})

    errors.value.push(...[
        {id: 'PV403', message: '잘못된 접근입니다.', reason: '이상접근', resolve: ''},
        {id: 'PV405', message: '지원하지 않는 PG사입니다.', reason: '이상접근', resolve: ''},
        {id: 'PV406', message: '가맹점을 찾을 수 없습니다.', reason: '결제모듈 삭제 또는 정보 수정 후 기존 결제링크 사용시 발생', resolve: '결제링크 재생성'},
        {id: 'PV407', message: '취소 가능시간이 초과되어 취소가 불가합니다.', reason: '취소 불가능 시간대에 취소시도', resolve: '해당 결제모듈에서 취소가능시간 수정'},
        {id: 'PV408', message: '결제한도를 초과하였습니다. (총 시도액: 000원)', reason: '일,월,연 결제한도 초과', resolve: '해당 결제모듈에서 결제한도 수정'},
        {id: 'PV409', message: '지금은 결제할 수 있는 시간이 아닙니다.', reason: '결제 불가능 시간대에 결제시도', resolve: '해당 결제모듈에서 결제가능시간 수정'},
    ])
    if(corp.pv_options.paid.use_dup_pay_validation)
        errors.value.push({id: 'PV411', message: '동일카드는 하루에 최대 n번만 결제가 가능합니다.', reason: '동일카드 ', resolve: '해당 결제모듈에서 동일카드 결제허용 회수 수정'})
    if(corp.pv_options.paid.subsidiary_use_control)
        errors.value.push({id: 'PV412', message: '사용불가 가맹점 입니다.', reason: '사용불가 가맹점', resolve: '해당 가맹점에서 전산 사용상태 수정'})

    errors.value.push(...[
        {id: 'PV413', message: '결제 정보가 잘못되었습니다. 상위 PG사에 문의하세요.', reason: '', resolve: '결제정보 재확인'},
        {id: 'PV414', message: '카드비밀번호 또는 생년월일(사업자번호)를 입력해주세요.', reason: '구인증 수기결제에서 생년월일 또는 비밀번호 누락', resolve: '생년월일 또는 비밀번호 확인'},
        {id: 'PV415', message: '결제금액이 너무 낮습니다.', reason: '', resolve: '결제금액 확인'},
    ])

    if(corp.pv_options.paid.use_regular_card)
        errors.value.push({id: 'PV416', message: '등록된 카드가 아니므로 결제할 수 없습니다.', reason: '', resolve: '해당 가맹점에서 단골고객 카드등록'})
    if(corp.pv_options.paid.use_collect_withdraw)
        errors.value.push({id: 'PV417', message: '출금 가능 금액보다 취소 금액이 더 큽니다.', reason: '', resolve: ''})
    
    errors.value.push(...[
        {id: 'PV418', message: '결제 키(api key) 값이 이상합니다. 결제 키를 확인하신 후 시도해주세요.', reason: '', resolve: '해당 결제모듈에서 API KEY 재확인'},
        {id: 'PV419', message: '결제 키(sub key) 값이 이상합니다. 결제 키를 확인하신 후 시도해주세요.', reason: '', resolve: '해당 결제모듈에서 SUB KEY 재확인'},
        {id: 'PV420', message: '이용할 수 없는 카드사입니다.', reason: '', resolve: '해당 결제모듈에서 카드사 필터 수정'},
        {id: 'PV421', message: '중복결제 방지를 위해 n시부터 결제 가능합니다.', reason: '', resolve: '해당 결제모듈에서 결제 허용 간격 수정'},
        {id: 'PV422', message: '파라미터 누락', reason: '', resolve: '파라미터 재확안'},
    ])
    if(corp.pv_options.paid.use_mcht_blacklist) 
        errors.value.push({id: 'PV423', message: '이용할 수 없는 카드입니다.', reason: '가맹점 블랙리스트에 등록된 카드정보', resolve: ''})
     
    
    if(corp.pv_options.paid.use_specified_limit) {
        errors.value.push(...[
            {id: 'PV424', message: '지금은 결제할 수 없습니다.', reason: '지정시간 결제제한시간대에 결제시도', resolve: '해당 가맹점 지정시간 결제제한시간 수정'},
            {id: 'PV425', message: '단건 결제한도를 초과하였습니다.', reason: '단건 결제한도 하향 시간대에 이상금액 결제시도', resolve: '해당 가맹점 단건 결제한도 하향금 수정'},
        ])
    }

    if(corp.pv_options.paid.use_realtime_deposit) {
        errors.value.push(...[
            {id: 'PV450', message: '개인정보 또는 실시간 모듈 정보가 매칭되지 않았습니다.', reason: '실시간 이체 시 개인정보 누락', resolve: '해당 가맹점에서 은행정보 기입'},
            {id: 'PV451', message: '이체 예약취소에 실패한 거래건들이 존재합니다.', reason: '이미 이체 예약 취소한 거래건', resolve: ''},
        ])
    }
    errors.value.push(...[
        {id: 'PV499', message: '해당 기능은 사용할 수 없습니다.', reason: '접근 금지 기능', resolve: ''},
        {id: 'PV500', message: 'PG사와 통신과정에서 문제가 발생했습니다.', reason: '상위 PG사 에러사항', resolve: ''},
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
    <VDialog v-model="visible">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="PV 전산내 에러코드">
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
                            <td class='list-square'><b>{{ error.id }}</b></td>
                            <td class='list-square'>{{ error.message }}</td>
                            <td class='list-square'>{{ error.reason }}</td>
                            <td class='list-square'>{{ error.resolve }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
