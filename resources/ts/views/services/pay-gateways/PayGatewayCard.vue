<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useRequestStore } from '@/views/request'
import PaySectionTr from '@/views/services/pay-gateways/PaySectionTr.vue'
import { pg_settle_types, useStore } from '@/views/services/pay-gateways/useStore'
import type { PayGateway } from '@/views/types'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

interface Props {
    item: PayGateway,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

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
    if (props.item.id != 0) {
        const filter = pss.filter(item => {
            return item.pg_id == props.item.id;
        })
        return filter
    }
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
                        <VCardTitle style="margin-bottom: 1em;">PG사 정보</VCardTitle>
                        <VRow class="pt-3">
                            <VCol :md="6" :cols="12">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>PG사 선택</label>
                                    </VCol>
                                    <VCol md="7">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_type"
                                            :items="pg_companies" prepend-inner-icon="ph-buildings" label="PG사 선택"
                                            item-title="name" item-value="id" single-line :rules="[requiredValidatorV2(props.item.pg_type, 'PG사')]" />
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
                                            prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                            persistent-placeholder :rules="[requiredValidatorV2(props.item.phone_num, '휴대폰번호')]" />
                                    </VCol>
                                </VRow>

                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol :md="6" :cols="12">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>주소</label>
                                    </VCol>
                                    <VCol md="7">
                                        <VTextField v-model="props.item.addr" prepend-inner-icon="tabler-map-pin"
                                            placeholder="주소 입력" persistent-placeholder maxlength="200"
                                            :rules="[requiredValidatorV2(props.item.addr, '주소')]" />
                                    </VCol>
                                </VRow>

                            </VCol>
                            <VCol>
                                <VRow no-gutters>
                                    <VCol>
                                        <label>정산타입</label>
                                    </VCol>
                                    <VCol md="7">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type"
                                            :items="pg_settle_types" prepend-inner-icon="tabler-calculator" label="정산타입 선택"
                                            item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.item.settle_type, '정산타입')]" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>                        
                        <VCardTitle style=" margin-top: 1em; margin-bottom: 1em;">                            
                            <BaseQuestionTooltip :location="'top'" :text="'대표 결제 정보'"
                                :content="'결제모듈 등록 시 대표 결제정보 값들을 불러올 수 있습니다.'"/>
                        </VCardTitle>
                        <VRow>
                            <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_pmid">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>PMID</label>
                                    </VCol>
                                    <VCol md="7">
                                    <VTextField type="text" v-model="props.item.p_mid" prepend-inner-icon="tabler-user"
                                        placeholder="PMID 입력" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol :md="6" :cols="12">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>MID</label>
                                    </VCol>
                                    <VCol md="7">
                                    <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                                        placeholder="MID 입력" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol :md="6" :cols="12">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>API KEY</label>
                                    </VCol>
                                    <VCol md="7">
                                    <VTextField type="text" v-model="props.item.api_key"
                                        prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                        persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol :md="6" :cols="12">
                                <VRow no-gutters>
                                    <VCol>
                                        <label>SUB KEY(iv)</label>
                                    </VCol>
                                    <VCol md="7">
                                    <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                                        placeholder="SUB KEY 입력" persistent-placeholder />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4 pt-10">
                                <VBtn type="button" style="margin-left: auto;"
                                    @click="update('/services/pay-gateways', props.item, vForm, false)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id"
                                    @click="remove('/services/pay-gateways', props.item, false)">
                                    삭제
                                    <VIcon end icon="tabler-trash" />
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardItem>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" />
            <VCol cols="12" md="7">
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">상세구간 테이블(영중소 등)</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">구간명</th>
                                <th scope="col" style="text-align: center;">거래 수수료율</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <PaySectionTr v-for="(ps, index) in filterPss" :key="index" :item="ps" :index="index">
                            </PaySectionTr>
                        </tbody>
                        <tfoot v-show="Boolean(props.item.id == 0)">
                            <tr>
                                <td colspan="4" class="text-center">
                                    PG사를 추가하신 후에 상세구간 테이블을 설정할 수 있습니다.
                                </td>
                            </tr>
                        </tfoot>
                    </VTable>
                    <VRow v-show="Boolean(props.item.id != 0)">
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;" @click="addNewSection()">
                                구간 추가
                                <VIcon end icon="tabler-plus" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCol>
        </div>
    </AppCardActions>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
