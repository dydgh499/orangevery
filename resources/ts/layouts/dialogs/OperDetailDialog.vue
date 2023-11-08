<script setup lang="ts">
import { OperatorHistory } from '@/views/types'
import { history_types } from '@/views/services/operator-histories/useStore'

const visible = ref(false)
const history = ref(<OperatorHistory>({}))
const store = <any>(inject('store'))

const show = (_history: any) => {
    delete _history.브랜드
    visible.value = true
    history.value = _history
}

const title = computed(() => {
    if(history.value)
        return history.value.nick_name+"님 활동이력"
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
            <VCardText>
                <b>
                    <div class="d-flex justify-space-between">
                        <h6 class="text-base font-weight-semibold me-3">
                            {{ history.history_target }}
                            {{ history.history_title ? " - "+history.history_title : ''}}
                            <VChip :color="store.getSelectIdColor(history.history_type)">
                                {{ history_types.find(history_type => history_type['id'] === history.history_type)?.title  }}
                            </VChip>   
                        </h6>
                        <span class="text-sm">
                            활동시간: {{ history.created_at }}
                        </span>
                    </div>
                </b>
                <br>
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
                            <td class='list-square'>{{ hist }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
