import type { Danger, LockedUser, MonthlyTransChart, OperatorHistory, UpSideChart } from '@/views/types'
import { axios, getUserLevel } from '@axios'
import { orderBy } from 'lodash'

export const useCRMStore = defineStore('CRMStore', () => {
    const errorHandler = <any>(inject('$errorHandler'))
    const monthly_transactions = ref(<MonthlyTransChart>({}))
    const upside_merchandises = ref(<UpSideChart>({}))
    const upside_salesforces = ref(<UpSideChart>({}))
    const danger_histories = ref(<Danger[]>[])
    const operator_histories = ref(<OperatorHistory[]>([]))
    const locked_users = ref(<LockedUser[]>([])
)
    const getGraphData = async() => {
        try {
            const r1 = await axios.get('/api/v1/manager/dashsboards/monthly-transactions-analysis')
            
            const sortedKeys = orderBy(Object.keys(r1.data), [], ['desc']);
            const sortedData = sortedKeys.reduce((acc, key) => {
              acc[key] = r1.data[key];
              return acc;
            }, {} as Record<string, typeof r1.data["2023-06"]>);
            Object.assign(monthly_transactions.value, sortedData)

            const [r2, r3, r4] = await Promise.all([
                axios.get('/api/v1/manager/dashsboards/upside-merchandises-analysis'),
                axios.get('/api/v1/manager/dashsboards/upside-salesforces-analysis'),
                axios.get('/api/v1/manager/dashsboards/recent-danger-histories'),
            ])
            Object.assign(upside_merchandises.value, r2.data)
            Object.assign(upside_salesforces.value, r3.data)
            Object.assign(danger_histories.value, r4.data.content)
            if(getUserLevel() >= 35) {
                const [r5, r6] = await Promise.all([
                    axios.get('/api/v1/manager/dashsboards/recent-operator-histories'),
                    axios.get('/api/v1/manager/dashsboards/locked-users'),
                ])
                Object.assign(operator_histories.value, r5.data.content)
                Object.assign(locked_users.value, r6.data.content)    
            }
        }
        catch (e) {
            const r = errorHandler(e)
        }
    }

    const getColors = (dates: string[], previous: string, current: string) => {
        const _color = []
        if (dates.length > 0) {
            for (let i = 0; i < dates.length - 1; i++) {
                _color.push(previous)
            }
            _color.push(current)
        }
        return _color
    }
    
    const getDayOfWeeks = (idays: string[]) => {
        const weeks:string[] = []
        idays.forEach(date => {
            const options:any = { weekday: 'short', locale: 'ko-KR' };
            const dayOfWeek = new Date(date).toLocaleDateString('ko-KR', options);
            weeks.push(dayOfWeek)
        });
        return weeks
    }

    const getMonths = (dates: string[]) => {
        const _months: string[] = []; // 결과를 저장할 배열
        for (let i = 0; i < dates.length; i++) {
            const month = new Date(dates[i]).toLocaleString('default', { month: 'long' }); // 월 이름 가져오기
            _months.unshift(month); // 배열의 맨 앞에 추가
        }
        return _months
    }
    return {
        getGraphData,
        monthly_transactions,
        upside_merchandises,
        upside_salesforces,
        danger_histories,
        operator_histories,
        locked_users,
        getColors,
        getDayOfWeeks,
        getMonths,

    }
})
