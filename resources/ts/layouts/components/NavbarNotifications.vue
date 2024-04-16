<script lang="ts" setup>
import CollectWithdrawDangerDialog from '@/layouts/dialogs/transactions/CollectWithdrawDangerDialog.vue'
import { axios } from '@axios'
import Notifications from '@core/components/Notifications.vue'
import type { Notification } from '@layouts/types'

const errorHandler = <any>(inject('$errorHandler'))
const notifications = ref(<Notification[]>[])
const collectWithdrawDangerDialog = ref()

axios.get('/api/v1/manager/posts/recent')
    .then(r => {
        notifications.value = r.data.content as Notification[]
    })
    .catch(e => {
        const r = errorHandler(e)
    })
</script>

<template>
    <Notifications :notifications="notifications" />
    <Suspense>
        <CollectWithdrawDangerDialog ref="collectWithdrawDangerDialog"/>
    </Suspense>
</template>
