<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import corp from '@/plugins/corp'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { isAbleModiy } from '@axios'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))

const { mchts } = useSalesFilterStore()
const { pgs, pss, psFilter, setFee } = useStore()

const setPGKeyInfo = () => {
    if(props.item.pg_id) {
        const pg = pgs.find(obj => obj.id === props.item.pg_id)
        if(pg) {
            props.item.mid = pg.mid
            props.item.api_key = pg.api_key 
            props.item.sub_key = pg.sub_key
            props.item.p_mid = pg.p_mid
            snackbar.value.show('결제 정보들이 세팅되었습니다.', 'success')
        }
    }
    else
        snackbar.value.show('PG사를 먼저 선택해주세요.', 'warning')
}
const onModuleTypeChange = () => {
    props.item.note = module_types.find(obj => obj.id === props.item.module_type)?.title || ''
}
const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.pg_id })
    props.item.ps_id = psFilter(filter, props.item.ps_id)
    return filter
})

</script>
<template>
    <VCardItem>
        <VRow v-if="props.able_mcht_chanage && isAbleModiy(props.item.id)">
            <VCol md="6" cols="6">소유 가맹점</VCol>
            <VCol md="6">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                        prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name" item-value="id"
                        single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" :eager="true" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">소유 가맹점</span>
            </VCol>
            <VCol md="7">
                {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
            </VCol>
        </VRow>
        <VCardSubtitle></VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="6" cols="12">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                        @update:modelValue="onModuleTypeChange" :items="module_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 타입" item-title="title"
                        item-value="id" :rules="[requiredValidatorV2(props.item.module_type, '결제모듈 타입')]" />
            </VCol>
            <VCol md="6">
                <VTextField v-model="props.item.note" label="결제모듈 별칭" placeholder='결제모듈 명칭 입력'
                prepend-inner-icon="twemoji-spiral-notepad" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">결제모듈 타입</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ module_types.find(obj => obj.id === props.item.module_type)?.title }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">결제모듈 별칭</span>
            </VCol>
            <VCol md="7">
                {{ props.item.note }}
            </VCol>
        </VRow>

        <template v-if="isAbleModiy(props.item.id)">
            <VDivider style="margin: 1em 0;" />
            <VCardSubtitle>
                <div style="display: flex; flex-direction: row;align-items: center;">
                    <span>원천사 정보</span>
                    <VBtn size="small" variant="tonal" @click="setPGKeyInfo()" style="margin-left: 0.5em;">가져오기</VBtn>
                </div>
            </VCardSubtitle>
            <br>
            <VRow style="align-items: baseline !important;">
                <VCol md="6" cols="12">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                            prepend-inner-icon="ph-buildings" label="원천사 선택" item-title="pg_name" item-value="id"
                            :rules="[requiredValidatorV2(props.item.pg_id, 'PG사')]" />    
                </VCol>
                <VCol md="6">
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id" :items="filterPgs"
                        prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name" item-value="id"
                        :hint="`${setFee(pss, props.item.ps_id)}`" persistent-hint
                        :rules="[requiredValidatorV2(props.item.ps_id, '구간')]" />
                </VCol>
            </VRow>
            <VRow>
                <VCol md="6" cols="12">
                    <VTextField type="text" v-model="props.item.api_key" prepend-inner-icon="ic-baseline-vpn-key"
                            placeholder="API KEY 입력" persistent-placeholder maxlength="100" label="API KEY"/>
                </VCol>
                <VCol md="6">
                    <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                            placeholder="SUB KEY 입력" persistent-placeholder maxlength="100" label="SUB KEY"/>
                </VCol>
            </VRow>
            <VRow v-if="corp.pv_options.paid.use_pmid">
                <VCol md="6" cols="12">                    
                    <VTextField type="text" v-model="props.item.p_mid" prepend-inner-icon="tabler-user"
                            placeholder="PMID 입력" persistent-placeholder maxlength="50" label="PMID"/>
                </VCol>
            </VRow>
            <VRow v-show="props.item.module_type == 1" v-if="isAbleModiy(props.item.id)">
                <VCol md="5" cols="5">수기결제 타입</VCol>
                <VCol md="7">
                    <BooleanRadio :radio="props.item.is_old_auth" @update:radio="props.item.is_old_auth = $event">
                        <template #true>구인증</template>
                        <template #false>비인증</template>
                    </BooleanRadio>                
                </VCol>
            </VRow>
            <VRow v-if="corp.use_different_settlement">
                <VCol md="5" cols="5">차액정산 활성화</VCol>
                <VCol md="7">
                    <VSwitch hide-details :false-value=0 :true-value=1 
                        v-model="props.item.is_different_settlement" 
                        label="" color="error" />
                </VCol>
            </VRow>
        </template>
        <template v-else>
            <VRow v-if="props.item.module_type == 1">
                <VCol md="5" cols="5">
                    <span class="font-weight-bold">수기결제 타입</span>
                </VCol>
                <VCol md="7">
                    {{ props.item.is_old_auth ? "구인증" : "비인증" }}
                </VCol>
            </VRow>
        </template>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
