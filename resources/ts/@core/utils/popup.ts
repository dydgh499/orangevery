
import type { Popup } from '@/views/types'

export const PopupEvent = (popup_key_name: string) => {
    const init = (popup: Popup) => {
        if(getCookie(popup_key_name+popup.id) !== null)
            popup.visible = false
        else
            popup.visible = true
    }
    const setOpenStatus = (popup: Popup) => {
        if(popup.is_hide) {
            setCookie(popup_key_name+popup.id, 'true', 1)
        }
        popup.visible = !popup.visible
    }
    
    const getCookie = (name: string) => {
        var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return value? value[2] : null;  
    }
    
    const setCookie = function(name: string, value: string, exp: number) {
        var date = new Date()
        date.setHours(23, 59, 59, 999)
        document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
    };
    return {
        setOpenStatus,
        init,
    }
}

