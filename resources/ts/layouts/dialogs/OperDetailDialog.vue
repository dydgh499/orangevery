<script setup lang="ts">
import { OperatorHistory } from '@/views/types'
import { history_types } from '@/views/services/operator-histories/useStore'

const visible = ref(false)
const history = ref(<OperatorHistory>({}))
const show = (_history: OperatorHistory) => {
    visible.value = true
    history.value = _history
}

const title = computed(() => {
    if(history.value)
        return history.value.nick_name+"님 활동이력"
    else
        return ""
})

const content = computed(() => {
    if(history.value)
    {
        let type = history_types.find(obj => obj.id === history.value.history_type)?.title
        if(history.value.history_type != 3,4)
            type +="("+history.value.history_target+")"
        return type
    }
    else
        return ""
})
defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="800">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard :title="title">
            <b>{{ content }}</b>
            <br>
            <b>{{ history.created_at }}</b>
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>적용대상</th>
                            <th class='list-square'>적용값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(hist, key) in history.history_detail" :key="key">
                            <th class='list-square'>{{ key }}</th>
                            <td class='list-square'>{{ hist[key] }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
