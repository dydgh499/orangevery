<script setup lang="ts">

import { nullValidator } from '@validators'
import type { FreeOption, PaidOption, AuthOption } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import corp from '@corp';

interface Props {
    item: {
        free: FreeOption,
        paid: PaidOption,
        auth: AuthOption,
    },
}
const props = defineProps<Props>()

</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="명칭설정" :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>                        
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>개발사</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.dev_name"
                                    prepend-inner-icon="ph:share-network" placeholder="개발사 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 6</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales5_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 5</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales4_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 4</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales3_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 3</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales2_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 2</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales1_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>영업자 등급 1</span></template>
                            <template #input>
                                <VTextField v-model="props.item.auth.levels.sales0_name"
                                    prepend-inner-icon="ph:share-network" placeholder="사용할 등급 명칭을 입력해주세요"
                                    persistent-placeholder :rules="[nullValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle>
                        <BaseQuestionTooltip location="top" text="권한설정" :content="`${corp.pv_options.auth.levels.dev_name}만 확인 가능한 정보입니다.`">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.dev_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.dev_use" @update:radio="item.auth.levels.dev_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales5_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales5_use" @update:radio="item.auth.levels.sales5_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales4_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales4_use" @update:radio="item.auth.levels.sales4_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales3_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales3_use" @update:radio="item.auth.levels.sales3_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales2_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales2_use" @update:radio="item.auth.levels.sales2_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales1_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales1_use" @update:radio="item.auth.levels.sales1_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>

                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="3" :mdr="9">
                            <template #name><span>{{ item.auth.levels.sales0_name }} 사용여부</span></template>
                            <template #input>
                                <BooleanRadio :radio="item.auth.levels.sales0_use" @update:radio="item.auth.levels.sales0_use = $event">
                                    <template #true>사용</template>
                                    <template #false>미사용</template>
                                </BooleanRadio>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
