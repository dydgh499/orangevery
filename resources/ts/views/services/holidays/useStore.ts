import { StatusColorSetter } from '@/views/searcher';
import { Holiday } from '@/views/types';
import { axios } from '@axios';
import type { CalendarOptions } from '@fullcalendar/core';
import koLocale from '@fullcalendar/core/locales/ko';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction'; // 상호작용 플러그인

export const rest_types = [
    {id: 1, title: '공공기관 휴일'},
    {id: 2, title: '기념일'},
    {id: 0, title: '기타'},
]

export const useHolidayStore = defineStore('HolidayStore', () => {
    const holidays = ref(<Holiday[]>([]))
    const filters = ref<number[]>([])
    const holidayDlg = <any>(inject('holidayDlg'))

    onMounted(async () => { 
        filters.value = [0, 1, 2]
        const r = await axios.get('/api/v1/manager/services/holidays')
        holidays.value = r.data
    })

    const updateFilter = (selected: number[]) => {
        filters.value = selected
    }

    const getHolidays = computed(() => {
        const _holidays = <any>([])
        holidays.value.forEach(holiday => {
            if(filters.value.includes(holiday.rest_type)) {
                _holidays.push({
                    id: holiday.id,
                    title: holiday.rest_name, 
                    start: new Date(holiday.rest_dt), 
                    end: new Date(holiday.rest_dt), 
                    color: StatusColorSetter().getSelectIdColor(holiday.rest_type),
                    allDay: true,
                    extendedProps: {
                        calendar: holiday.rest_type,
                    },
                    locales: [koLocale], // 로케일을 배열로 설정
                })    
            }
        })
        return _holidays
    })
    
    const editHoliday = (holiday_id: number) => {
        const idx = holidays.value.findIndex(obj => obj.id === Number(holiday_id))
        if(idx !== -1) 
            holidayDlg.value.show(holidays.value[idx])    
    }

    const calendarOptions = {
        plugins: [dayGridPlugin, interactionPlugin ],
        initialView: 'dayGridMonth',
            headerToolbar: {
            start: 'drawerToggler,prev,next title',
            end: 'dayGridMonth',
        },
        weekends: true,
        forceEventDuration: true,
        editable: true,
        eventResizableFromStart: true,
        dragScroll: true,
        dayMaxEvents: 5,
        events: getHolidays,
        eventClassNames({ event: calendarEvent }) {
            const calendarsColor = {
                0: 'default',
                1: 'primary',
                2: 'success',
            }
            const colorName = calendarsColor[calendarEvent._def.extendedProps.calendar as keyof typeof calendarsColor]
            return [
                `bg-light-${colorName} text-${colorName}`,
            ]
        },
        eventClick({ event: clickedEvent }) {
            editHoliday(Number(clickedEvent.id))
        },
    } as CalendarOptions

    return { holidays, updateFilter, calendarOptions }
})
