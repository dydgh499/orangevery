<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { PayModule } from '@/views/types'
import { fin_trx_delays } from '@/views/merchandises/pay-modules/useStore'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { axios, isAbleModiy } from '@axios'
import corp from '@corp'

interface Props {
    item: PayModule,
}

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const midCreateDlg = <any>(inject('midCreateDlg'))

const { pgs, finance_vans } = useStore()

const tidCreate = async() => {
    if(await alert.value.show('정말 TID를 신규 발급하시겠습니까?')) {
        try {
            const pg_type = pgs.find(obj => obj.id === props.item.pg_id)?.pg_type
            if(pg_type) {
                const r = await axios.post('/api/v1/manager/merchandises/pay-modules/tid-create', { pg_type : pg_type })
                props.item.tid = r.data.tid
                snackbar.value.show('성공하였습니다.<br>저장하시려면 추가버튼을 눌러주세요.', 'success')
            }
            else
                snackbar.value.show('PG사를 먼저 선택해주세요.', 'warning')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const midCreate = async() => {
    const mid_code = await midCreateDlg.value.show()
    if(mid_code) {
        const r = await axios.post('/api/v1/manager/merchandises/pay-modules/mid-create', {mid_code: mid_code})    
        if(r.status == 200)
            props.item.mid = r.data.mid
        else
            snackbar.value.error(r.data.message, 'error')
    }
}
const payKeyCreate = async() => {
    if(await alert.value.show('정말 결제 KEY를 신규 발급하시겠습니까?<br><br><b>이전 결제키는 더이상 사용할 수 없으니 주의하시기바랍니다.</b>')) {
        try {
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/pay-key-create', {id: props.item.id})
            props.item.pay_key = r.data.pay_key
            snackbar.value.show('결제 KEY가 업데이트 되었습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

</script>
<template>
    <VCardItem>
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>API KEY(license)</template>
                <template #input>
                    <VTextField type="text" v-model="props.item.api_key" prepend-inner-icon="ic-baseline-vpn-key"
                        placeholder="API KEY 입력" persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>SUB KEY(iv)</template>
                <template #input>
                    <VTextField type="text" v-model="props.item.sub_key" prepend-inner-icon="ic-sharp-key"
                        placeholder="SUB KEY 입력" persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id) && corp.pv_options.paid.use_pmid">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>PMID</template>
                <template #input>
                    <VTextField type="text" v-model="props.item.p_mid" prepend-inner-icon="tabler-user"
                        placeholder="PMID 입력" persistent-placeholder />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>MID</template>
                <template #input>
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                            placeholder="MID 입력" persistent-placeholder />
                        <VBtn type="button" variant="tonal" v-if="isAbleModiy(props.item.id) && props.item.id == 0 && corp.pv_options.paid.use_mid_create"
                            @click="midCreate()">
                            {{ "생성" }}
                            <VIcon end icon="material-symbols:add-to-home-screen" />
                        </VBtn>
                    </div>
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">MID</span></template>
                <template #input>
                    {{ props.item.mid }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <!-- 👉 TID -->
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>TID</template>
                <template #input>
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                            placeholder="TID 입력" persistent-placeholder />
                        <VBtn type="button" variant="tonal" v-if="props.item.id == 0 && corp.pv_options.paid.use_tid_create" @click="tidCreate()">
                            {{ "생성" }}
                            <VIcon end icon="material-symbols:add-to-home-screen" />
                        </VBtn>
                    </div>
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">TID</span></template>
                <template #input>
                    {{ props.item.tid }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>계약 시작일</template>
                <template #input>
                    <VTextField type="date" v-model="props.item.contract_s_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" label="시작일 입력" single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">계약 시작일</span></template>
                <template #input>
                    {{ props.item.contract_s_dt }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="isAbleModiy(props.item.id)">
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name>계약 종료일</template>
                <template #input>
                    <VTextField type="date" v-model="props.item.contract_e_dt"
                        prepend-inner-icon="ic-baseline-calendar-today" label="종료일 입력" single-line />
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-else>
            <CreateHalfVCol :mdl="5" :mdr="7">
                <template #name><span class="font-weight-bold">계약 종료일</span></template>
                <template #input>
                    {{ props.item.contract_e_dt }}
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow v-if="props.item.id != 0 && props.item.module_type != 0 && corp.pv_options.paid.use_online_pay">
            <CreateHalfVCol :mdl="5" :mdr="7" v-if="isAbleModiy(props.item.id)">
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'결제 KEY'"
                        :content="'해당 키를 통해 온라인 결제를 발생시킬 수 있습니다.<br>키를 복사하려면 입력필드에서 더블클릭하세요.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <div style="display: flex; flex-direction: row; justify-content: space-between;">
                        <VTextField type="text" v-model="props.item.pay_key" prepend-inner-icon="ic-baseline-vpn-key"
                            persistent-placeholder :disabled="true" />

                        <VBtn type="button" variant="tonal" @click="payKeyCreate()">
                            {{ "발급" }}
                            <VIcon end icon="material-symbols:add-to-home-screen" />
                        </VBtn>
                    </div>
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="5" :mdr="7" v-else>
                <template #name>
                    <BaseQuestionTooltip :location="'top'" :text="'결제 KEY'" :content="'드래그하여 확인할 수 있습니다.'">
                    </BaseQuestionTooltip>
                </template>
                <template #input>
                    <span style="background-color: rgba(var(--v-theme-on-surface));">{{ props.item.pay_key }}</span>
                </template>
            </CreateHalfVCol>
        </VRow>
        <template v-if="isAbleModiy(props.item.id) && corp.pv_options.paid.use_realtime_deposit">
            <VDivider style="margin: 1em 0;" />
            <VRow>
                <CreateHalfVCol :mdl="6" :mdr="6">
                    <template #name>실시간 사용여부</template>
                    <template #input>
                        <BooleanRadio :radio="props.item.use_realtime_deposit"
                            @update:radio="props.item.use_realtime_deposit = $event">
                            <template #true>사용</template>
                            <template #false>미사용</template>
                        </BooleanRadio>
                    </template>
                </CreateHalfVCol>
            </VRow>
            <VRow>
                <CreateHalfVCol :mdl="5" :mdr="7">
                    <template #name>이체 모듈 타입</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_id" :items="finance_vans"
                            prepend-inner-icon="streamline-emojis:ant" label="모듈 타입 선택" item-title="nick_name"
                            item-value="id" single-line />
                    </template>
                </CreateHalfVCol>
            </VRow>
            <VRow>
                <CreateHalfVCol :mdl="5" :mdr="7">
                    <template #name>이체 딜레이</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.fin_trx_delay"
                            :items="fin_trx_delays" prepend-inner-icon="streamline-emojis:bug" label="이체 딜레이 선택"
                            item-title="title" item-value="id" single-line />
                    </template>
                </CreateHalfVCol>
            </VRow>
        </template>
    </VCardItem>
</template>
