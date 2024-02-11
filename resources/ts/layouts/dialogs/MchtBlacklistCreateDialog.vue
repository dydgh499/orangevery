<script lang="ts" setup>
import { block_types } from '@/views/services/mcht-blacklists/useStore'
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
    block_type: 0,
    block_content: '',
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
                                    <label>차단 타입</label>
                                </VCol>
                                <VCol md="8">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="mcht_blacklist.block_type"
                                        :items="block_types" single-line item-title="title" item-value="id" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters>
                                <VCol>
                                    <label>차단 내용</label>
                                </VCol>
                                <VCol md="8">
                                    <VTextField v-model="mcht_blacklist.block_content" placeholder='차단내용을 적어주세요.' />
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
