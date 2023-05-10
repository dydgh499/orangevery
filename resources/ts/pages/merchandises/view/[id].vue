<script lang="ts" setup>
import UserPrivacyOverview from '@/views/merchandises/view/UserDefaultOverview.vue';
import UserMerchandiseOverview from '@/views/merchandises/view/UserMerchandiseOverview.vue';
import type { IUserModel } from '@/views/types';

let submitType = {text: '제출'}
let userModel : IUserModel;

const isDialogVisible = ref(false)
const tabs = [
  { icon: 'tabler-user-check', title: '개인정보' },
  { icon: 'ph-buildings', title: '가맹점정보' },
  { icon: 'tabler-currency-dollar', title: '영업자정보' },
]
const userTab = ref(null)

 
</script>

<template>
  <VTabs v-model="userTab" class="v-tabs-pill">
    <VTab v-for="tab in tabs" :key="tab.icon">
      <VIcon :size="18" :icon="tab.icon" class="me-1" />
      <span>{{ tab.title }}</span>
    </VTab>
  </VTabs>

  <VWindow v-model="userTab" class="mt-6 disable-tab-transition" :touch="false">
    <VWindowItem>
      <UserPrivacyOverview :user="userModel" :submit="submitType"/>
    </VWindowItem>
    <VWindowItem>
      <UserMerchandiseOverview />
    </VWindowItem>
  </VWindow>

  
  <VDialog v-if="isDialogVisible"
        v-model="isDialogVisible"
        width="500"
      >
        <!-- Activator -->
        <template #activator="{ props }">
          <VBtn v-bind="props">
            Click Me
          </VBtn>
        </template>

        <!-- Dialog close btn -->
        <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

        <!-- Dialog Content -->
        <VCard title="Privacy Policy">
          <VCardText>
            Bear claw pastry cotton candy jelly toffee. Pudding chocolate cake shortbread bonbon biscuit sweet. Lemon drops cupcake muffin brownie fruitcake. Pastry pastry tootsie roll jujubes chocolate cake gummi bears muffin pudding caramels. Jujubes lollipop gummies croissant shortbread. Cupcake dessert marzipan topping gingerbread apple pie chupa chups powder. Cake croissant halvah candy canes gummies.
          </VCardText>

          <VCardText class="d-flex justify-end">
            <VBtn @click="isDialogVisible = false">
              I accept
            </VBtn>
          </VCardText>
        </VCard>
      </VDialog>
</template>
