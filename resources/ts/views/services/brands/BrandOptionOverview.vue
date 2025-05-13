<script setup lang="ts">
import CreateHalfVColV2 from '@/layouts/utils/CreateHalfVColV2.vue';
import type { AuthOption, FreeOption, P2pAppOption, PaidOption } from '@/views/types';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
        p2p: P2pAppOption,
    },
}
const props = defineProps<Props>()

</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" :md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>대량출금 옵션</VCardTitle>
                    <CreateHalfVColV2 :mdl="8" :mdr="4">
                        <template #l_name>계좌번호 중복검사 사용</template>
                        <template #l_input>
                            <VSwitch hide-details v-model="props.item.free.use_account_number_duplicate" color="primary" />
                        </template>
                    </CreateHalfVColV2>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
