<script lang="ts" setup>
import { useRequestStore } from '@/views/request';
import BrandIdentityCard from '@/views/services/brands/identity-auth-infos/BrandIdentityCard.vue';
import type { IdentityAuthInfo } from '@/views/types';

interface Props {
    item: IdentityAuthInfo[],
}

const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()

const addNewBrandIdentity = () => {
    props.item.push(<IdentityAuthInfo>({
        id: 0,
        identitiy_auth_type: 0,
        corp_code: '',
        api_key: '',
        sub_key: '',
        enc_key: '',
    }))
}
watchEffect(() => {
    setNullRemove(props.item)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewBrandIdentity">
                인증방식 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow style="margin-top: 1em;">
        <BrandIdentityCard v-for="identity_auth_info in props.item" :key="identity_auth_info.id" :item="identity_auth_info"/>
    </VRow>
</template>
