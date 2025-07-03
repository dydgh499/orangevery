<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/options/useStore'
import type { PayGateway } from '@/views/types'
import { HistoryTargetNames } from '@core/enums'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'
import PaySectionTr from './PaySectionTr.vue';

interface Props {
    item: PayGateway,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))

const { pss, pg_companies } = useStore()
const { update, remove, setNullRemove } = useRequestStore()

const addNewSection = () => {
    pss.push({
        id: 0,
        pg_id: props.item.id as number,
        name: '',
        trx_fee: 0,
        is_delete: true,
    })
}
const filterPss = computed(() => {
    if (props.item.id != 0) 
        return pss.filter(item => { return item.pg_id == props.item.id })
    else
        return []
})

watchEffect(() => {
    if (props.item.pg_type != 0 && props.item.pg_type != null) {
        const idx = pg_companies.findIndex(item => item.id === props.item.pg_type)
        if (idx !== null) {
            props.item.addr = pg_companies[idx].addr
            props.item.rep_name = pg_companies[idx].rep_name
            props.item.company_name = pg_companies[idx].company_name
            props.item.business_num = pg_companies[idx].business_num
            props.item.phone_num = pg_companies[idx].phone_num
        }
    }
})

watchEffect(() => {
    setNullRemove(pss)
})
</script>
<template>
<AppCardActions action-collapsed :title="props.item.pg_name">
    <VDivider />
    <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
        <VCol cols="12" md="5">
            <VCardItem>
                <VForm ref="vForm">
                    <VCardTitle style="margin-bottom: 1em;">결제대행사 정보</VCardTitle>
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>결제대행사 선택</label>
                                </VCol>
                                <VCol md="7">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_type"
                                        :items="pg_companies"
                                        variant="underlined"
                                        prepend-inner-icon="ph-buildings" label="결제대행사 선택"
                                        item-title="name" item-value="id" single-line :rules="[requiredValidatorV2(props.item.pg_type, '결제대행사')]" 
                                    />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>별칭</label>
                                </VCol>
                                <VCol md="7">
                                    <VTextField type="text" v-model="props.item.pg_name"
                                        variant="underlined"
                                        prepend-inner-icon="tabler-table-alias" placeholder="별칭 입력"
                                        persistent-placeholder :rules="[requiredValidatorV2(props.item.pg_name, '별칭')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>대표자명</label>
                                </VCol>
                                <VCol md="7">
                                    <VTextField type="text" v-model="props.item.rep_name"
                                        variant="underlined"
                                        prepend-inner-icon="tabler-user" placeholder="대표자명 입력" persistent-placeholder
                                        :rules="[requiredValidatorV2(props.item.rep_name, '대표자명')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol>
                            <VRow no-gutters>
                                <VCol>
                                    <label>상호명</label>
                                </VCol>
                                <VCol md="7">
                                    <VTextField type="text" v-model="props.item.company_name"
                                        variant="underlined"
                                        prepend-inner-icon="tabler-building-store" placeholder="상호명 입력"
                                        persistent-placeholder :rules="[requiredValidatorV2(props.item.company_name, '상호명')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>사업자번호</label>
                                </VCol>
                                <VCol md="7">
                                    <VTextField v-model="props.item.business_num" type="text"
                                        variant="underlined"
                                        prepend-inner-icon="ic-outline-business-center" placeholder="사업자등록번호 입력"
                                        persistent-placeholder
                                        :rules="[requiredValidatorV2(props.item.business_num, '사업자등록번호')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol>
                            <VRow no-gutters>
                                <VCol>
                                    <label>휴대폰번호</label>
                                </VCol>
                                <VCol md="7">
                                    <VTextField v-model="props.item.phone_num" type="text"
                                        variant="underlined"
                                        prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                        persistent-placeholder :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호')]" />
                                </VCol>
                            </VRow>

                        </VCol>
                    </VRow>       
                    <VRow>
                        <VCol :md="12" :cols="12">
                            <VRow no-gutters>
                                <VCol md="2">
                                    <label>주소</label>
                                </VCol>
                                <VCol md="10">
                                    <VTextField v-model="props.item.addr" prepend-inner-icon="tabler-map-pin"
                                        variant="underlined"
                                        placeholder="주소 입력" persistent-placeholder maxlength="200"
                                        :rules="[requiredValidatorV2(props.item.addr, '주소')]" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol class="pt-10" style="text-align: end;">
                            <VBtn v-if="props.item.id"
                                style="margin-left: auto;"
                                color="secondary" 
                                variant="tonal"
                                @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['services/pay-gateways'])">
                                이력
                                <VIcon end size="20" icon="tabler:history" />
                            </VBtn>                            
                            <VBtn 
                                style="margin-left: 1em;"
                                @click="update('/services/pay-gateways', props.item, vForm, false)">
                                {{ props.item.id == 0 ? "추가" : "수정" }}
                                <VIcon end size="20" icon="tabler-pencil" />
                            </VBtn>
                            <VBtn v-if="props.item.id"
                                style="margin-left: 1em;"
                                color="error"
                                @click="remove('/services/pay-gateways', props.item, false)">
                                삭제
                                <VIcon end size="20" icon="tabler-trash" />
                            </VBtn>
                            <VBtn v-else
                                style="margin-left: 1em;"
                                color="warning"
                                @click="props.item.id = -1">
                                입력란 제거
                                <VIcon end size="20" icon="tabler-trash" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VForm>
            </VCardItem>
        </VCol>
        <VDivider :vertical="$vuetify.display.mdAndUp" />
        <VCol cols="12" md="7">
            <VCardItem>
                <VCardTitle style="margin-bottom: 1em;">수수료율 테이블</VCardTitle>
                <VTable style="text-align: center;">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;">No.</th>
                            <th scope="col" style="text-align: center;">수수료율 명칭</th>
                            <th scope="col" style="text-align: center;">거래 수수료율</th>
                            <th scope="col" style="text-align: center;">
                                <VBtn 
                                    type="button" 
                                    v-show="Boolean(props.item.id != 0)"
                                    size="small" 
                                    @click="addNewSection()">
                                    입력란 추가
                                    <VIcon end icon="tabler-plus" />
                                </VBtn>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <PaySectionTr v-for="(ps, index) in filterPss" :key="index" :item="ps" :index="index"/>
                    </tbody>
                    <tfoot v-show="Boolean(props.item.id == 0)">
                        <tr>
                            <td colspan="4" class="text-center">
                                결제대행사를 추가하신 후에 수수료율 테이블을 추가할 수 있습니다.
                            </td>
                        </tr>
                    </tfoot>
                </VTable>
            </VCardItem>
        </VCol>
    </div>
</AppCardActions>

</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}

:deep(.v-row) {
  align-items: center;
}
</style>
