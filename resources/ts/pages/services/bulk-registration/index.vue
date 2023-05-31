
<script setup lang="ts">
import corp from '@corp';
import type { Tab } from '@/views/types'

import SalesforceRegister from '@/views/services/bulk-registration/SalesforceRegister.vue';
import MerchandiseRegister from '@/views/services/bulk-registration/MerchandiseRegister.vue';
import PayModuleRegister from '@/views/services/bulk-registration/PayModuleRegister.vue';

import CreateForm from '@/layouts/utils/CreateForm.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';

const tabs = <Tab[]>([
    { icon: 'tabler-user', title: '영업점 등록' },
    { icon: 'tabler-building-store', title: '가맹점 등록' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈 등록' },
])
const brand_id = ref<number>(corp.id) //brand_id
const formatDownload = () => {

}
const formatUpload = () => {

}
const validate = () => {

}

</script>
<template>
    <section>
        <CreateForm :id="0" :path="`services/bulk-registration`" :tabs="tabs" :item="[]">
            <template #additional_explaination>
                <VCard style='margin-top: 1em;'>
                    <VRow style="padding: 1em;">
                        <CreateHalfVCol :mdl="12" :mdr="0">
                            <template #name>
                                <div style="font-weight: bold;">
                                    사용방법
                                </div>
                                <br>
                                <div style="line-height: 40px;">
                                    오른쪽 하단
                                    <VBtn color="secondary" variant="tonal" aria-readonly="" size="small" >
                                        양식 다운로드
                                        <VIcon end icon="uiw-file-excel" />
                                    </VBtn>
                                     버튼을 클릭하여 양식을 다운받은 후 주의사항을 확인하고
                                     <VBtn type="button"  aria-readonly="" size="small" >
                                        양식 업로드
                                        <VIcon end icon="uiw-file-excel" />
                                    </VBtn>
                                    버튼을 통해 대량등록을 진행합니다.
                                    <br>
                                    양식 업로드를 통해 정상적인 값으로 검증되면 
                                    <VBtn type="button" aria-readonly="" size="small">
                                        등록
                                        <VIcon end icon="tabler-checkbox" />
                                    </VBtn>   
                                    버튼이 활성화 됩니다.
                                </div>
                            </template>
                            <template #input>
                            </template>
                        </CreateHalfVCol>
                    </VRow>                    
                </VCard>
            </template>
            <template #view>
                <VWindowItem>
                    <SalesforceRegister :items="[]">
                    </SalesforceRegister>
                </VWindowItem>
                <VWindowItem>
                    <MerchandiseRegister :items="[]">
                    </MerchandiseRegister>
                </VWindowItem>
                <VWindowItem>
                    <PayModuleRegister :items="[]">
                    </PayModuleRegister>
                </VWindowItem>
            </template>
        </CreateForm>
        <VCard style="margin-top: 1em;" slot="button">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="">
                등록
                <VIcon end icon="tabler-checkbox" />
            </VBtn>            
            <VBtn type="button" @click="formatUpload()">
                양식 업로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
            <VBtn color="secondary" variant="tonal" @click="formatDownload()">
                양식 다운로드
                <VIcon end icon="uiw-file-excel" />
            </VBtn>
        </VCol>
    </VCard>
    </section>
</template>
