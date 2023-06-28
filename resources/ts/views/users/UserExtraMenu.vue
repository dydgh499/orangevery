<script setup lang="ts">
interface Props {
    id: number,
    type: number, //0 == 가맹점, 1 == 영업자, 2 == 운영자
}

const props = defineProps<Props>()
const password = <any>(inject('password'))
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent">
            <VList>
                <VListItem value="password" @click="password.show(props.id, type)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-lock" />
                    </template>
                    <VListItemTitle>패스워드변경</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
