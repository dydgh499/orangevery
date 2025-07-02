<script lang="ts" setup>
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue'
import { useRequestStore } from '@/views/request'
import type { PayModule } from '@/views/types'
import { requiredValidatorV2 } from '@validators'
import { HistoryTargetNames } from '@core/enums'
import { VForm } from 'vuetify/components'
import { useStore, module_types } from './useStore'

interface Props {
    item: PayModule,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))


const { update, remove } = useRequestStore()
const { pgs, pss, psFilter, setFee } = useStore()

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
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed :title="props.item.note">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                결제모듈 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>별칭</template>
                                <template #l_input>
                                    <VTextField 
                                        v-model="item.note" 
                                        label="결제모듈 별칭" 
                                        variant="underlined"
                                        :rules="[requiredValidatorV2(item.note, '별칭')]"
                                        prepend-inner-icon="twemoji-spiral-notepad"
                                    />
                                </template>
                                <template #r_name>타입</template>
                                <template #r_input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" 
                                        v-model="item.module_type"
                                        @update:modelValue="onModuleTypeChange" 
                                        :items="module_types"
                                        prepend-inner-icon="ic-outline-send-to-mobile" 
                                        variant="underlined"
                                        label="결제모듈 타입" 
                                        item-title="title"
                                        item-value="id" 
                                        :rules="[requiredValidatorV2(item.module_type, '결제모듈 타입')]" 
                                    />
                                </template>
                            </CreateHalfVColV2>                         
                        </VCardItem>
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                상위사 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>결제대행사</template>
                                <template #l_input>
                                    <VSelect 
                                        :menu-props="{ maxHeight: 400 }" 
                                        v-model="item.pg_id" 
                                        :items="pgs"
                                        variant="underlined"
                                        prepend-inner-icon="ph-buildings" 
                                        label="원천사 선택" item-title="pg_name" item-value="id"
                                        :rules="[requiredValidatorV2(item.pg_id, '결제대행사')]" />    
                                </template>
                                <template #r_name>수수료율</template>
                                <template #r_input>
                                <VSelect 
                                        :menu-props="{ maxHeight: 400 }"
                                        v-model="item.ps_id" 
                                        :items="filterPgs"
                                        variant="underlined"
                                        prepend-inner-icon="mdi-vector-intersection" 
                                        label="수수료율 선택" item-title="name" item-value="id"
                                        :hint="`${setFee(pss, item.ps_id)}`" persistent-hint
                                        :rules="[requiredValidatorV2(item.ps_id, '수수료율')]" />
                                </template>
                            </CreateHalfVColV2>
                             <VRow v-if="item.module_type == 1">
                                <VCol md="12" cols=12 style="margin-top: auto; margin-bottom: auto;">
                                    <VRow style="align-items: center;">
                                        <VCol :md="4">
                                            수기결제 타입
                                        </VCol>
                                        <VCol :md="8">
                                                <BooleanRadio :radio="item.is_old_auth" @update:radio="item.is_old_auth = $event">
                                                    <template #true>구인증</template>
                                                    <template #false>비인증</template>
                                                </BooleanRadio>       
                                        </VCol>
                                    </VRow>
                                </VCol>
                            </VRow>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>API KEY</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="item.api_key" 
                                            prepend-inner-icon="ic-baseline-vpn-key"
                                            variant="underlined"
                                            placeholder="API KEY 입력" 
                                            persistent-placeholder 
                                            maxlength="100" 
                                            label="API KEY"
                                        />
                                </template>
                                <template #r_name>SUB KEY</template>
                                <template #r_input>
                                <VTextField type="text" v-model="item.sub_key" prepend-inner-icon="ic-sharp-key"
                                    variant="underlined"
                                    placeholder="SUB KEY 입력" persistent-placeholder maxlength="100" label="SUB KEY"/>
                                </template>
                            </CreateHalfVColV2>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>MID</template>
                                <template #l_input>
                                    <VTextField type="text" v-model="item.mid" prepend-inner-icon="tabler-user"
                                        variant="underlined"
                                        placeholder="MID 입력" persistent-placeholder label="MID"
                                        maxlength="50"/>
                                </template>
                                <template #r_name>TID</template>
                                <template #r_input>
                                    <VTextField type="text" v-model="item.tid" prepend-inner-icon="jam-key-f"
                                        variant="underlined"
                                        placeholder="TID 입력" persistent-placeholder label="TID"
                                        maxlength="50"/>
                                </template>
                            </CreateHalfVColV2>
                            <VRow>
                                <VCol class="pt-10" style="text-align: end;">
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: auto;"
                                        color="secondary" 
                                        variant="tonal"
                                        @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['pays/pay-modules'])">
                                        이력
                                        <VIcon end size="20" icon="tabler:history" />
                                    </VBtn>  
                                    <VBtn 
                                        style="margin-left: 1em;"
                                        @click="update('/pays/pay-modules', props.item, vForm, false)">
                                        {{ props.item.id == 0 ? "추가" : "수정" }}
                                        <VIcon end size="20" icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: 1em;"
                                        color="error"
                                        @click="remove('/pays/pay-modules', props.item, false)">
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
                        </VCardItem>
                    </VCol>
                </div>
            </VForm>
        </AppCardActions>
    </VCol>
</template>
