import { defineStore } from 'pinia';

export const useFilterStore = defineStore('filterStore', () => {
    let path = '';
    const headers   = <any>(JSON.parse(localStorage.getItem('merchandises') || "{}"));    
    const isFilter  = ref(false);

    function setHeader(name:string, key:string) {
        const visable = <boolean>(headers[key]);
        if(visable === undefined)
            headers[key] = true;
        return {'ko': name, 'key': key, 'visable':ref(visable)};
    }
    function setFilter() {

    }
    return {
        path, headers, isFilter, setHeader, 
    };
});

export default useFilterStore
