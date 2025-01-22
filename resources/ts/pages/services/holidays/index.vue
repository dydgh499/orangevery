<script setup lang="ts">
import { useRequestStore } from '@/views/request'
import { StatusColorSetter } from '@/views/searcher'
import { rest_types, useHolidayStore } from '@/views/services/holidays/useStore'
import FullCalendar from '@fullcalendar/vue3'


const refCalendar = ref()
const alert = <any>(inject('alert'))
const selected = ref<number[]>([0, 1, 2])
const holidayDlg = <any>(inject('holidayDlg'))

const { post } = useRequestStore()
const { holidays, updateFilter, calendarOptions } = useHolidayStore()

const bulkRegister = async () => {
    if (await alert.value.show('정말 공휴일을 읽어오시겠습니까?')) {
        const r = await post('/api/v1/manager/services/holidays/batch-updaters/register', {}, true)
    }
}
</script>
<template>
    <VRow>
        <VCol>
            <VCard>
                <VLayout style="z-index: 0;">
                    <VNavigationDrawer width="290" absolute touchless location="start"
                        class="calendar-add-event-drawer" :temporary="$vuetify.display.mdAndDown">
                        <div style="display: flex; flex-direction: column;margin: 22.5px;">
                            <VBtn block prepend-icon="tabler-plus" @click="holidayDlg.show({ id: 0 })">
                                공휴일 추가
                            </VBtn>
                        </div>
                        <VDivider />
                        <div class="pa-7">
                            <p class="text-sm text-uppercase text-disabled mb-3">
                                FILTER
                            </p>
                            <div class="d-flex flex-column calendars-checkbox">
                                <VCheckbox v-for="rest_type in rest_types" :key="rest_type.id"
                                    v-model="selected" 
                                    :value="rest_type.id" 
                                    :color="StatusColorSetter().getSelectIdColor(rest_type.id)"
                                    :label="rest_type.title" 
                                    @update:modelValue="updateFilter(selected)"
                                />
                            </div>
                        </div>
                        <VDivider />
                        <div style="display: flex; flex-direction: column;margin: 22.5px;">
                            <VBtn prepend-icon="material-symbols:holiday-village" @click="bulkRegister()" color="warning">
                                공휴일 업데이트
                            </VBtn>
                        </div>
                    </VNavigationDrawer>
                    <VMain>
                        <VCard flat>
                            <FullCalendar ref="refCalendar" 
                                :key="holidays.length"
                                :options="calendarOptions"
                                :style="'max-height: 1000px;'"
                            />
                        </VCard>
                        <VDivider />
                        <div style="padding: 1em; text-align: end;">
                            <h5>1. 매년 12월 1일 다음연도 공휴일이 일괄 추가됩니다.</h5>
                            <h5>2. 추가/수정된 공휴일은 작업 후 5분 이후에 정산내용에 반영됩니다.</h5>
                        </div>
                    </VMain>
                </VLayout>
            </VCard>
        </VCol>
    </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-event-drawer {
  &.v-navigation-drawer:not(.v-navigation-drawer--temporary) {
    border-end-start-radius: 0.375rem;
    border-start-start-radius: 0.375rem;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }

  & ~ .flatpickr-calendar .flatpickr-weekdays {
    margin-block: 0 4px;
  }
}
</style>

<style lang="scss" scoped>
.v-layout {
  overflow: visible !important;

  .v-card {
    overflow: visible;
  }
}
</style>
