<script lang="ts" setup>
import MidCreateDialog from '@/layouts/dialogs/pay-modules/MidCreateDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { comm_settle_types, cxl_types, fin_trx_delays, installments, module_types, pay_window_secure_levels, under_sales_types, useSearchStore, withdraw_limit_types } from '@/views/merchandises/pay-modules/useStore'
import { Registration } from '@/views/registration'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { keyCreater, useRegisterStore, validateItems } from '@/views/services/bulk-register/PayModRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options, PayModule } from '@/views/types'
import { axios, salesLevels } from '@axios'
import corp from '@corp'

const { store } = useSearchStore()
const { pgs, pss, settle_types, terminals, finance_vans, psFilter } = useStore()
const { headers, isPrimaryHeader } = useRegisterStore()
const { mchts } = useSalesFilterStore()

const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const error_message = ref('')
const use_mid_create = ref(Number(corp.pv_options.paid.use_mid_create))
const use_tid_create = ref(0)
const use_online_pay = ref(0)
const all_levels = [{ id: 10, title: '가맹점' }, ...salesLevels()]
const auth_types: Options[] = [
    { id: 0, title: '비인증', },
    { id: 1, title: '구인증', },
]
const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<PayModule[]>([])
const is_clear = ref<boolean>(false)

const midCreateDlg = ref()
const finance_van = ref({id: null, nick_name: ''})
const fin_trx_delay = ref(fin_trx_delays[0])
const withdraw_limit_type = ref({id: null, title: ''})

const comm_settle_type = ref(comm_settle_types[0])
const under_sales_type = ref(under_sales_types[0])
const all_level = ref(all_levels[0])
const module_type = ref(module_types[0])
const cxl_type = ref(cxl_types[0])
const pay_window_secure_level = ref(pay_window_secure_levels[0])
const settle_type = ref(settle_types[0])
const pg = ref({id: null, pg_name: ''})
const ps = ref({id: null, name: ''})
const terminal = ref({id: null, name: ''})

const filterPgs = computed(() => {
    if(pg.value) {
        const filter = pss.filter(item => { return item.pg_id === pg.value.id })
        ps.value.id = psFilter(filter, ps.value.id)
        return filter
    }
    else
        return []
})

const use_types: Options[] = [
    { id: 0, title: '미사용'},
    { id: 1, title: '사용'},
]
const { payKeyCreater, signKeyCreater } = keyCreater(snackbar, items)

const validate = async () => {
    error_message.value = ''
    for (let i = 0; i < items.value.length; i++) {        
        const results = validateItems(items.value[i], i, mchts)
        is_clear.value = results[0] as boolean
        error_message.value = results[1] as string

        if(is_clear.value === false) {
            error_message.value = '엑셀파일에서 ' + error_message.value
            snackbar.value.show(error_message.value, 'error')
            return
        }
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true

    if (corp.pv_options.paid.use_mid_create && use_mid_create.value)
        await midCreater()
    if (corp.pv_options.paid.use_tid_create && use_tid_create.value)
        await tidCreater()
    if (corp.pv_options.paid.use_online_pay) {
        signKeyCreater()
        if (use_online_pay.value)
            payKeyCreater()
    }
}

const midCreater = async () => {
    const mid_code = await midCreateDlg.value.show()
    if (mid_code) {
        snackbar.value.show('MID들을 자동 발급 중입니다.', 'primary')
        const params = {
            mid_code: mid_code,
            pay_mod_count: items.value.length
        }
        const r = await axios.post('/api/v1/manager/merchandises/pay-modules/mid-bulk-create', params)
        const new_mids = r.data.new_mids
        for (let i = 0; i < items.value.length; i++) {
            items.value[i].mid = new_mids[i]
        }
        snackbar.value.show('MID들이 발급 되었습니다.', 'success')
    }
}

const tidCreater = async () => {
    snackbar.value.show('TID들을 자동 발급 중입니다.', 'primary')
    const unique_pgids = [
        ...new Set(
            items.value
                .filter(item => (item?.tid === undefined || item?.tid.toString()?.trim() === ''))
                .map(item => item.pg_id)
        )
    ];
    const group_by_pgids = unique_pgids.map(pg_id => ({
        pg_id,
        pg_type: pgs.find(item => item.id === pg_id)?.pg_type,
        count: items.value.filter(item => item.pg_id === pg_id).length
    }));
    const r = await axios.post('/api/v1/manager/merchandises/pay-modules/tid-bulk-create', { groups: group_by_pgids })
    const new_tid_gruops = r.data
    for (let i = 0; i < items.value.length; i++) {
        let idx = new_tid_gruops.findIndex(obj => obj.pg_id === items.value[i].pg_id)
        if (idx !== null && new_tid_gruops[idx]['new_tids'].length) {
            items.value[i].tid = new_tid_gruops[idx]['new_tids'].shift()
        }
    }
    snackbar.value.show('TID들이 발급 되었습니다.', 'success')
}

const payModRegister = async () => {
    if (await bulkRegister('결제모듈', 'merchandises/pay-modules', items.value))
        location.reload()
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as PayModule[]
        await validate()
    }
})
</script>
<template>
    <VCard style='margin-top: 1em;'>
        <VRow style="padding: 1em;">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>
                    <UsageTooltip />
                    <br><br>
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 코드를 작성</b>해주세요.
                    <br><br>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </template>
                <template #input>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="use_mid_create" label="MID 자동발급 여부"
                        color="primary" v-if="corp.pv_options.paid.use_mid_create" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="use_tid_create" label="TID 자동발급 여부"
                        color="primary" v-if="corp.pv_options.paid.use_tid_create" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="use_online_pay" label="PAY KEY 자동발급 여부"
                        color="primary" v-if="corp.pv_options.paid.use_online_pay" />
                </template>
            </CreateHalfVCol>
            <VDivider />
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol>
                        <h3 class="pt-3">정산 정보</h3>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">가맹점 정산타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="settle_type"
                                            :items="settle_types"
                                            label="가맹점 정산타입 검색"
                                            :hint="`가맹점 정산타입 코드: ${settle_type ? settle_type.id : ''} `"
                                            item-title="name" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">PG사</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="pg"
                                            :items="pgs"
                                            label="PG사 검색"
                                            :hint="`PG사 코드: ${pg ? pg.id : ''} `"
                                            item-title="pg_name" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">구간</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="ps"
                                            :items="filterPgs"
                                            label="구간 검색"
                                            :hint="`구간 코드: ${ps ? ps.id : ''} `"
                                            item-title="name" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">결제 정보</h3>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">결제모듈 타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="module_type"
                                            :items="module_types"
                                            label="결제모듈 타입 검색"
                                            :hint="`결제모듈 타입 코드: ${module_type ? module_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">수기결제 여부</VCol>
                                    <VCol md="6">
                                        <VRow>
                                            <VChip color="primary" style="margin: 0.5em;" v-for="(auth, key) in auth_types" :key="key">
                                                {{ auth.title }} = {{ auth.id }}
                                            </VChip>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">제한 정보</h3>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">취소 타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="cxl_type"
                                            :items="cxl_types"
                                            label="취소 타입 검색"
                                            :hint="`취소 타입 코드: ${cxl_type ? cxl_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">결제창 보안등급</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="pay_window_secure_level"
                                            :items="pay_window_secure_levels"
                                            label="결제창 보안등급 검색"
                                            :hint="`결제창 보안등급 코드: ${pay_window_secure_level ? pay_window_secure_level.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VDivider style="margin: 1em 0;" />
                        <h3 class="pt-3">
                            <BaseQuestionTooltip :location="'top'" :text="'장비 정보'"
                        :content="'<b>통신비, 통신비 정산타입, 개통일, 정산일, 정산주체</b>가 설정되어있어야 적용됩니다.<br>ex)<br>통신비: 30,000<br>통신비 정산타입: 개통월 M+2부터 적용<br>개통일: 2023-09-25<br>정산일: 1일<br>정산주체: 가맹점<br><br>통신비 차감적용일: 2023-11-01, 2023-12-01, 2024-01-01 ...'"/>
                        </h3>
                        <VRow>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">장비 종류</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="terminal"
                                            :items="terminals"
                                            label="장비 종류"
                                            :hint="`장비 종류: ${terminal ? terminal.id : ''} `"
                                            item-title="name" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">통신비 정산타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="comm_settle_type"
                                            :items="comm_settle_types"
                                            label="통신비 정산타입 검색"
                                            :hint="`통신비 정산타입 코드: ${comm_settle_type ? comm_settle_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">매출미달 적용타입</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="under_sales_type"
                                            :items="under_sales_types"
                                            label="매출미달 적용타입 검색"
                                            :hint="`매출미달 적용타입 코드: ${under_sales_type ? under_sales_type.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">정산주체</VCol>
                                    <VCol md="6">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="all_level"
                                            :items="all_levels"
                                            label="정산주체 검색"
                                            :hint="`정산주체 코드: ${all_level ? all_level.id : ''} `"
                                            item-title="title" item-value="id" persistent-hint return-object
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <template v-if="corp.pv_options.paid.use_realtime_deposit">
                            <VDivider style="margin: 1em 0;" />
                            <h3 class="pt-3">즉시출금 정보</h3>
                            <VRow>
                                <VCol md="4" cols="12">
                                    <VRow>
                                        <VCol class="font-weight-bold" md="6">실시간 사용여부</VCol>
                                        <VCol md="6">

                                            <VRow>
                                                <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in use_types" :key="key">
                                                    {{ cus.title }} = {{ cus.id }}
                                                </VChip>
                                            </VRow>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol md="4" cols="12">
                                <VRow>
                                    <VCol class="font-weight-bold" md="6">이체모듈 검색</VCol>
                                        <VCol md="6">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="finance_van"
                                                :items="finance_vans"
                                                label="이체모듈 검색"
                                                :hint="`이체모듈 코드: ${finance_van ? finance_van.id : ''} `"
                                                item-title="nick_name" item-value="id" persistent-hint return-object
                                            />
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="finance_vans.length == 0">
                                                <b>
                                                    "운영 관리 - PG사 관리 - 실시간 이체모듈"에서 금융 VAN 추가 후 입력 가능합니다.
                                                </b>
                                            </VTooltip>
                                        </VCol>
                                    </VRow>
                                </VCol>
                                <VCol md="4" cols="12">
                                    <VRow>
                                        <VCol class="font-weight-bold" md="6">이체 딜레이 검색</VCol>
                                        <VCol md="6">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="fin_trx_delay"
                                                :items="fin_trx_delays"
                                                label="이체 딜레이 검색"
                                                :hint="`이체 딜레이 코드: ${fin_trx_delay ? fin_trx_delay.id : ''} `"
                                                item-title="title" item-value="id" persistent-hint return-object
                                            />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                            <VRow>
                                <VCol md="4" cols="12">
                                    <VRow>
                                        <VCol class="font-weight-bold" md="6">출금금지타입</VCol>
                                        <VCol md="6">
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="withdraw_limit_type"
                                                :items="withdraw_limit_types"
                                                label="출금금지타입 검색"
                                                :hint="`출금금지타입 코드: ${withdraw_limit_type ? withdraw_limit_type.id : ''} `"
                                                item-title="title" item-value="id" persistent-hint return-object
                                            />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </template>
                    </VCol>

                </template>
                <template #input>
                    <VCol>
                        <b class="important-text">한도, 매출미달 하한금 입력시 주의사항</b>
                        <br>
                        <span>- 만원 단위로 숫자만 입력(예: 100만원=100)</span>
                    </VCol>
                    <VCol>
                        <b>날짜타입 입력시 주의사항</b>
                        <br>
                        <span>0000-00-00 포멧으로 입력(예: 2024-01-01)</span>
                    </VCol>
                    <VCol>
                        <b>할부 한도 입력시 주의사항</b>
                        <br>
                        <span>- 숫자만 입력(예: 0,2,3,4...11)</span>
                    </VCol>
                    <VCol>
                        <b>결제금지시간 입력시 주의사항</b>
                        <br>
                        <span>- H:i:s 포멧으로 입력(예: 11:00:00)</span>
                    </VCol>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCard>
    <br>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12">
            <VCard>
                <VCardText class="d-flex flex-wrap py-4 gap-4">
                    <h3>1차 검증 테이블</h3>
                    <div class="app-user-search-filter d-flex flex-wrap gap-4" style="margin-left: auto;">
                        <b v-if="error_message !== '' && is_clear === false" style="display: inline-flex; align-items: center;">
                            <span class="text-error">
                                {{ error_message }}
                            </span>
                        </b>
                        <div style="inline-size: 15rem;">
                            <AppTextField
                                v-model="search"
                                placeholder="검색"
                                density="compact"
                                prepend-inner-icon="tabler:search"
                            >
                            </AppTextField>
                        </div>
                    </div>
                    <VDivider/>
                    <VDataTable v-model:items-per-page="item_per_page" v-model:page="page"                     
                        :items-length="items.length" :items="items" :headers="headers" class="text-no-wrap"
                        no-data-text="양식 업로드후 등록 버튼을 클릭해주세요."
                        item-value="title" :height="corp.pv_options.free.fix_table_size"
                        :search="search">
                        <template v-slot:headers="{ columns, isSorted, getSortIcon, toggleSort }">
                            <tr>
                                <th v-for="column in columns" :key="column.key + '_headers'">
                                    <span :class="isPrimaryHeader(column.key) ? 'text-primary' : ''">
                                        {{ column.title }}
                                    </span>
                                </th>
                            </tr>
                        </template>
                        <template v-slot:item="{ item }">
                            <tr>
                                <template v-for="header in headers" :key="header.key + '_items'">                                                                        
                                    <td v-if="header.key === 'terminal_id'">
                                        {{ terminals.find(terminal => terminal['id'] === item.terminal_id)?.name }}
                                    </td>
                                    <td v-else-if="header.key === 'installment'">
                                        {{ installments.find(inst => inst['id'] === item.installment)?.title }}
                                    </td>
                                    <td v-else-if="header.key === 'module_type'">
                                        <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item.module_type)?.id)">
                                            {{ module_types.find(module_type => module_type['id'] === item.module_type)?.title }}
                                        </VChip>
                                    </td>
                                    <td v-else-if="header.key === 'cxl_type'">
                                        {{ cxl_types.find(cxl_type => cxl_type['id'] === item.cxl_type)?.title }}
                                    </td>
                                    <td v-else-if="header.key === 'pg_id'">
                                        {{ pgs.find(pg => pg['id'] === item.pg_id)?.pg_name }}
                                    </td>
                                    <td v-else-if="header.key === 'ps_id'">
                                        {{ pss.find(ps => ps['id'] === item.ps_id)?.name }}
                                    </td>
                                    <td v-else-if="header.key === 'settle_type'">
                                        {{ settle_types.find(settle_type => settle_type['id'] === item.settle_type)?.name }}
                                    </td>
                                    <td v-else>
                                        <span>{{ item[header.key] }}</span>                                        
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </VDataTable>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('결제모듈 대량등록 포멧', headers)" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='pay-mod-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('pay-mod-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="payModRegister()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
    <MidCreateDialog ref="midCreateDlg" />
</template>
<style scoped>
.important-text {
  color: red;
}

:deep(.v-row) {
  align-items: center;
}
</style>
