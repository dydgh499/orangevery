<script lang="ts" setup>
import type { PayModule } from '@/views/types'

interface Props {
    items: PayModule[],
}
const props = defineProps<Props>();
const headers = [
'가맹점 ID', '결제모듈 타입', '수기결제 타입', '할부한도', 'PG사', 
'구간', '정산일', 'API KEY', 'SUB EKY', 'MID', 'TID', 
'매출전표 공급자 정보', '매출전표 판매자 정보', '비고', '단말기 타입', 
'통신비', '정산일', '정산주체', '매출미달 차감금', '개통일', '출고일', '출고 상태'
]
</script>

<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>결제모듈정보</VCardTitle>
                    <VRow class="pt-5 pb-5">
                        <VTable class="text-no-wrap">
                            <!-- 👉 table head -->
                            <thead>
                                <tr>
                                    <th v-for="(header, index) in headers" :key="index" scope="col">
                                        {{ header }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(user, index) in props.items" :key="index" style="height: 3.75rem;">
                                    <td v-for="(val, index) in Object.keys(user)" :key="index">
                                        <span>
                                            {{ val }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-show="!Boolean(props.items.length || false)">
                                <tr>
                                    <td :colspan="headers.length" class="text-center">
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
</template>
