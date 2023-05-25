<script lang="ts" setup>
import { axios } from '@axios';
import { requiredValidator, nullValidator } from '@validators';
import type { MerchandisePropertie } from '@/views/types'
import BooleanRadio from '@/views/utils/BooleanRadio.vue';
import CreateHalfVCol from '@/views/utils/CreateHalfVCol.vue';
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'

interface Props {
    item: MerchandisePropertie,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = inject('$errorHandler');

const salesforce = ref({})
const { hierarchical, flattened } = useSalesHierarchicalStore()

props.item.is_show_fee = Boolean(props.item.is_show_fee)
props.item.use_dupe_trx = Boolean(props.item.use_dupe_trx)

const directFeeChange = async () => {
    if (await alert.value.show('정말 즉시적용하시겠습니까?')) {

    }
}
const bookFeeChange = async () => {
    if (await alert.value.show('정말 예약적용하시겠습니까? 명일 00시에 반영됩니다.')) {

    }
}
watchEffect(() => {
    const sf_idx = flattened.findIndex(item => item.id === props.item.group_id)
    salesforce.value = sf_idx == -1 ? { id: props.item.group_id, user_name: '영업자 선택', trx_fee: 0 } : flattened[sf_idx]
})
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
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name>상호</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.mcht_name"
                                    prepend-inner-icon="tabler-building-store" placeholder="상호를 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <!-- 👉 수수료율 -->
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="feesRateHorizontalIcons">거래 수수료율</label>
                                </VCol>
                                <VCol cols="12" md="5">
                                    <VTextField id="feesRateHorizontalIcons" v-model="props.item.trx_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" md="4"
                                    style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn type="submit" size="small" variant="tonal" @click="directFeeChange()"
                                        style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn type="submit" size="small" variant="tonal" color="secondary"
                                        @click="bookFeeChange()" style='flex-grow: 1; margin: 0.25em 0.5em;'>
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
                                    <VTextField id="holdRateHorizontalIcons" v-model="props.item.hold_fee" type="number"
                                        suffix="%" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" md="4"
                                    style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn type="submit" size="small" variant="tonal" @click="directFeeChange()"
                                        style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn type="submit" size="small" variant="tonal" color="secondary"
                                        @click="bookFeeChange()" style='flex-grow: 1; margin: 0.25em 0.5em;'>
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
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce" :items="flattened"
                                        prepend-inner-icon="tabler-man" label="상위 영업자 선택"
                                        :hint="`수수료율: ${(salesforce.trx_fee * 100).toFixed(3)}%`" item-title="user_name"
                                        item-value="id" persistent-hint single-line return-object />
                                </VCol>
                                <VCol cols="12" md="4"
                                    style="display: flex; flex-direction: row; justify-content: space-between;">
                                    <VBtn type="submit" size="small" variant="tonal" @click="directFeeChange()"
                                        style='flex-grow: 1; margin: 0.25em 0.5em;'>
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn type="submit" size="small" variant="tonal" color="secondary"
                                        @click="bookFeeChange()" style='flex-grow: 1; margin: 0.25em 0.5em;'>
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
                                    <BooleanRadio :radio.sync="props.item.is_show_fee" @update:radio="props.item.is_show_fee=$event">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow no-gutters>
                                <VCol cols="12" md="3">
                                    <label for="acctNumHorizontalIcons">중복결제 허용</label>
                                </VCol>
                                <VCol cols="12" md="9">
                                    <BooleanRadio :radio.sync="props.item.use_dupe_trx" @update:radio="props.item.use_dupe_trx = $event">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
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
</template>
