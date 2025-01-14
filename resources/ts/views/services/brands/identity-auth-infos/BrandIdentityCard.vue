<script setup lang="ts">

import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import { useRequestStore } from '@/views/request';
import { identity_auth_types } from '@/views/services/brands/useStore';
import type { IdentityAuthInfo } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

interface Props {
    item: IdentityAuthInfo,
}

const vForm = ref<VForm>()
const props = defineProps<Props>()
const { update, remove } = useRequestStore()

</script>
<template>
    <VCol cols="12" md="6">
        <AppCardActions action-collapsed>
            <VDivider />
            <VForm ref="vForm">
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">
                        본인인증 정보
                    </VCardTitle>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>본인인증 타입</template>
                        <template #l_input>                                
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.identitiy_auth_type"
                                density="compact" variant="outlined" :items="identity_auth_types"
                                eager item-title="title" item-value="id" :rules="[requiredValidatorV2(props.item.identitiy_auth_type, '본인인증 타입')]" />
                        </template>
                        <template #r_name>법인코드</template>
                        <template #r_input>
                            <VTextField type="text" v-model="props.item.corp_code"
                                prepend-inner-icon="ph:share-network" placeholder="법인코드 입력"
                                persistent-placeholder />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>API KEY</template>
                        <template #l_input>
                            <VTextField type="text" v-model="props.item.api_key"
                                prepend-inner-icon="ic-baseline-vpn-key" placeholder="API KEY 입력"
                                persistent-placeholder />
                        </template>
                        <template #r_name>SUB KEY</template>
                        <template #r_input>
                            <VTextField type="text" v-model="props.item.sub_key"
                                prepend-inner-icon="ic-baseline-vpn-key" placeholder="SUB KEY 입력"
                                persistent-placeholder />
                        </template>
                    </CreateHalfVColV2>
                    <CreateHalfVColV2 :mdl="5" :mdr="7">
                        <template #l_name>ENC KEY</template>
                        <template #l_input>                                
                            <VTextField type="text" v-model="props.item.enc_key"
                                prepend-inner-icon="ic-baseline-vpn-key" placeholder="ENC KEY 입력"
                                persistent-placeholder />
                        </template>
                        <template #r_name></template>
                        <template #r_input>
                        </template>
                    </CreateHalfVColV2>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;"
                                @click="update('/services/brands/identity-auth-infos', props.item, vForm, false)">
                                {{ props.item.id == 0 ? "추가" : "수정" }}
                                <VIcon end icon="tabler-pencil" />
                            </VBtn>
                            <VBtn type="button" color="error" v-if="props.item.id"
                                @click="remove('/services/brands/identity-auth-infos', props.item, false)">
                                삭제
                                <VIcon end icon="tabler-trash" />
                            </VBtn>
                            <VBtn type="button" color="warning" v-else @click="props.item.id = -1">
                                입력란 제거
                                <VIcon end icon="tabler-trash" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VForm>
        </AppCardActions>
    </VCol>
</template>
