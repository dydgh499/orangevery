<script lang="ts" setup>
import CollectWithdrawDangerDialog from '@/layouts/dialogs/transactions/CollectWithdrawDangerDialog.vue'
import { axios, getUserLevel } from '@axios'
import Notifications from '@core/components/Notifications.vue'
import corp from '@corp'
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
if(corp.pv_options.paid.use_collect_withdraw && getUserLevel() >= 35) {
    axios.get('/api/v1/manager/transactions/settle/collect-withdraws/dangers')
    .then(r => {
        //r.data
        if(r.data.length > 0) {
            collectWithdrawDangerDialog.value.show(r.data)
        }
    })
    .catch(e => {
        const r = errorHandler(e)
    })
}

</script>

<template>
    <Notifications :notifications="notifications" />
    <CollectWithdrawDangerDialog ref="collectWithdrawDangerDialog"/>
</template>
