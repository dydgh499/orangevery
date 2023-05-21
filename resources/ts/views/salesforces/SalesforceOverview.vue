<script lang="ts" setup>
import {axios} from '@axios';
import { requiredValidator } from '@validators';
import type { SalesforcePropertie } from '@/views/types'
import AlertDialog from '@/views/utils/AlertDialog.vue';

interface Props {
    item: SalesforcePropertie,
}
const props = defineProps<Props>()

const alert = inject('alert');
const snackbar = inject('snackbar');
const errorHandler = inject('$errorHandler');

async function directFeeChange() {
    if (await alert.value.show('정말 즉시적용하시겠습니까?')) {

    }
}
async function bookFeeChange() {
    if (await alert.value.show('정말 예약적용하시겠습니까? 명일 00시에 반영됩니다.')) {

    }
}
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>영업자정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 Email -->
                        <VCol cols="6">
                            <VRow no-gutters>
                                <VCol cols="6" md="3">
                                    <label for="acctNumHorizontalIcons">정산 세율</label>
                                </VCol>
                                <VCol cols="6" md="9">
                                    <VRadioGroup v-model="props.item.tax_type" inline>
                                        <VRadio :value="0">
                                            <template #label>
                                                <span>
                                                    세율 없음
                                                </span>
                                            </template>
                                        </VRadio>

                                        <VRadio :value="1">
                                            <template #label>
                                                <span>
                                                    3.3%
                                                </span>
                                            </template>
                                        </VRadio>
                                        <VRadio :value="2">
                                            <template #label>
                                                <span>
                                                    10%
                                                </span>
                                            </template>
                                        </VRadio>
                                        <VRadio :value="3">
                                            <template #label>
                                                <span>
                                                    10+3.3%
                                                </span>
                                            </template>
                                        </VRadio>
                                    </VRadioGroup>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>                    
                    <VRow class="pt-5">
                         <!-- 👉 수수료율 -->
                         <VCol cols="6">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="feesRateHorizontalIcons">거래 수수료율</label>
                                </VCol>
                                <VCol cols="12" md="5">
                                    <VTextField id="feesRateHorizontalIcons" v-model="props.item.trx_fee"
                                        type="number" suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" md="4"
                                    style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn type="submit" size="small" variant="tonal" 
                                        @click="directFeeChange()"
                                        style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn type="submit" size="small" variant="tonal" color="secondary" 
                                        @click="bookFeeChange()"
                                        style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                        예약적용
                                        <VIcon end icon="tabler-clock-up" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                       
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
    <AlertDialog ref="alert"/>
</template>
