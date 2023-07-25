<script lang="ts" setup>
import { businessNumValidator, requiredValidator } from '@validators'
import type { PayGateway, PaySection } from '@/views/types'
import { VForm } from 'vuetify/components'
import { useStore } from '@/views/services/pay-gateways/useStore'
import PaySectionTr from '@/views/services/pay-gateways/PaySectionTr.vue'
import { useRequestStore } from '@/views/request'

interface Props {
    item: PayGateway,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()

const { pss, pg_types }  = useStore()
const { update, remove } = useRequestStore()

const new_pss = reactive<PaySection[]>([])

const addNewSection = () => {
    new_pss.push({
        id: 0,
        pg_id: props.item.id,
        name: '',
        trx_fee: 0,
        is_delete: true,
    })
}

watchEffect(() => {
    if (props.item.pg_type != 0 && props.item.pg_type != null) {
        const idx = pg_types.findIndex(item => item.id == props.item.pg_type)
        if (idx != null) {
            props.item.addr = pg_types[idx].addr
            props.item.rep_name = pg_types[idx].rep_name
            props.item.company_name = pg_types[idx].company_name
            props.item.business_num = pg_types[idx].business_num
            props.item.phone_num = pg_types[idx].phone_num
        }
    }
})
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

</script>
<template>
    <AppCardActions action-collapsed :title="props.item.pg_name" :collapsed="true">
        <VDivider />
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <VCol cols="12" md="5">
                <VCardItem>
                    <VForm ref="vForm">
                        <VCardTitle style="margin-bottom: 1em;">PG사 정보</VCardTitle>
                        <VRow class="pt-3">
                            <VCol>
                                <label>PG사 선택</label>
                            </VCol>
                            <VCol>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_type" :items="pg_types"
                                    prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="name" item-value="id"
                                    single-line :rules="[requiredValidator]" />
                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>별칭</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.pg_name" prepend-inner-icon="tabler-table-alias"
                                    placeholder="별칭 입력" persistent-placeholder :rules="[requiredValidator]" />
                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>대표자명</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.rep_name" prepend-inner-icon="tabler-user"
                                    placeholder="대표자명 입력" persistent-placeholder :rules="[requiredValidator]" />
                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>상호명</label>
                            </VCol>
                            <VCol>
                                <VTextField type="text" v-model="props.item.company_name"
                                    prepend-inner-icon="tabler-building-store" placeholder="상호명 입력"
                                    persistent-placeholder :rules="[requiredValidator]"/>
                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>사업자등록번호</label>
                            </VCol>
                            <VCol>
                                <VTextField v-model="props.item.business_num" type="text"
                                    prepend-inner-icon="ic-outline-business-center" placeholder="사업자등록번호 입력"
                                    persistent-placeholder
                                    :rules="[requiredValidator, businessNumValidator(props.item.business_num)]" />

                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>휴대폰번호</label>
                            </VCol>
                            <VCol>
                                <VTextField v-model="props.item.phone_num" type="text"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호 입력"
                                    persistent-placeholder :rules="[requiredValidator]" />

                            </VCol>
                        </VRow>
                        <VRow class="pt-3">
                            <VCol>
                                <label>주소</label>
                            </VCol>
                            <VCol>
                                <VTextField v-model="props.item.addr" prepend-inner-icon="tabler-map-pin"
                                    placeholder="주소 입력" persistent-placeholder maxlength="200" :rules="[requiredValidator]" />
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol class="d-flex gap-4 pt-10">
                                <VBtn type="button" style="margin-left: auto;" @click="update('/services/pay-gateways', props.item.id as number, props.item, vForm, false)">
                                    {{ props.item.id == 0 ? "추가" : "수정" }}
                                    <VIcon end icon="tabler-pencil" />
                                </VBtn>
                                <VBtn type="button" color="error" v-if="props.item.id" @click="remove('/services/pay-gateways', props.item.id, false)">
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
                            <PaySectionTr v-for="(ps, index) in new_pss" :key="index" :item="ps" :index="index">
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
