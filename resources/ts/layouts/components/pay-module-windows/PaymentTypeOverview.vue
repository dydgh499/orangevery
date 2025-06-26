<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import corp from '@/plugins/corp'
import { module_types } from '@/views/services/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { getUserLevel, isAbleModiyV2 } from '@axios'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: PayModule,
    able_mcht_chanage: boolean,
}

const props = defineProps<Props>()
const snackbar = <any>(inject('snackbar'))

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
        <!--
        <VCardSubtitle>
            <VChip variant="outlined">소유 가맹점 정보</VChip>                
        </VCardSubtitle>
        <br>
        <VRow v-if="props.able_mcht_chanage && getUserLevel() >= 35">
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
        -->
        <VCardSubtitle>
            <VChip variant="outlined">결제모듈 정보</VChip>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'merchandises/pay-modules')">
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
            <VCol md="6" cols="12">
                <VTextField type="text" v-model="props.item.api_key" prepend-inner-icon="ic-baseline-vpn-key"
                        placeholder="API KEY 입력" persistent-placeholder maxlength="100" label="API KEY"/>
            </VCol>
            <VCol md="6">
                <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                        placeholder="SUB KEY 입력" persistent-placeholder maxlength="100" label="SUB KEY"/>
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
    </VCardItem>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
