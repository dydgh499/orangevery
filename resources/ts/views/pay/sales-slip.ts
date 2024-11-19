import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import type { BeforeBrandInfo, PayGateway, SalesSlip } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';
import { useZoomProperty } from '@layouts/composable/useZoomProperty';
import html2canvas from "html2canvas";
import { useDisplay } from 'vuetify';

export const salesSlip = () => {
    const { mobile } = useDisplay()
    const pg = ref<PayGateway>()
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const merchandise_info = ref<BeforeBrandInfo>({})
    const provider_info = ref<BeforeBrandInfo>({})
    const supply_amount = ref(0)
    const vat = ref(0)
    const tax_free = ref(0)
    const total_amount = ref(0)
    const { copy } = payWindowStore()
    const { zoom } = useZoomProperty()

    const getVat = (trans: SalesSlip) => {
        return Math.round(trans?.amount as number / 1.1)
    }
    
    const getSalesSlipRect = async () => {
        const sales_slip_rect = document.getElementsByClassName('sales-slip-rect')[0]
        const canvas = await html2canvas(sales_slip_rect, {
            scale: 2, // 기본값은 1, 더 높은 값으로 설정하면 고해상도로 캡처
            useCORS: true, // 외부 리소스를 로드할 때 CORS 문제가 발생하지 않도록 설정
            letterRendering: true, // 텍스트 렌더링 정확도를 높임
            allowTaint: true, // Cross-Origin 이미지를 허용할 경우
            onclone: (document, element) => {
                if(zoom.value !== 100) {
                    document.documentElement.style.setProperty("zoom", "100%")
                }
              }
        })
        return canvas
    }

    const copySalesSlip = async (trans: SalesSlip, card: any) => {
        if(mobile.value)
            copySalesSlipText(trans)
        else
        {
            if (card) {
                const canvas = await getSalesSlipRect()
                canvas.toBlob(blob => navigator.clipboard.write([new ClipboardItem({ "image/png": blob as Blob })]))
                snackbar.value.show('영수증 이미지가 클립보드에 복사되었습니다.', 'success')
            }
        }
    }
    
    const downloadSalesSlip = async (trans: SalesSlip, card: any) => {
        const downloadURI = (uri: string, file_name: string) => {
            const link = document.createElement("a")
            link.download = file_name
            link.href = uri
            document.body.appendChild(link)
            link.click()
        }
    
        snackbar.value.show('영수증을 다운로드하고있습니다..', 'success')
        if (card) {
            const canvas = await getSalesSlipRect()
            downloadURI(canvas.toDataURL(), trans?.trx_dttm+"_"+trans?.appr_num+".png")
            snackbar.value.show('영수증이 다운로드 되었습니다.', 'success')
        }
    }

    const copySalesSlipText = (trans: SalesSlip) => {
        let text = `신용카드 영수증\n
    결제정보
    ---------------------------------
    결제수단\t\t${module_types.find(obj => obj.id === trans?.module_type)?.title}
    거래상태\t\t${trans?.is_cancel ? "취소" : '승인'}
    승인일시\t\t${trans?.trx_dttm}
    `
        if(trans?.is_cancel) {
            text += `취소일시\t\t${trans?.cxl_dttm}
    `
        }
            text += `발급사\t\t\t${trans?.issuer ?? ''}
    매입사\t\t\t${trans?.acquirer ?? ''}
    카드번호\t\t${trans?.card_num ?? ''}
    할부개월\t\t${installments.find(inst => inst['id'] === parseInt(trans?.installment as string))?.title}
    구매자명\t\t${trans?.buyer_name ?? ''}
    상품명\t\t\t${trans?.item_name ?? ''}
    승인번호\t\t${trans?.appr_num ?? ''}
    과세금액\t\t${supply_amount.value.toLocaleString()}원
    부가세액\t\t${vat.value.toLocaleString()}원
    `
    if(trans?.tax_category_type === 1)
        text += `면세액\t\t\t${tax_free.value.toLocaleString()}원
    `
    text += `총결제액\t\t${total_amount.value.toLocaleString()}원
    
    판매자 정보
    ---------------------------------
    상호\t\t\t\t${trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.company_name : trans?.mcht_name}
    사업자번호\t${trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.business_num : trans?.business_num}
    대표자명\t\t${trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.rep_name : trans?.nick_name}
    주소\t\t\t\t${trans?.use_saleslip_sell ? corp.pv_options.free.sales_slip.merchandise.addr : trans?.addr}
    
    공급자(결제대행사)정보
    ---------------------------------
    상호\t\t\t\t${provider_info.value?.company_name}
    사업자번호\t${provider_info.value?.business_num}
    대표자명\t\t${provider_info.value?.rep_name}
    주소\t\t\t\t${provider_info.value?.addr}
    
    ---------------------------------
    신용카드 매출전표는 부가가치세법 제32조 2 제3항에 의거하여 발행되었으며 부가가치세법 제 46조에 따라 신용카드매출전표 등을 발급받은 경우에는 매입세액 공제가 가능합니다.`
        
        copy(text, '영수증 텍스트')
    }
    
    const copyLink = async (trans: SalesSlip) => {
        const url = `${window.location.origin}/pay/sales-slip/${trans?.ord_num}?is_cancel=${trans?.is_cancel}`
        copy(url, '매출전표 링크')
    }
    
    const businessNumMasking = (business_num: string) => {
        if(business_num?.length as number > 9) {
            const bsin_num = business_num?.replace(/\D/g, '') as string
            return bsin_num.slice(0, 3) + "-" + bsin_num.slice(3, 5) + "-" + bsin_num.slice(5)
        }
        else
            return business_num
    }

    const setVat = (trans: SalesSlip) => {
        if (trans.tax_category_type == 1) {
            supply_amount.value = 0
            vat.value = 0
            tax_free.value = trans.amount
            total_amount.value = trans.amount
        }
        else {
            supply_amount.value = getVat(trans)
            vat.value = trans.amount as number - getVat(trans)
            tax_free.value = 0
            total_amount.value = trans.amount
        }

    }

    const setMerchandiseInfo = (trans: SalesSlip) => {
        if(trans?.use_saleslip_sell) {
            merchandise_info.value = <BeforeBrandInfo>({
                company_name: corp.pv_options.free.sales_slip.merchandise.company_name,
                business_num: corp.pv_options.free.sales_slip.merchandise.business_num,
                phone_num: corp.pv_options.free.sales_slip.merchandise.phone_num,
                rep_name: corp.pv_options.free.sales_slip.merchandise.rep_name,
                addr: corp.pv_options.free.sales_slip.merchandise.addr,
            })
        }
        else {
            merchandise_info.value = <BeforeBrandInfo>({
                company_name: trans?.mcht_name,
                business_num: trans?.business_num,
                phone_num: trans?.contact_num,
                rep_name: trans?.nick_name,
                addr: trans?.addr,
            })
        }
    }

    const setProviderInfo = (trans: SalesSlip) => {
        const trx_dt = new Date(trans?.trx_dt as string)
        const before_brand_info = corp.before_brand_infos.find(obj => new Date(obj.apply_e_dt) >= trx_dt && new Date(obj.apply_s_dt) <= trx_dt)
        if (before_brand_info) {
            provider_info.value = <BeforeBrandInfo>({
                company_name: before_brand_info?.company_name,
                business_num: before_brand_info?.business_num,
                phone_num: before_brand_info?.phone_num,
                rep_name: before_brand_info?.rep_name,
                addr: before_brand_info?.addr,
            })
        }
        else {
            if (Number(trans?.use_saleslip_prov)) {
                provider_info.value = <BeforeBrandInfo>({
                    company_name: pg.value?.company_name,
                    business_num: pg.value?.business_num,
                    phone_num: pg.value?.phone_num,
                    rep_name: pg.value?.rep_name,
                    addr: pg.value?.addr,
                })
            }
            else {
                provider_info.value = <BeforeBrandInfo>({
                    company_name: corp.company_name,
                    business_num: corp.business_num,
                    phone_num: corp.phone_num,
                    rep_name: corp.ceo_name,
                    addr: corp.addr,
                })
            }    
        }
    }
        
    const init = (trans: SalesSlip, pgs: PayGateway[]) => {
        pg.value = pgs.find(pg => pg['id'] === Number(trans.pg_id))
        setVat(trans)
        setProviderInfo(trans)
        setMerchandiseInfo(trans)
        provider_info.value.business_num = businessNumMasking(provider_info.value?.business_num as string)
        merchandise_info.value.business_num = businessNumMasking(merchandise_info.value?.business_num as string)
    }
    
    const getSalesSlip = async (ord_num: string | string[], is_cancel: number) => {
        let code = 200
        let message = ''
        let res = null
        try {
            res = await axios.get(`/api/v1/pay/sales-slip/${ord_num}`, {
                 params : { 
                    brand_id: corp.id,
                    is_cancel: is_cancel,
                }
            })
        }
        catch (e: any) {
            code = e.response.data.code
            message = e.response.data.message
            res = errorHandler(e)
        }
        return [code, message, res.data]
    }

    return {
        provider_info, merchandise_info, 
        supply_amount, vat, tax_free, total_amount,
        init, copySalesSlip, copyLink, downloadSalesSlip, getSalesSlip
    }
}
