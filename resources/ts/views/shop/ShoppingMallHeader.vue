<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import { avatars } from '@/views/users/useStore';
import { shoppingMallStore } from './shoppingMallStore';

const store = shoppingMallStore()
const visiable_search_bar = ref(true)
const visiable_mcht_bar = ref(false)
const emits = defineEmits(['update:visiable_search_bar']);

const setSearchBar = () => {
    visiable_search_bar.value = !visiable_search_bar.value
}

const setMchtBar = () => {
    visiable_mcht_bar.value = !visiable_mcht_bar.value
}

onMounted(async () => {
    const route = useRoute()
    const [_code, _message, _data] = await store.getShoppingMallWindow()

    _data.merchandise.profile_img = _data.merchandise.profile_img ? _data.merchandise.profile_img : avatars[Math.floor(Math.random() * avatars.length)]
    store.merchandise = _data.merchandise
    store.categories = _data.categories
    store.is_skeleton = false
})

</script>
<template>
    <VCard style="width: 100%; max-width: 720px;" :key="store.is_skeleton">
        <VCol>
            <VRow class="header-wrapper">
                <div class="d-inline-flex" style="margin: 0.5em;">
                    <VBtn icon variant="text" color="default"
                        v-if="store.main_tab === 0"
                        size="small" @click="setSearchBar()">
                        <VIcon icon="tabler:search" size="24" :color="visiable_search_bar ? 'primary' : 'default'"/>
                    </VBtn>
                    <VBtn icon variant="text" color="default"
                        v-if="store.main_tab !== 0"
                        size="small" @click="store.moveBack()">
                        <VIcon icon="tabler:arrow-back" size="24"/>
                    </VBtn>
                </div>
                <div class="mcht-name-wrapper">
                    <h3 style="margin: 1em;">
                        <SkeletonBox v-if="store.is_skeleton" :width="'50%'"/>
                        <span v-else>{{ store.merchandise.mcht_name }}</span>
                    </h3>
                </div>
                <div class="d-inline-flex" style="margin: 0.5em;">
                    <VBtn icon variant="text" color="default"
                        size="small" @click="setMchtBar()">
                        <VIcon icon="tabler:shopping-cart-cog" size="24"
                        :color="visiable_mcht_bar ? 'primary' : 'default'"/>
                    </VBtn>
                </div>
            </VRow>
        </VCol>
        <template v-if="visiable_mcht_bar">
            <VDivider />
            <VCol>
                <VRow>
                    <VCol md="3" cols="3" style="text-align: center;">
                        <VAvatar color="primary" variant="tonal" size="70">
                            <SkeletonBox :borderRadius="'10em'" v-if="store.is_skeleton"/>
                            <VImg v-else :src="store.merchandise.profile_img" />
                        </VAvatar>
                    </VCol>
                    <VDivider :vertical="true"/>
                    <VCol md="3" cols="3">
                        <div style="margin-bottom: 0.25em;">
                            <b style="width: 5em;">대표자</b>
                        </div>
                        <div style="margin-bottom: 0.25em;">
                            <b>고객문의</b>
                        </div>
                        <div>
                            <b>주소</b>
                        </div>
                    </VCol>
                    <VCol md="5" cols="5">
                        <div style="margin-bottom: 0.25em;">
                            <SkeletonBox v-if="store.is_skeleton"/>
                            <span v-else>{{ store.merchandise.nick_name }}</span>
                        </div>
                        <div style="margin-bottom: 0.25em;">
                            <SkeletonBox v-if="store.is_skeleton"/>
                            <span v-else>{{ store.merchandise.contact_num }}</span>
                        </div>
                        <div style="margin-bottom: 0.25em;">
                            <SkeletonBox v-if="store.is_skeleton"/>
                            <span v-else>{{ store.merchandise.addr }}</span>
                        </div>
                    </VCol>                    
                </VRow>
            </VCol>
        </template>
        <template v-if="store.main_tab === 0">
            <VDivider />
            <VTabs grow v-if="store.is_skeleton">
                <VTab v-for="(category, index) in 10" :key="index" :class="{
                    'v-slide-group-item--active v-tab--selected': 0 === index,
                    'text-secondary': 0 !== index
                    }" @click="store.search = ''">
                    <span>
                        <SkeletonBox :width="'3em'" :height="'1em'"/>
                    </span>
                </VTab>
            </VTabs>
            <VTabs v-model="store.caetegory_tab" grow v-else>
                <VTab v-for="(category, index) in store.categories" :key="index" :class="{
                    'v-slide-group-item--active v-tab--selected': store.caetegory_tab === index,
                    'text-secondary': store.caetegory_tab !== index
                }" @click="store.search = ''">
                    <span>{{ category.category_name }}</span>
                </VTab>
            </VTabs>
            <VRow v-if="visiable_search_bar">
                <VCol cols="12">
                    <VTextField 
                        variant="underlined" density="comfortable" 
                        v-model="store.search" placeholder="상품명 검색"
                        prepend-icon="tabler:search"
                         style="margin: 0.5em 1em;"
                    />
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>

<style scoped>
.header-wrapper {
  align-items: center;
  justify-content: space-between;
}

.mcht-name-wrapper {
  position: absolute;
  z-index: -1;
  inline-size: 100%;
  text-align: center;
}
</style>

