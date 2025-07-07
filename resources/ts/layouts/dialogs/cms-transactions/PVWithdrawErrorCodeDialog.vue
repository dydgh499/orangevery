<script setup lang="ts">
import { useStore } from '@/views/services/options/useStore'
import { getUserLevel } from '@axios'
import { coocon_error_codes } from './finance-vans-error-codes/coocon'
import { hecto_error_codes } from './finance-vans-error-codes/hecto'
import VanWithdrawErrorCodeDialog from './finance-vans-error-codes/VanWithdrawErrorCodeDialog.vue'


const visible = ref(false)
const errors = ref(<any>([]))
const vanWithdrawErrorCodeDialog = ref()
const { finance_companies, finance_vans } = useStore()

const show = () => {
    visible.value = true
}
const getErrorContent = (code: string, message: string, reason: string, resolve: string) => {
    const error_prefix = import.meta.env.VITE_ERROR_PREFIX
    return {
        id: error_prefix + code,
        message: message,
        reason: reason,
        resolve: resolve,
    }
}

const setErrorCode = () => {
    errors.value = [
	    getErrorContent('427', '시스템 점검시간입니다.(06:00 ~ 06:05)', '점검시간대에 출금시도', '06:05 이후 출금가능'),
    ]
    errors.value.push(...[
        getErrorContent('481', '이미 입금처리가 된 건입니다.', '이미처리된 단일거래건에 대해서 중복 출금시도', ''),
        getErrorContent('482', '타임아웃 발생건', '타임아웃된 단일거래건에대해서 중복 출금시도', '시스템 내부적으로 내역 재조회'),
        getErrorContent('485', '은행코드를 매칭할 수 없습니다.', '금융 VAN사에서 수취은행을 지원하지 않을 경우', '수취계좌변경 또는 관리자 문의'),
        getErrorContent('499', '해당 기능은 사용할 수 없습니다.', '접근 금지 기능', ''),
        {id: '이외 에러코드', message: '', reason: '금융 VAN사에 문의', resolve: '금융 VAN사에 문의'},
    ])
}

const getFindFianceVan = (finance_company_num: number) => {
    return finance_vans.find(obj => obj.finance_company_num === finance_company_num) ? true : false
}

const getFindFianceVanErrors = (finance_company_num: number) => {
    if(finance_company_num === 1)
        return coocon_error_codes
    else if(finance_company_num === 2)
        return hecto_error_codes
    else
        return []
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
        <DialogCloseBtn @click="visible = false" />
        <VCard title="">
            <VCardTitle>
                <div style="display: flex;align-items: center;justify-content: space-between;">
                    <span style="margin-left: 1em;">출금 에러코드</span>
                    <div :style="$vuetify.display.smAndDown ? 'display: inline-flex;flex-direction: column;' : 'display: inline-flex;'">
                        <template v-if="getUserLevel() >= 30">
                            <template v-for="(finance_company, key) in finance_companies">
                                <VBtn v-if="getFindFianceVan(finance_company.id as number)"
                                    @click="vanWithdrawErrorCodeDialog.show(getFindFianceVanErrors(finance_company.id as number))"
                                    style='margin: 0.25em;' variant="tonal" size="small">
                                    {{ finance_company.title}} 에러코드
                                </VBtn>
                            </template>
                        </template>
                    </div>
                </div>
            </VCardTitle>
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
    <VanWithdrawErrorCodeDialog ref="vanWithdrawErrorCodeDialog"/>
</template>
