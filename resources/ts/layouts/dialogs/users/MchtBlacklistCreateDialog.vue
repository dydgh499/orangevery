<script lang="ts" setup>
import type { MchtBlacklist } from '@/views/types'
import { useRequestStore } from '@/views/request'
import { VForm } from 'vuetify/components'

const { update } = useRequestStore()
const store = <any>(inject('store'))

const visible = ref(false)
const vForm = ref<VForm>()

let resolveCallback: (isAgreed: boolean) => void;

const mcht_blacklist = ref<MchtBlacklist>({
    id: 0,
    mcht_name: '',
    nick_name: '',
    phone_num: '',
    business_num: '',
    resident_num: '',
    addr: '',
    block_reason: '',
})

const show = (_mcht_blacklist: MchtBlacklist): Promise<boolean> => {
    mcht_blacklist.value = _mcht_blacklist
    visible.value = true

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = async () => {
    const res = await update(`/services/mcht-blacklists`, mcht_blacklist.value, vForm.value, false)
    store.setTable()
    visible.value = false;
    resolveCallback(true); // 동의 버튼 누름
};

const onCancel = () => {
    visible.value = false;
    resolveCallback(false); // 취소 버튼 누름
};

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="900">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard :title="'블랙리스트 ' + (mcht_blacklist.id ? '수정' : '추가')">
            <VCardText>
                <VForm ref="vForm">
                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>가맹점 상호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.mcht_name" prepend-inner-icon="tabler-building-store"
                                        placeholder="상호를 입력해주세요" persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>대표자명</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.nick_name" prepend-inner-icon="tabler-user"
                                        placeholder="대표자명을 입력해주세요" persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>

                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>사업자등록번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.business_num" prepend-inner-icon="ic-outline-business-center"
                                     placeholder="123-12-12345" persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>주민등록번호</label> 
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.resident_num" prepend-inner-icon="carbon-identification"
                                        placeholder="사업자번호를 입력해주세요" persistent-placeholder />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>

                    <VRow class="pt-3">
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters>
                                <VCol>
                                    <label>전화번호</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.phone_num" prepend-inner-icon="tabler-device-mobile"
                                        placeholder="010-0000-0000" persistent-placeholder maxlength="13"/>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>주소</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField id="addressHorizontalIcons" v-model="mcht_blacklist.addr"
                                        prepend-inner-icon="tabler-map-pin" placeholder="주소 입력" persistent-placeholder
                                        maxlength="100" />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VTextarea v-model="mcht_blacklist.block_reason" counter placeholder='차단사유를 적어주세요.'
                        prepend-inner-icon="twemoji-spiral-notepad" class="pt-3" auto-grow/>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="onCancel">
                    취소
                </VBtn>
                <VBtn @click="onAgree">
                    확인
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
