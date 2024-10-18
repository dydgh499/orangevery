import { NavGroup, NavLink } from '@/@layouts/types';
import navItems from '@/navigation/vertical';
import { getUserLevel, user_info } from '@/plugins/axios';
import corp from '@/plugins/corp';
import router from '@/router';
import { types } from '@/views/posts/useStore';
import { RouteLocationNormalized } from "vue-router";

interface Tab {
    title: string;
    path: string;
}

export const useDynamicTabStore = defineStore('dynamicTabStore', () => {    
    const local_key = ref(`${corp.name}-${user_info.value.user_name}-dynamic-tap-headers`)
    const tab = ref(<number>(-1))
    const tabs = reactive<Tab[]>(JSON.parse(localStorage.getItem(local_key.value) || "[]"))

    const add = (to: RouteLocationNormalized) => {        
        const path = to.fullPath.slice(1).replaceAll('/', '-')
        const edit_pattern = /-edit-\d+/;
        const part_settle_pattern = /-part-\d+/;
        const edit_brand_pattern = /services-brands-edit-\d+/;
        const post_view_pattern = /-\d+/;
        const getTitle = (nav_title: string) => {            
            const numbers = path.match(/\d+/g);

            const getReplyViewTitle = () => {
                let title = nav_title + ` 답변`;
                if(numbers?.length)
                    title += `(#${numbers[0]})`
                return title
            }
            const getCreateViewTitle = () => {
                return nav_title.replaceAll('목록', " ").replaceAll('관리', " ") + '추가'
            }
            const getEditViewTitle = () => {
                let title = nav_title.replaceAll('목록', " ").replaceAll('관리', " ")
                title += getUserLevel() >= 35 ? `수정` : `정보`
                if(numbers?.length)
                    title += `(#${numbers[0]})`
                return title
            }
            const getPartSettleViewTitle = () => {
                return numbers?.length ? nav_title + `(#${numbers[0]})` : nav_title
            }
            const getPostViewTitle = () => {
                let title = nav_title
                if(numbers?.length)
                    title += `(#${numbers[0]})`
                return title
            }

            if(edit_brand_pattern.test(path))
                return nav_title
            else if(path.includes('posts-reply'))
                return getReplyViewTitle()
            else if(path.includes('create'))
                return getCreateViewTitle()
            else if(edit_pattern.test(path))
                return getEditViewTitle()
            else if(part_settle_pattern.test(path)) 
                return getPartSettleViewTitle()
            else if(post_view_pattern.test(path) && path.includes('posts'))
                return getPostViewTitle()
            else
                return nav_title
        }

        const isExistTap = () => {
            return tabs.some(obj => obj.path === to.fullPath)
        }

        const getNavs = () => {
            const navs = <any>([]);
            const [uri, query] = to.fullPath.slice(1).replaceAll('/', '-').split('?')
            const dest = uri
                .replace(edit_pattern, '')
                .replace(part_settle_pattern, '')
                .replace(post_view_pattern, '')
                .replaceAll('-create', "")
                .replaceAll('-reply', "")
            const extract = (item: NavLink | NavGroup) => {
                // 'title'과 'to'가 있는 경우 추출
                if (item.title && item.to) {
                    navs.push({ title: item.title, to: item.to });
                }        
                // 'children'이 있는 경우 재귀적으로 처리
                if (item.children && Array.isArray(item.children)) {
                    item.children.forEach(extract);
                }
            }
            navItems.value.forEach(extract);
            return navs.find(obj => obj.to === dest)
        }

        const nav = getNavs()
        if(nav && isExistTap() === false && path !== 'services-brands') {
            tabs.push({
                title: getTitle(nav.title),
                path: to.fullPath,
            })
        }
    }

    const move = (path: string) => {
        router.replace(path)
    }

    const remove = (index: number) => {
        if (tabs.length) {
            tabs.splice(index, 1)
        }
    }

    const titleUpdate = (id: number, title: string, user_name: string) => {
        const idx = tabs.findIndex(obj => obj.title.includes(title) && obj.title.includes('#'+id))
        if(idx !== -1) {
            tabs[idx].title = tabs[idx].title.replace('#' + id, user_name)
        }
    }

    const postTitleUpdate = (view_type:string, post_type:number, id: number, path: string) => {
        const type = types.find(obj => obj.id === post_type)
        if(type) {
            const idx = tabs.findIndex(obj => obj.path === path)
            if(idx !== -1) {
                tabs[idx].title = type.title + `${view_type}(#${id})`    
                return true
            }
        }
        return false
    }

    watchEffect(() => {
        if(getUserLevel() >= 10)
            local_key.value = `${corp.name}-${user_info.value.user_name}-dynamic-tap-headers`  
    })

    watchEffect(() => {
        if(tabs.length > 20)
            tabs.splice(0, 1)
        if(getUserLevel() >= 10)
            localStorage.setItem(local_key.value, JSON.stringify(tabs))
    })

    return {
        add,
        move,
        remove,
        titleUpdate,
        postTitleUpdate,
        tab,
        tabs,
    }
})

