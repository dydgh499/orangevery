<script setup lang="ts">
import { SettlesHistories } from '@/views/types'
import { axios } from '@axios'
import { getLevelByIndex } from '@/views/salesforces/useStore'
import { useSearchStore } from '@/views/transactions/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

interface Props {
    name: string,
    item: SettlesHistories,
    is_mcht: boolean
}

const props = defineProps<Props>()

const { printer } = useSearchStore()
const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const commonRequest = async (page: string, method: string) => {
    try {
        const type = props.is_mcht ? 'merchandises' : 'salesforces'
        const url = '/api/v1/manager/transactions/settle-histories/' + type + '/' + page
        const res = await axios({
            url: url,
            method: method
        })
        snackbar.value.show('성공하였습니다.', 'success')
        store.setTable()
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const deposit = async () => {
    const deposit_after_text = props.item.status ? '입금취소처리' : '입금처리'
    if (await alert.value.show('정말 ' + deposit_after_text + ' 하시겠습니까?')) {
        commonRequest(props.item.id.toString() + '/deposit', 'post')
    }
}

const cancel = async () => {
    if (await alert.value.show('정말 정산취소 하시겠습니까?')) {
        commonRequest(props.item.id.toString(), 'delete')
    }
}
const download = async () => {
    if (await alert.value.show('정산매출을 다운로드 하시겠습니까?')) {
        try {
            const params:Record<string, string|number> = {
                page: 1,
                page_size: 99999999,
            };
            if(props.is_mcht)
                params['mcht_settle_id'] = props.item.id
            else {
                const idx = getLevelByIndex(props.item.level)             
                params['sales'+idx+'_settle_id'] = props.item.id
            }

            const res = await axios.get('/api/v1/manager/transactions', { params: params })
            snackbar.value.show('엑셀 출력중 입니다..', 'success')
            printer(1, res.data.content)
            snackbar.value.show('성공하였습니다.', 'success')
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text" :id="`item-${props.item.id}`">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230" :attach="`#item-${props.item.id}`">
            <VList>
                <VListItem value="deposit" @click="deposit()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:report-money" />
                    </template>
                    <VListItemTitle>입금처리</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" @click="cancel()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>정산취소</VListItemTitle>
                </VListItem>
                <VListItem value="download" @click="download()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="vscode-icons:file-type-excel" />
                    </template>
                    <VListItemTitle>
                        <BaseQuestionTooltip :location="'bottom'" :text="'정산매출 다운로드'" :content="'해당 정산에 사용되었던 매출건들이 다운로드 됩니다.(추가차감액은 추출되지 않습니다.)'">
                        </BaseQuestionTooltip>
                    </VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
<style scoped>
:deep(.v-overlay__content) {
  z-index: 99999999999 !important;
  inset-block-start: 4em !important;
  inset-inline-start: -19em !important;
}
</style>
