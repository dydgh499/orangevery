<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { noti_statuses, useSearchStore } from '@/views/merchandises/noti-urls/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { Registration } from '@/views/registration'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useRegisterStore } from '@/views/services/bulk-register/NotiUrlRegisterStore'
import UsageTooltip from '@/views/services/bulk-register/UsageTooltip.vue'
import type { NotiUrl, PayModule } from '@/views/types'
import { isEmpty } from '@core/utils'

const { store } = useSearchStore()
const { head, headers } = useRegisterStore()
const { mchts } = useSalesFilterStore()


const { ExcelReader, openFilePicker, bulkRegister } = Registration()

const snackbar = <any>(inject('snackbar'))

const excel = ref()
const items = ref<NotiUrl[]>([])
const is_clear = ref<boolean>(false)
const pay_modules = reactive<PayModule[]>([])

Object.assign(pay_modules, await getAllPayModules())


const validate = () => {
    for (let i = 0; i < items.value.length; i++) {
        items.value[i].mcht_name = items.value[i].mcht_name ? items.value[i].mcht_name?.trim() : ''
        const mcht = mchts.find(item => item.mcht_name == items.value[i].mcht_name)

        if (mcht) {
            items.value[i].pmod_id = items.value[i].pmod_note == -1 ? -1 : filterPayModuleNote(items.value[i].pmod_note, mcht.id) as number
            if (items.value[i].pmod_id === null) {
                snackbar.value.show((i + 1) + '번째 노티의 결제모듈 별칭이 이상합니다.', 'error')
                is_clear.value = false
            }

            else if (isEmpty(items.value[i].send_url)) {
                snackbar.value.show((i + 2) + '번째 노티주소가 비어있습니다.', 'error')
                is_clear.value = false
            }
            else
                is_clear.value = true
        }
        else {
            snackbar.value.show((i + 2) + '번째 노티의 가맹점 상호가 이상합니다.', 'error')
            is_clear.value = false
        }
        items.value[i].mcht_id = mcht?.id as number
        if (is_clear.value == false)
            return
    }
    snackbar.value.show('입력값 1차 검증에 성공하였습니다.', 'success')
    is_clear.value = true
}
const register = async () => {
    if (await bulkRegister('노티주소', 'merchandises/noti-urls', items.value))
        location.reload()
}

const filterPayModuleNote = (pmod_note: string, mcht_id: number) => {
    const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id === mcht_id })
    return filter.find(obj => obj.note === pmod_note.trim())?.id
}

watchEffect(async () => {
    if (excel.value) {
        items.value = await ExcelReader(headers, excel.value[0]) as NotiUrl[]
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
                    엑셀 작성시 <b class="important-text">입력하실 내용에 매칭되는 숫자를 작성</b>해주세요.
                </VCol>
                <VCol>
                    컬럼 우측의 <b>O표시는 필수 입력값, X표시는 옵션 입력값</b>을 의미합니다.
                </VCol>
            </VCol>
            <VDivider />
            <CreateHalfVCol :mdl="8" :mdr="4">
                <template #name>
                    <VCol class="pb-0">
                        <b>노티 사용유무</b>
                        <br>
                        <VChip color="primary" style="margin: 0.5em;" v-for="(cus, key) in noti_statuses" :key="key">
                            {{ cus.title }} = {{ cus.id }}
                        </VChip>
                    </VCol>
                </template>
                <template #input>
                </template>
            </CreateHalfVCol>
        </VRow>
    </VCard>
    <br>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>노티 정보</VCardTitle>
                    <VRow class="pt-5 pb-5">
                        <VTable class="text-no-wrap" style="width: 100%;">
                            <!-- 👉 table head -->
                            <thead>
                                <tr>
                                    <th v-for="(header, key) in head.flat_headers" :key="key" class='list-square'>
                                        <span>
                                            {{ header.ko }}
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in items" :key="index">
                                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                        <td class='list-square'>
                                            <span v-if="_key == 'noti_status'">
                                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                                    {{ noti_statuses.find(noti => noti['id'] === item[_key])?.title }}
                                                </VChip>
                                            </span>
                                            <span v-else>
                                                {{ item[_key] }}
                                            </span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot v-show="!Boolean(items.length)">
                                <tr>
                                    <td :colspan="Object.keys(head.flat_headers).length" class='list-square'
                                        style="border: 0;">
                                        양식 업로드후 등록 버튼을 클릭해주세요.
                                    </td>
                                </tr>
                            </tfoot>
                        </VTable>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn color="secondary" variant="tonal" @click="head.exportToExcel([])" style="margin-left: auto;">
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

