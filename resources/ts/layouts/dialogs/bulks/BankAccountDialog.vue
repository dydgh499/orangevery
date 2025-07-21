<script lang="ts" setup>
import { banks } from '@/views/users/useStore'
import { useStore } from '@/views/services/options/useStore'
import { FinanceVan } from '@/views/types'

const visible = ref(false)
const { finance_vans } = useStore()
const tab = ref(0)
const tabs = [
    {
        title: "설명",
        icon: "ic:outline-help",
    },
    {
        title: "입력 주의사항",
        icon: "gridicons:notice",
    }
]
const bank = ref(banks[0])
const finance_van = ref(<FinanceVan><unknown>({ nick_name: null, card_num: '' }))
const show = () => {
    visible.value = true
}
const onCancel = () => {
    visible.value = false
}
defineExpose({ show })
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-sm">
        <DialogCloseBtn @click="onCancel" />
        <VCard title="이체하기 페이지 안내">
            <VTabs v-model="tab" class="mt-2 w-100">
                <VTab v-for="(t, index) in tabs" :key="index" grow>
                    <VIcon :size="18" :icon="t.icon" class="me-1" />
                    <span>{{ t.title }}</span>
                </VTab>
            </VTabs>
            <VDivider class="mb-3" />
            <VCardText>
                <VWindow v-model="tab" :touch="false">
                    <!-- 탭 1: 페이지 설명 -->
                    <VWindowItem>
                        <div class="text-base font-medium mb-4">
                            <strong>여러 건의 이체를 자동으로 처리하는 페이지입니다.</strong>
                            <br />아래 순서에 따라 실행됩니다.
                        </div>

                        <h3 class="text-md font-semibold mb-2">1. 사전 준비</h3>
                        <div class="ml-4 leading-loose">
                            <p>
                                <VBtn color="secondary" variant="tonal" size="x-small">
                                    양식 다운로드
                                    <VIcon end icon="uiw-file-excel" />
                                </VBtn>
                                버튼을 눌러 엑셀 양식을 받아주세요.
                            </p>
                            <p>
                                내용을 작성한 뒤,
                                <VBtn size="x-small">
                                    양식 업로드
                                    <VIcon end icon="uiw-file-excel" />
                                </VBtn>
                                버튼으로 파일을 업로드합니다.
                            </p>
                            <p>
                                시스템이 <b>자동으로 1차 유효성 검증</b>을 수행합니다.
                            </p>
                            <p>
                                모든 항목이 정상적으로 입력되었다면
                                <VBtn size="x-small">
                                    출금 예약
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                버튼이 활성화됩니다.
                            </p>
                        </div>

                        <VDivider class="my-4" />

                        <h3 class="text-md font-semibold mb-2">2. 이체 실행</h3>
                        <div class="ml-4 leading-loose">
                            <p>
                                <b>출금 예약</b> 버튼을 누르면, 업로드된 엑셀을 기반으로 아래 과정이 자동으로 처리돼요:
                            </p>
                            <ol class="ml-4 list-decimal">
                                <li><b>예금주 검증</b>: 엑셀의 계좌 정보로 예금주 검증</li>
                                <li><b>이체</b>: 결제 완료 후 계좌로 자동 이체</li>
                                <li style="margin-top: 1em;"><b>예금주 검증 실패시</b> <span class="text-error">다음시퀀스 미진행</span></li>
                                <li><b>예약 방식</b>으로 순차 진행</li>
                                <li><b>이체 현황</b>에서 실시간 확인 가능</li>
                            </ol>
                        </div>
                    </VWindowItem>

                    <!-- 탭 2: 엑셀 주의사항 -->
                    <VWindowItem>
                        <div class="text-base font-medium mb-4">
                            <strong>엑셀 작성 전 반드시 주의사항을 확인해주세요.</strong>
                        </div>

                        <h3 class="text-md font-semibold mb-2">1. 작성 전 유의사항</h3>
                        <div class="ml-4 leading-loose">
                            <p>
                                각 컬럼 우측에 표시된
                                <b>●</b>: 필수 입력값 / <b>○</b>: 선택 입력값
                            </p>
                            <p>
                                <b>출금 금액</b>: 숫자만 입력
                            </p>
                            <p>
                                <b>입금 계좌번호</b>: 숫자만 입력
                            </p>
                            <p>
                                <div style="display: flex; align-items: center; height: 3em;">
                                    <b>이체모듈 타입</b>: 
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="finance_van"
                                        :items="finance_vans"
                                        item-title="nick_name" 
                                        item-value="id" 
                                        variant="underlined"
                                        return-object
                                        persistent-hint
                                        :hint="`코드: ${finance_van ? finance_van.id : ''} `"
                                        style="max-width: 15em; margin-left: 1em;"
                                    />
                                </div>                                
                            </p>
                            <p>
                                <div style="display: flex; align-items: center; height: 3em;">
                                    <b>입금 은행코드</b>: 
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="bank"
                                        :items="banks"
                                        item-title="title" 
                                        item-value="code" 
                                        variant="underlined"
                                        return-object
                                        persistent-hint
                                        :hint="`은행 코드: ${bank ? bank.code : ''} `"
                                        style="max-width: 10em; margin-left: 1em;"
                                    />
                                </div>
                            </p>
                        </div>
                        <VDivider class="my-4" />
                        <h3 class="text-md font-semibold mb-2">2. 입력 예시</h3>
                        <div class="ml-4 leading-loose">
                            <ul class="ml-3">
                                <li><b>출금 금액</b>: <code>1000000</code></li>
                                <li><b>입금 계좌번호</b>: <code>12345123451234</code></li>
                                <li><b>입금 은행코드</b>: <code>003</code></li>
                            </ul>
                        </div>
                    </VWindowItem>
                </VWindow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
