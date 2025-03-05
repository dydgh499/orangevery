<script lang="ts" setup>
import SwiperPreview from '@/layouts/utils/SwiperPreview.vue';
import type { IdentityDesign } from '@/views/types';

interface Props {
    item: IdentityDesign,
}
const login_imgs = [
    '/utils/logins/1.png',
    '/utils/logins/2.png',
    '/utils/logins/3.png',    
    '/utils/logins/4.png',
]
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
                    <SwiperPreview :items="login_imgs" 
                        :preview="props.item.login_img ?? login_imgs[Math.floor(Math.random() * login_imgs.length)]"
                        :label="'로그인 배경'" :lmd="6" :rmd="6"
                        @update:file="props.item.login_file = $event"
                        @update:path="props.item.login_img = $event">
                    </SwiperPreview>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
