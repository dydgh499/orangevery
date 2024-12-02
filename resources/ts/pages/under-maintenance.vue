<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant';
import miscMaskDark from '@images/pages/misc-mask-dark.png';
import miscMaskLight from '@images/pages/misc-mask-light.png';
import miscUnderMaintenance from '@images/pages/misc-under-maintenance.png';

const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)
</script>

<template>
    <div class="misc-wrapper" style="padding-top: 6em;">
        <div class="text-center mb-12">
            <h4 class="text-h4 font-weight-medium mb-3">
                유지 보수 중! 🚧
            </h4>
            <p>불편을 끼쳐드려 죄송하지만 현재 일부 유지관리 작업을 진행 중입니다.</p>

            <VBtn to="/">
                Back to Home
            </VBtn>
        </div>

        <!-- 👉 Image -->
        <div class="misc-avatar w-100 text-center">
            <VImg :src="miscUnderMaintenance" alt="Coming Soon" :max-width="550" class="mx-auto" />
        </div>

        <VImg :src="authThemeMask" class="misc-footer-img d-none d-md-block" />
    </div>
</template>

<route lang="yaml">
    meta:
      layout: blank
      action: read
      subject: Auth
</route>
