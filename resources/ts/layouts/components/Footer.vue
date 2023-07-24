<script setup lang="ts">
import corp from '@corp'

</script>
<template>
    <VRow>
        <VCol :cols="12">
            <span style="padding: 0.2em;"><b>회사명 </b>{{ corp.company_name }}</span>
            <span style="padding: 0.2em;"><b> 대표 </b>{{ corp.ceo_name }}</span>
            <span style="padding: 0.2em;"><b> 주소 </b>{{ corp.addr }}</span>
        </VCol>
    </VRow>
    <VRow>
        <VCol :cols="12">
            <span style="padding: 0.2em;"><b> 사업자 등록번호 </b>{{ corp.business_num }}</span>
            <span style="padding: 0.2em;"><b> 전화 </b>{{ corp.phone_num }}</span>
            <span style="padding: 0.2em;"><b> 팩스 </b>{{ corp.fax_num }}</span><br>
        </VCol>
    </VRow>
</template>
