<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters'
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue'
import { useRequestStore } from '@/views/request'
import type { BillKeyCreate, PayModule } from '@/views/types'
import { requiredValidatorV2, lengthValidatorV2 } from '@validators'
import { HistoryTargetNames } from '@core/enums'
import { VForm } from 'vuetify/components'
import { useStore } from './useStore'

interface Props {
    item: BillKeyCreate,
}
const vForm = ref<VForm>()
const props = defineProps<Props>()
const activityHistoryTargetDialog = <any>(inject('activityHistoryTargetDialog'))
const {
    phone_num_format,
    card_num_format,
    yymm_format,
    card_num,
    yymm,
    phone_num,
    formatPhoneNum,
    formatCardNum,
    formatYYmm,
} = inputFormater()

phone_num.value = props.item.buyer_phone
card_num.value = props.item.card_num
yymm.value = props.item.yymm

const { pay_modules } = useStore()
const { update, remove } = useRequestStore()

const filterPayModule = computed(() => {
    return pay_modules.filter(item => {
        return item.module_type === 4
    })
})

watchEffect(() => {
    props.item.buyer_phone = phone_num.value
    props.item.card_num = card_num.value
    props.item.yymm = yymm.value
})
</script>
<template>
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed :title="props.item.nick_name">
            <VDivider />
            <VForm ref="vForm">
                <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
                    <VCol cols="12">
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                빌키 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>카드별칭</template>
                                <template #l_input>
                                    <VTextField 
                                        v-model="props.item.nick_name"
                                        variant="underlined"
                                        placeholder="카드별칭을 입력해주세요" :rules="[requiredValidatorV2(props.item.nick_name, '별칭')]" 
                                        prepend-icon="ic:round-drive-file-rename-outline" />
                                </template>
                                <template #r_name>결제모듈</template>
                                <template #r_input>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.pmod_id"
                                        variant="underlined"
                                        :items="filterPayModule" prepend-inner-icon="ic-outline-send-to-mobile"
                                        label="결제모듈" item-title="note"
                                        :rules="[requiredValidatorV2(props.item.pmod_id, '결제모듈')]"
                                        item-value="id" />
                                </template>
                            </CreateHalfVColV2>

                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>소유자명</template>
                                <template #l_input>
                                    <VTextField 
                                        v-model="props.item.buyer_name"
                                        variant="underlined"
                                        placeholder="소유자 명을 입력해주세요" :rules="[requiredValidatorV2(props.item.buyer_name, '구매자명')]" 
                                        prepend-icon="tabler-user" />
                                </template>
                                <template #r_name>연락처</template>
                                <template #r_input>
                                    <VTextField
                                        v-model="phone_num_format"
                                        @input="formatPhoneNum"
                                        variant="underlined"
                                        prepend-icon="tabler-device-mobile" placeholder="소유자 연락처를 입력해주세요"
                                        :rules="[requiredValidatorV2(props.item.buyer_phone, '구매자 연락처')]" 
                                        />
                                </template>
                            </CreateHalfVColV2>
                        </VCardItem>
                        <VCardItem>
                            <VCardTitle style="margin-bottom: 1em;">
                                카드 정보
                            </VCardTitle>
                            <CreateHalfVColV2 :mdl="5" :mdr="7">
                                <template #l_name>카드번호</template>
                                <template #l_input>
                                    <VTextField
                                        v-if="props.item.id === 0"
                                        variant="underlined"
                                        v-model="card_num_format"
                                        @input="formatCardNum"
                                        prepend-icon="tabler:credit-card"
                                        placeholder="카드번호를 입력해주세요"
                                        :rules="[requiredValidatorV2(props.item.card_num, '카드번호')]"
                                        maxlength="22"
                                        autocomplete="cc-number"
                                    />
                                    <b v-else>
                                        {{ `(${props.item.issuer}) ${props.item.card_num}` }}
                                    </b>
                                </template>
                                <template #r_name>
                                    <span v-if="props.item.id === 0">비밀번호</span>
                                </template>
                                <template #r_input>
                                    <div 
                                        v-if="props.item.id === 0"
                                        style="display: inline-flex; align-items: center;">
                                        <VTextField
                                        v-model="props.item.card_pw"
                                        type="password"
                                        prepend-icon="tabler:paywall"
                                        variant="underlined"
                                        persistent-placeholder
                                        maxlength="2"
                                        style="max-width: 4em;"
                                        >
                                        <VTooltip activator="parent" location="top">
                                            카드비밀번호 앞 4자리 중 2자리를 입력해주세요.
                                        </VTooltip>
                                        </VTextField>
                                        <b style="margin-left: 0.5em;">**</b>
                                    </div>
                                </template>
                            </CreateHalfVColV2>
                            <template v-if="props.item.id === 0">
                                <CreateHalfVColV2 :mdl="5" :mdr="7">
                                    <template #l_name>유효기간</template>
                                    <template #l_input>
                                        <VTextField
                                            v-model="yymm_format"
                                            @input="formatYYmm"
                                            placeholder="MM/YY"
                                            variant="underlined"
                                            prepend-icon="ri:pass-expired-line"
                                            :rules="[requiredValidatorV2(props.item.yymm, '유효기간'), lengthValidatorV2(props.item.yymm, 4)]"
                                            maxlength="5"
                                            style="min-inline-size: 11em;"
                                        />
                                            <VTooltip activator="parent" location="top">
                                            카드의 유효기간 4자리를 입력해주세요.<br />(MM/YY:0324)
                                            </VTooltip>
                                    </template>
                                    <template #r_name>본인확인</template>
                                    <template #r_input>
                                        <VTextField
                                            v-model="props.item.auth_num"
                                            type="number"
                                            maxlength="10"
                                            variant="underlined"
                                            prepend-icon="teenyicons:id-outline"
                                            placeholder="생년월일6자리(사업자번호)"
                                            persistent-placeholder
                                            counter
                                        />
                                            <VTooltip activator="parent" location="top">
                                            개인카드일 경우 카드소유주의 생년월일6자리 입력,<br />법인카드의 경우 사업자등록번호를 입력해주세요.
                                            </VTooltip>
                                    </template>
                                </CreateHalfVColV2>
                            </template>
                            <VRow>
                                <VCol class="pt-10" style="text-align: end;">
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: auto;"
                                        color="secondary" 
                                        variant="tonal"
                                        @click="activityHistoryTargetDialog.show(props.item.id, HistoryTargetNames['pays/bill-keys'])">
                                        이력
                                        <VIcon end size="20" icon="tabler:history" />
                                    </VBtn>   
                                    <VBtn 
                                        style="margin-left: 1em;"
                                        @click="update('/pays/bill-keys', props.item, vForm, false)">
                                        {{ props.item.id == 0 ? "추가" : "수정" }}
                                        <VIcon end size="20" icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn v-if="props.item.id"
                                        style="margin-left: 1em;"
                                        color="error"
                                        @click="remove('/pays/bill-keys', props.item, false)">
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
