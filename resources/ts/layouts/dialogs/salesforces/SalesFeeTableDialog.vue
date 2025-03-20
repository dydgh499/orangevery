<script setup lang="ts">
import corp from '@/plugins/corp';
import { useRequestStore } from '@/views/request';
import { getTotalFee } from '@/views/salesforces/fee-table/useStore';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { getIndexByLevel, getUserLevel } from '@axios';
import { requiredValidatorV2 } from '@validators';

interface SalesforceFeeTable {
    id: number,
    sales5_id: number | null,
    sales5_fee: float,

    sales4_id: number | null,
    sales4_fee: float,

    sales3_id: number | null,
    sales3_fee: float,

    sales2_id: number | null,
    sales2_fee: float,

    sales1_id: number | null,
    sales1_fee: float,
}

const levels = corp.pv_options.auth.levels
const { sales, all_sales } = useSalesFilterStore()
const { update, remove } = useRequestStore()

const vForm = ref()
const visible = ref(false)
const salesforce_fee_table = ref(<SalesforceFeeTable><unknown>({ id: 0}))

const show = (__salesforce_fee_table: SalesforceFeeTable) => {
    salesforce_fee_table.value = __salesforce_fee_table
    visible.value = true
}

const updateRootSalesFee = () => {
    salesforce_fee_table.value.sales5_fee = all_sales[5].find(obj => obj.id === salesforce_fee_table.value.sales5_id)?.sales_fee ?? 0
}

const rootInputValidate = () => {
    const contract_fee  = all_sales[5].find(obj => obj.id === salesforce_fee_table.value.sales5_id)?.sales_fee ?? 0
    const profit_fee    = salesforce_fee_table.value.sales5_fee
    if(profit_fee > contract_fee)
        return `계약 수수료보다 작아야합니다.`
    else
        return true
}


defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="수수료율 테이블 관리">
            <VCardText>
                <VCard>
                    <VForm ref="vForm">
                        <VCol cols="12" v-if="levels['sales5_use'] && getUserLevel() >= getIndexByLevel(5)">
                            <VRow>
                                <VCol cols="12" :md="3">
                                    <b>
                                        {{ levels['sales5_name'] }}
                                    </b>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce_fee_table.sales5_id"
                                        :items="sales[5].value"
                                        variant="underlined"
                                        :label="levels['sales5_name'] + ' 선택'"
                                        @update:modelValue="updateRootSalesFee()"
                                        item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network"
                                        :rules="[requiredValidatorV2(salesforce_fee_table.sales5_id, levels['sales5_name'])]"
                                    />
                                </VCol>
                                <VCol cols="12" :md="5">
                                    <VRow>
                                        <VCol cols="6" :md="6">
                                            <b>
                                                {{ "계약 수수료: " }}
                                            </b>
                                        </VCol>
                                        <VCol cols="6" :md="6">
                                            <VChip size="small" color="success">
                                                {{ all_sales[5].find(obj => obj.id === salesforce_fee_table.sales5_id)?.sales_fee ?? 0 }} %
                                            </VChip>
                                        </VCol>
                                    </VRow>
                                    <VRow>
                                        <VCol cols="6" :md="6">
                                            <b>
                                                {{ "이익률: " }}
                                            </b>
                                        </VCol>
                                        <VCol cols="6" :md="6">
                                            <VTextField v-model="salesforce_fee_table.sales5_fee" type="number" suffix="%"
                                                variant="underlined"
                                                :rules="[requiredValidatorV2(salesforce_fee_table.sales5_fee, levels['sales5_name'] + ' 이익률'), rootInputValidate()]" />
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VDivider/>
                        <template v-for="i in 4" :key="i">
                            <VCol cols="12" v-if="levels['sales'+(5-i)+'_use'] && getUserLevel() >= getIndexByLevel(5-i)">
                                <VRow>
                                    <VCol cols="12" :md="3">
                                        <b>
                                            {{ levels['sales'+(5-i)+'_name'] }}
                                        </b>
                                    </VCol>
                                    <VCol cols="12" :md="4">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce_fee_table['sales'+(5-i)+'_id']"
                                            :items="sales[(5-i)].value"
                                            variant="underlined"
                                            :label="levels['sales'+(5-i)+'_name'] + ' 선택'"
                                            item-title="sales_name" item-value="id" persistent-hint single-line prepend-inner-icon="ph:share-network"
                                            :rules="[requiredValidatorV2(salesforce_fee_table['sales'+(5-i)+'_id'], levels['sales'+(5-i)+'_name'])]"
                                        />
                                    </VCol>
                                    <VCol cols="12" :md="5">
                                        <VRow>
                                            <VCol cols="6" :md="6">
                                                <b>
                                                    {{ "이익률: " }}
                                                </b>
                                            </VCol>
                                            <VCol cols="6" :md="6">
                                                <VTextField v-model="salesforce_fee_table['sales'+(5-i)+'_fee']" type="number" suffix="%"
                                                    variant="underlined"
                                                    :rules="[requiredValidatorV2(salesforce_fee_table['sales'+(5-i)+'_fee'], levels['sales'+(5-i)+'_name'] + ' 이익률')]" 
                                                />
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VDivider/>
                        </template>
                    </VForm>
                    <VCol cols="12">
                        <VRow>
                            <VCol cols="12" :md="5" style="margin-left: auto;">
                                <VRow>
                                    <VCol cols="6" :md="6">
                                        <b>
                                            {{ "이익률 합계: " }}
                                        </b>
                                    </VCol>
                                    <VCol cols="6" :md="6">
                                        <VChip size="small" color="primary">
                                            {{ getTotalFee(salesforce_fee_table) }} %
                                        </VChip>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </VCard>
                <VRow style="margin-top: 1em; float: inline-end;">
                    <VCol cols="12" class="d-flex gap-4">
                        <VBtn 
                            type="button" 
                            :color="salesforce_fee_table.id ? 'primary' : 'warning'" 
                            size="small" 
                            @click="update('/salesforces/fee-table', salesforce_fee_table, vForm, false)">
                            {{ salesforce_fee_table.id == 0 ? "추가하기" : "수정하기" }}
                        </VBtn>
                        <VBtn type="button" 
                            color="error" 
                            size="small" 
                            v-if="salesforce_fee_table.id"                            
                            @click="remove('/salesforces/fee-table', salesforce_fee_table, false)">
                            삭제하기
                            <VIcon end icon="tabler-trash" />
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
