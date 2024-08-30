<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import {
    installments,
    module_types
} from '@/views/merchandises/pay-modules/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { isAbleModiy } from '@axios'
import corp from '@corp'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))

const { mchts } = useSalesFilterStore()
const { pgs, pss, settle_types, psFilter, setFee } = useStore()

const setPGKeyInfo = () => {
    if(props.item.pg_id) {
        const pg = pgs.find(obj => obj.id === props.item.pg_id)
        if(pg) {
            props.item.mid = pg.mid
            props.item.api_key = pg.api_key 
            props.item.sub_key = pg.sub_key
            if(corp.pv_options.paid.use_pmid)
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
            <VCol md="5" cols="5">소유 가맹점</VCol>
            <VCol md="7">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id" :items="mchts"
                        prepend-inner-icon="tabler-building-store" label="가맹점 선택" item-title="mcht_name" item-value="id"
                        single-line :rules="[requiredValidatorV2(props.item.mcht_id, '가맹점')]" :eager="true" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">소유 가맹점</span>
            </VCol>
            <VCol md="7">
                {{ mchts.find(obj => obj.id === props.item.mcht_id)?.mcht_name }}
            </VCol>
        </VRow>
        <!-- 👉 비고 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">결제모듈 별칭</VCol>
            <VCol md="7">
                <VTextField v-model="props.item.note" label="" placeholder='결제모듈 명칭을 입력해주세요.'
                prepend-inner-icon="twemoji-spiral-notepad" />
            </VCol>
        </VRow>

        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="5">결제모듈 타입</VCol>
            <VCol md="7">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                        @update:modelValue="onModuleTypeChange" :items="module_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" label="결제모듈 선택" item-title="title"
                        item-value="id" single-line :rules="[requiredValidatorV2(props.item.module_type, '결제모듈 타입')]" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="5">
                <span class="font-weight-bold">결제모듈 타입</span>
            </VCol>
            <VCol md="7">
                {{ module_types.find(obj => obj.id === props.item.module_type)?.title }}
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


        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="6">할부한도</VCol>
            <VCol md="7" cols="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment" :items="installments"
                    prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="할부한도 선택" item-title="title"
                    item-value="id" single-line />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">할부한도</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ installments.find(obj => obj.id === props.item.installment)?.title }}
            </VCol>
        </VRow>
        <!-- 👉 PG사 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="6">
                <span>PG사</span>
                <VBtn size="small" variant="tonal" @click="setPGKeyInfo()" style="margin-left: 0.5em;">가져오기</VBtn>
            </VCol>
            <VCol md="7" cols="6">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                        prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                        single-line :rules="[requiredValidatorV2(props.item.pg_id, 'PG사')]" />                
            </VCol>
        </VRow>
        <!-- 👉 PG 구간 -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="6">
                <span>구간</span>
            </VCol>
            <VCol md="7">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id" :items="filterPgs"
                        prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name" item-value="id"
                        :hint="`${setFee(pss, props.item.ps_id)}`" persistent-hint single-line
                        :rules="[requiredValidatorV2(props.item.ps_id, '구간')]" />
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">
                정산일
            </VCol>
            <VCol md="7">
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.settle_type" :items="settle_types"
                        prepend-inner-icon="ic-outline-send-to-mobile" item-title="name" item-value="id"
                        :rules="[requiredValidatorV2(props.item.settle_type, '정산일')]" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="4">
                <span class="font-weight-bold">정산일</span>
            </VCol>
            <VCol md="7">
                {{ settle_types.find(obj => obj.id === props.item.settle_type)?.name }}
            </VCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <VCol md="5" cols="4">
                이체 수수료
            </VCol>
            <VCol md="7">
                <VTextField v-model="props.item.settle_fee" type="number" suffix="₩"
                        :rules="[requiredValidatorV2(props.item.settle_fee, '이체 수수료')]" />
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="4">
                <span class="font-weight-bold">이체 수수료</span>
            </VCol>
            <VCol md="7">
                {{ props.item.settle_fee }} ₩
            </VCol>
        </VRow>
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
