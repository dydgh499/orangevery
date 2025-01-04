
import router from '@/router';
import { Category, Merchandise } from '@/views/types';
import { axios } from '@axios';


export const shoppingMallStore = defineStore('shoppingMallStore', () => {
    const route = useRoute()
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))

    const main_tab = ref(<number>(0))
    const caetegory_tab = ref(<number>(0))
    const categories = ref(<Category[]>([]))
    const merchandise = ref(<Merchandise>({
        profile_img: '',
        mcht_name: '',
        nick_name: '',
        contact_num: '',
        addr: '',
    }))
    const is_skeleton = ref(true)
    const search = ref('')
    

    const getShoppingMallWindow = async () => {
        let code = 200
        let message = ''
        let res = null
        try {
            res = await axios.get('/api/v1/shopping-mall/' + route.params.shop_window)
        }
        catch (e: any) {
            code = e.response.data.code
            message = e.response.data.message
            res = errorHandler(e)
        }
        return [code, message, res.data]
    }

    const getShoppingMallProduct = async (product_id: string | string[]) => {
        let code = 200
        let message = ''
        let res = null
        try {
            res = await axios.get('/api/v1/shopping-mall/' + route.params.shop_window + '/' + product_id)
        }
        catch (e: any) {
            code = e.response.data.code
            message = e.response.data.message
            res = errorHandler(e)
        }
        return [code, message, res.data]
    }

    const filterProducts = computed(() => {
        if(categories.value.length > 0) {
            const category = categories.value[caetegory_tab.value]
            return category.products.filter(product => { 
                return (search.value === '') || (search.value !== '' && product.product_name.includes(search.value))
            })                
        }
        return []
    })

    const moveProductDetail = (window: string | string[], product_id: number) => {
        router.push('/shop/' + window + '/' + product_id)
    }

    const moveBack = () => {
        router.back()
    }

    watchEffect(() => {
        if(route.params.shop_window && route.params.product_id)
            main_tab.value = 1
        else
            main_tab.value = 0
    })
    
    return {
        main_tab,
        caetegory_tab,

        search,
        categories,
        merchandise,
        filterProducts,

        is_skeleton,
        getShoppingMallWindow,
        getShoppingMallProduct,
        moveProductDetail,
        moveBack,
    }
})
