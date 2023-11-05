<script lang="ts" setup>
import corp from '@corp'

const visible = ref(false)
const show = () => {
    visible.value = true
};

defineExpose({
    show
});
</script>
<template>
    <VSnackbar v-model="visible" vertical transaction="fade-transition" :timeout="60000" variant="flat">
        <div class="pwa-container">
            <img :src="corp.logo_img || ''" width="100" height="100">
           <span style="font-weight: bold;">바로가기를 생성할까요?</span>
           <br>
           <span>홈화면에 전산을 빠르게 접근할 수 있는 바로가기가 생성됩니다.</span>
        </div>
        <template #actions>
            <VBtn color="success" @click="visible = false">
                생성하기
            </VBtn>
            <VBtn color="error" @click="visible = false">
                한달간 안보기
            </VBtn>
        </template>
    </VSnackbar>
</template>
<style scoped>
.pwa-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

:deep(.v-snackbar__content) {
  inline-size: 100%;
}
</style>
