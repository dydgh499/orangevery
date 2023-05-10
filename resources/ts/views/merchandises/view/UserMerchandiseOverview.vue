<script lang="ts" setup>
  const withdrawalType = ['d+1', 'd+3', 'd+5', 'd+7']
  const salesSegmentType = ['영세', '중소1', '중소2', '중소3']
</script>

<template>
        <!-- 👉 가맹점정보 -->
        <VCol cols="12">
        <VCard>
          <VCardItem>
            <VCardTitle>가맹점 정보</VCardTitle>
            <br>
            <VRow>
              <VCol cols="12">
                <VSelect :items="withdrawalType" label="출금 타입 선택"/>
              </VCol>
            </VRow>
          </VCardItem>
        </VCard>
      </VCol>
</template>
