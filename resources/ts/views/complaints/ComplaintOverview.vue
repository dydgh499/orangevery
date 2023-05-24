<script lang="ts" setup>
import { axios } from '@axios';
import { requiredValidator, nullValidator } from '@validators';
import type { Complaint } from '@/views/types'
import CreateHalfVCol from '@/views/utils/CreateHalfVCol.vue';
import { useStore } from '@/views/services/pay-gateways/useStore';

interface Props {
    item: Complaint,
}
interface Options {
    id: number,
    title: string,
}

const props = defineProps<Props>()
const { pgs } = useStore()


const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = inject('$errorHandler');

const types = <Options[]>([
    { id: 0, title: "공지사항" },
    { id: 2, title: "FAQ" },
    { id: 2, title: "1:1 문의" },
])

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>고객정보</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol>
                            <template #name>고객명</template>
                            <template #input>
                                <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                    placeholder="고객명을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol>
                            <template #name>연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                    placeholder="연락처를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol>
                            <template #name>수기작성성함</template>
                            <template #input>
                                <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                    placeholder="수기작성성함을 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol>
                            <template #name>수기작성연락처</template>
                            <template #input>
                                <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                    placeholder="수기작성연락처를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <VCol>
                            <VTextarea v-model="props.item.note" counter label="메모사항"
                                prepend-inner-icon="twemoji-spiral-notepad" />
                        </VCol>

                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 계약정보 -->
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>민원정보</VCardTitle>
                    <CreateHalfVCol>
                        <template #name>TID</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>민원타입</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type" :items="types"
                                prepend-inner-icon="fxemoji-notepage" label="민원 타입 선택" item-title="title" item-value="id"
                                single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>승인일</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>승인번호</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>발급사</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>PG사</template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_nm" item-value="id"
                                single-line />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol>
                        <template #name>entry_path</template>
                        <template #input>
                            <VTextField v-model="props.item.tid" prepend-inner-icon="tabler-building-store"
                                placeholder="TID를 입력해주세요" persistent-placeholder :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
