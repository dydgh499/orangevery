<script lang="ts" setup>
import type { Salesforce } from '@/views/types'

interface Props {
    items: Salesforce[],
}
const props = defineProps<Props>();
const headers = ['아이디', '패스워드', '대표자명', '주소', '휴대폰번호', '사업자번호', '주민등록번호', '계좌번호', '예금주', '은행코드',]
</script>

<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업점정보</VCardTitle>
                    <VRow class="pt-5 pb-5">
                        <VTable class="text-no-wrap" style="width: 100%;">
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
