<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { noti_statuses, useSearchStore } from '@/views/merchandises/noti-urls/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { Registration } from '@/views/registration'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useRegisterStore, validateItems } from '@/views/services/bulk-register/NotiUrlRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import type { NotiUrl, PayModule } from '@/views/types'
import corp from '@corp'

const { store } = useSearchStore()
const { headers } = useRegisterStore()
const { mchts } = useSalesFilterStore()

const { ExcelFormatV2, ExcelReaderV2, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))
const search = ref('')
const item_per_page = ref(100)
const page = ref(1)

const excel = ref()
const items = ref<NotiUrl[]>([])
const is_clear = ref<boolean>(false)
const error_message = ref('')
const pay_modules = reactive<PayModule[]>([])

Object.assign(pay_modules, await getAllPayModules())

const validate = () => {
    error_message.value = ''
    for (let i = 0; i < items.value.length; i++) {
        const results = validateItems(items.value[i], i, mchts, pay_modules)
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
}
const register = async () => {
    if (await bulkRegister('노티주소', 'merchandises/noti-urls', items.value))
        location.reload()
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReaderV2(headers, excel.value[0]) as NotiUrl[]
        validate()
    }
})
</script>
<template>
    <VCard style='margin-top: 1em;'>
        <VRow style="padding: 1em;">
            <VCol style="padding-bottom: 0;">
                <VCol>
                    <UsageTooltip />
                </VCol>
                <VCol>
                    하단 컬럼들은 숫자로 매칭되는 값들입니다.
                    <br>
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 코드를 작성</b>해주세요.
                </VCol>
                <VCol>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </VCol>
            </VCol>
            <VDivider />
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol class="pb-0">
                        <b>노티 사용여부</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in noti_statuses" :key="key">
                            {{ cus.title }} = {{ cus.id }}
                        </VChip>
                    </VCol>
                </template>
                <template #input>
                    <VCol class="pb-0">
                        <b>결제모듈 별칭</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;">
                            -1 = 전체
                        </VChip>
                    </VCol>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCard>
    <br>
    <VRow class="match-height">
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
                                    <span :class="'text-primary'">
                                        {{ column.title }}
                                    </span>
                                </th>
                            </tr>
                        </template>
                        <template v-slot:item="{ item }">
                            <tr>
                                <template v-for="header in headers" :key="header.key + '_items'">
                                    <td class='list-square'>
                                        <span v-if="header.key == 'noti_status'">
                                            <VChip :color="store.booleanTypeColor(!item[header.key])">
                                                {{ noti_statuses.find(noti => noti['id'] === item[header.key])?.title }}
                                            </VChip>
                                        </span>
                                        <span v-else>
                                            {{ item[header.key] }}
                                        </span>
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
            <VBtn color="secondary" variant="tonal" @click="ExcelFormatV2('노티주소 대량등록 포멧', headers)" style="margin-left: auto;">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VFileInput id='noti-url-uploader' accept="xlsx/*" show-size v-model="excel" v-show="false">
            </VFileInput>
            <VBtn type="button" @click="openFilePicker('noti-url-uploader')">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn type="button" @click="register()" v-show="is_clear">
                등록
                <VIcon end icon="tabler-pencil" />
            </VBtn>
        </VCol>
    </VCard>
</template>
<style scoped>
.important-text {
  color: red;
}
</style>

