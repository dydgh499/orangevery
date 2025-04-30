<script lang="ts" setup>
import { useZoomProperty } from '@/@layouts/composable/useZoomProperty';
import { htmlToPDF } from '@/views/pdf';
import { banks } from '@/views/users/useStore';
import { axios } from '@axios';
import corp from '@corp';
import { themeConfig } from '@themeConfig';
import { nextTick } from 'vue';

interface WithdrawStatement {
    mcht_name: string,
    acct_num: string,
    acct_bank_name: string,
    trans_seq_num: string,
    created_at: string,
    amount: number,
    withdraw_bank_code: string,
    withdraw_corp_name: string,
    withdraw_acct_num: string,
}

const { zoom } = useZoomProperty()
const { createPDF } = htmlToPDF()

const errorHandler = <any>(inject('$errorHandler'))
const formatDate = <any>(inject('$formatDate'))
const formatTime = <any>(inject('$formatTime'))
const snackbar = <any>(inject('snackbar'))
const visible = ref(false)
const statement = ref<WithdrawStatement>({
    mcht_name: '-',
    acct_num: '-',
    acct_bank_name: '-',
    trans_seq_num: '-',
    created_at: '-',
    amount: 0,
    withdraw_bank_code: '-',
    withdraw_corp_name: '-',
    withdraw_acct_num: '-',
})
const logoImg = ref<string>('')
const issue_date = ref<string>('')
const current_date = ref<string>('')
const card = ref()

const getLogo = async () => {
    try {
        const imageUrl = corp.logo_img
        
        if (imageUrl) {
        const response = await fetch(imageUrl)
        const blob = await response.blob()
        const reader = new FileReader()
        
        reader.onloadend = () => {
        logoImg.value = reader.result as string
        }
        
        reader.readAsDataURL(blob)
        }
    } catch (e: any) {
    }
}

// type 0=realtime, 1=collect, 2=deposit
const show = async (id: number, type: number) => {
        try {
        const url = '/api/v1/manager/transactions/settle-histories/merchandises/withdraw-statement'
        const res = await axios.get(url, {
            params: {
                id : id,
                type : type,
            }
        })
        setCurrentDate()
        statement.value = res.data
        await getLogo()
        visible.value = true
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const setCurrentDate = () => {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, '0')
    const day = String(now.getDate()).padStart(2, '0')

    issue_date.value = formatDate(now) + ' ' + formatTime(now)
    current_date.value = `${year}년 ${month}월 ${day}일`
}

const downloadPDF = async () => {
    zoom.value = Number(localStorage.getItem(`${themeConfig.app.title}-zoom`) ?? 90)
    if(zoom.value === 100) {
        if (card.value) {
            try {
                snackbar.value.show('PDF를 다운로드하고있습니다..', 'success')
                await createPDF(card.value, 450, 5.6, 3, 900, 800, 800, statement.value.trans_seq_num)
                snackbar.value.show('PDF가 다운로드 되었습니다.', 'success')
            } 
            catch (error) {
                console.error(error)
            }
        }
    }
    else
        snackbar.value.show('화면 배율을 100%로 설정한 후 출력해주세요.','error')
}

defineExpose({
    show
})
</script>
<template>
    <VDialog v-model="visible" max-width="1000">
        <DialogCloseBtn @click="visible = false" />
        <VBtn @click="downloadPDF()" size="small" style=" position: absolute; z-index: 9999; top: 16px; right: 35px;width: 120px;">
            PDF 다운로드
            <VIcon end icon="material-symbols:download" />
        </VBtn>
        <VCard>
            <section ref="card" class="card">
                <VCardText>
                    <h3 class="text-center">거래내역조회서</h3>
                    <VRow class="mt-8">
                        <VCol cols="6">
                            <b>발급일시(Date and Time of Issue):</b>
                            {{ issue_date }}
                        </VCol>
                        <VCol cols="6" class="text-right">
                            <b>거래번호(Transaction No):</b> {{ statement.trans_seq_num || '-' }}
                        </VCol>
                    </VRow>
                    <h4 class="mt-8">공급자</h4>
                    <table class="mt-2">
                        <tbody>
                            <tr>
                                <th class="text-center" style="width: 25%;">은행명</th>
                                <td class="text-center" style="width: 25%;">
                                    {{ banks.find(obj => obj.code == statement.withdraw_bank_code)?.title }}
                                </td>
                                <th class="text-center" style="width: 25%;">계좌번호</th>
                                <td class="text-center" style="width: 25%;">{{ statement.withdraw_acct_num || '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-center" style="width: 25%;">공급자명</th>
                                <td class="text-center" style="width: 25%;">{{ statement.withdraw_corp_name }}</td>
                                <th class="text-center" style="width: 25%;">출금금액</th>
                                <td class="text-center" style="width: 25%;">- {{ statement.amount.toLocaleString() }}원</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4 class="mt-4">수취인</h4>
                    <table class="mt-2">
                        <tbody>
                            <tr>
                                <th class="text-center" style="width: 25%;">은행명</th>
                                <td class="text-center" style="width: 25%;">{{ statement.acct_bank_name || '1' }}</td>
                                <th class="text-center" style="width: 25%;">계좌번호</th>
                                <td class="text-center" style="width: 25%;">{{ statement.acct_num || '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-center" style="width: 25%;">가맹점명</th>
                                <td class="text-center" style="width: 25%;">{{ statement.mcht_name }}</td>
                                <th class="text-center" style="width: 25%;">수취금액</th>
                                <td class="text-center" style="width: 25%;">{{ statement.amount.toLocaleString() }}원</td>
                            </tr>
                        </tbody>
                    </table>

                    <table>
                        <caption class="text-right">(단위: 원)</caption>
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 10%;">번호</th>
                                <th class="text-center" style="width: 30%;">거래일시</th>
                                <th class="text-center" style="width: 30%;">출금금액</th>
                                <th class="text-center" style="width: 30%;">입금금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" style="width: 10%;">1</td>
                                <td class="text-center" style="width: 30%;">{{ statement.created_at }}</td>
                                <td class="text-center" style="width: 30%;">{{ statement.amount.toLocaleString() }}원</td>
                                <td class="text-center" style="width: 30%;">{{ 0 }}원</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-center" style="width: 10%;">합계</td>
                                <td class="text-center" style="width: 30%;">{{ '-' }}</td>
                                <td class="text-center" style="width: 30%;">{{ statement.amount.toLocaleString() }}원</td>
                                <td class="text-center" style="width: 30%;">{{ 0 }}원</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-12" style="border: 3px solid lightgray;">
                        <li class="mt-4 mx-3">본 출력 양식은 별도의 법적 효력이 없습니다.</li>
                        <li class="mb-4 mx-3">위 거래내용(명세)은 고객님의 편의를 위하여 제공되는 것으로, 거래의 참고용으로만 사용하실 수 있습니다.</li>
                    </div>
                    <div class="mt-4" style="text-align: center;">
                        발급일(Date of Issue): {{ current_date }}
                    </div>
                    <div style="text-align: center;">
                        <img :src="corp.logo_img" width="80">
                    </div>
                    <div style="text-align: center;">
                        <img :src="logoImg" width="80">
                    </div>
                </VCardText>
            </section>
        </VCard>
    </VDialog>
</template>
<style scoped>
table {
  border-collapse: collapse;
  inline-size: 100%;
  margin-block-end: 1rem;
}

th,
td {
  padding: 8px;
  border: 1px solid #ddd;
  text-align: start;
}

th {
  background-color: #f2f2f2;
}

.pdf-download {
}
</style>
