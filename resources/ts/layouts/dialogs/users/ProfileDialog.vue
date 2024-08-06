<script lang="ts" setup>
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue'
import type { UserPropertie } from '@/views/types'
import { avatars } from '@/views/users/useStore'
interface Props {
    item: UserPropertie,
}
const props = withDefaults(defineProps<Props>(), {})
const visible = ref(false)
const show = () => {
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent style="max-width: 800px;">
        <DialogCloseBtn @click="visible = !visible" />
        <VCard>
            <VCardText>
                <VRow>
                    <SwiperPreview :items="avatars"
                        :preview="props.item.profile_img ?? avatars[Math.floor(Math.random() * avatars.length)]"
                        :label="'프로필'" :lmd="9" :rmd="3" @update:file="props.item.profile_file = $event"
                        @update:path="props.item.profile_img = $event">
                    </SwiperPreview> 
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
