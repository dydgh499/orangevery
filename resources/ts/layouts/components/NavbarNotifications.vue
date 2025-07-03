<script lang="ts" setup>
import DetailWorkStatusDialog from '@/layouts/dialogs/utils/DetailWorkStatusDialog.vue'
//import SecureReportDialog from '@/layouts/dialogs/utils/SecureReportDialog.vue'
import Notifications from '@core/components/Notifications.vue'
import type { Notification } from '@layouts/types'

const errorHandler = <any>(inject('$errorHandler'))
const notifications = ref(<Notification[]>[])
const detailWorkStatusDialog = ref()
//const secureReportDialog = ref(false)
const is_login = ref(true)

// provide('detailWorkStatusDialog', detailWorkStatusDialog)
</script>

<template>
    <div class="me-2">
        <Notifications :notifications="notifications" />
        <template v-if="is_login">
            <Suspense>
                <!--
                <SecureReportDialog ref="secureReportDialog"/>
                -->
            </Suspense>
            <DetailWorkStatusDialog ref="detailWorkStatusDialog"/>
        </template>
    </div>
</template>
