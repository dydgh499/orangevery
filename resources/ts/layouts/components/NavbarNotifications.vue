<script lang="ts" setup>
import Notifications from '@core/components/Notifications.vue'
import type { Notification } from '@layouts/types'
import { axios } from '@axios'

const errorHandler = <any>(inject('$errorHandler'))
const notifications = ref(<Notification[]>[])

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
</template>
