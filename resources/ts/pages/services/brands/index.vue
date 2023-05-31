<script setup lang="ts">
import { useSearchStore } from '@/views/services/brands/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';

const { store } = useSearchStore()
provide('store', store)

const metas: any[] = []
</script>
<template>
        <BaseIndexView placeholder="서비스명" :metas="metas" :add="true" add_name="서비스">
        <template #filter>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden"> {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden"> 
                    <span v-if="key == `id`" class="edit-link" @click="store.edit(user.id)">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `dns`" class="edit-link" @click="store.edit(user.id)">
                        {{ user[key] }}
                    </span>
                    <span v-else-if="key == `logo_img`" class="edit-link" @click="store.edit(user.id)">
                        <img :src="user.logo_img" style="max-height: 60px; padding: 0.3em;"/>
                    </span>
                    
                    <span v-else-if="key == `company_nm`" class="edit-link" @click="store.edit(user.id)">
                        {{ user[key] }}
                    </span>
                    <span v-else-if="key == `main_color`">
                        <div :style="`width: 90%; height: 50%;background:`+user.theme_css.main_color"></div>
                    </span>
                    <span v-else-if="key == `deposit_amount`"> 
                            {{ user[key].toLocaleString() }}
                    </span>
                    <span v-else> 
                        {{ user[key] }} 
                    </span>
                </td>
            </tr>
        </template>
    </BaseIndexView>
</template>
