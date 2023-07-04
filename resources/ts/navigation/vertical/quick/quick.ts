import { usePayModFilterStore } from '@/views/merchandises/pay-modules/useStore'
import { user_info } from '@axios'
import corp from '@corp'


const getAbilitiesMenu = computed(() => {
    const payments = []
    if(user_info.value.level == 10) {
        const { pay_modules, getAllPayModules } = usePayModFilterStore()
        getAllPayModules()
        if (corp.pv_options.free.use_hand_pay) {
            payments.push({
                title: 'Hand payment',
                to: 'quick-view-hand',
            })
        }
        if (corp.pv_options.free.use_auth_pay) {
            payments.push({
                title: 'Auth payment',
                to: 'quick-view-auth',
            })
        }
        if (corp.pv_options.free.use_simple_pay) {
            payments.push({
                title: 'Simple payment',
                to: 'quick-view-simple',
            })
        }
    }
    return [
        { heading: 'Forms' },
        {
          title: 'Home',
          icon: { icon: 'tabler-smart-home' },
          to:  'quick-view',
        },
        { heading: 'Transaction' },
        {
            title: 'Payment',
            icon: { icon: 'fluent-payment-32-regular' },
            children: payments
        },
    ]
})
export default getAbilitiesMenu
