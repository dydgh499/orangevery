<script lang="ts" setup>
import axios from '@axios';
import { requiredValidator } from '@validators';
import type { MerchandisePropertie } from '@/views/types'
import AlertDialog from '@/views/utils/AlertDialog.vue';

interface Props {
    item: MerchandisePropertie,
}
const props = defineProps<Props>()
const alert = ref<any>(null)
const salesforces = [{ sf_id: 1, sf_name: '테스트', sf_fee: 3.3 }];
const salesforce = ref({ sf_id: 0, sf_name: '영업자 선택', sf_fee: 0 })
//axios.get('/api/v1/util/salesforces')

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
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 Email -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="nameHorizontalIcons">상호</label>
                                </VCol>

                                <VCol cols="12" md="9">
                                    <VTextField id="nameHorizontalIcons" v-model="props.item.mcht_name"
                                        prepend-inner-icon="tabler-building-store" placeholder="상호를 입력해주세요"
                                        persistent-placeholder :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 수수료율 -->
                        <VCol cols="12">
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
                        <!-- 👉 수수료율 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="holdRateHorizontalIcons">보유금액 수수료율</label>
                                </VCol>
                                <VCol cols="12" md="5">
                                    <VTextField id="holdRateHorizontalIcons" v-model="props.item.hold_fee"
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

                        <!-- 👉 영업자 수수료율 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="salesforceHorizontalIcons">상위 영업자</label>
                                </VCol>

                                <VCol cols="12" md="5">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce" :items="salesforces"
                                        prepend-inner-icon="tabler-man" label="상위 영업자 선택"
                                        :hint="`수수료율: ${salesforce.sf_fee}%`" item-title="sf_name" item-value="sf_id"
                                        persistent-hint return-object single-line />

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
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>옵션정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>가맹점 수수료율 노출</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VRadioGroup v-model="props.item.is_show_fee" inline>
                                        <VRadio :value="true">
                                            <template #label>
                                                <span>
                                                    사용
                                                </span>
                                            </template>
                                        </VRadio>

                                        <VRadio :value="false">
                                            <template #label>
                                                <span>
                                                    미사용
                                                </span>
                                            </template>
                                        </VRadio>
                                    </VRadioGroup>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="acctNumHorizontalIcons">중복결제 허용</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VRadioGroup v-model="props.item.use_dupe_trx" inline>
                                        <VRadio :value="true">
                                            <template #label>
                                                <span>
                                                    사용
                                                </span>
                                            </template>
                                        </VRadio>

                                        <VRadio :value="false">
                                            <template #label>
                                                <span>
                                                    미사용
                                                </span>
                                            </template>
                                        </VRadio>
                                    </VRadioGroup>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>결제 하루 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_day_limit"
                                        type="number" :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>결제 1년 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="props.item.pay_year_limit"
                                        type="number" :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label>이상거래 금액 한도</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="props.item.abnormal_trans_limit" type="number"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
    <AlertDialog ref="alert"/>
</template>
