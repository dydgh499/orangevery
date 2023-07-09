import{_ as w}from"./Preview.vue_vue_type_style_index_0_scope_true_lang.6d3289f9.js";import{e as V}from"./validators.f65c6173.js";import{V as _}from"./VFileInput.ba4c7c33.js";import{V as x}from"./VChip.138758bf.js";import{V as k}from"./VCol.0ff826f7.js";import{d as y,a as m,a2 as p,c as R,E as r,H as c,S as i,z as d,R as s,W as C,G as F,F as L,X as h,Z as z,K as B}from"./main.78b17f62.js";const G=y({__name:"FileLogoInput",props:{preview:null,label:null,validates:null},emits:["update:file"],setup(v,{emit:f}){const a=v,e=m([]),l=m("/icons/img-preview.svg"),g=`
    border: 2px solid rgb(238, 238, 238);
    border-radius: 0.5em;
    float: inline-end;
    margin-block-end: 0.5em;
    margin-block-start: 0.5em;
    margin-inline-start: auto;
    max-inline-size: 10em;
`;p(()=>{e.value!=null&&(l.value=e.value.length?URL.createObjectURL(e.value[0]):"/icons/img-preview.svg",f("update:file",e.value?e.value[0]:e.value))}),p(()=>{l.value=a.preview});const b=R(()=>o=>V(o,a.validates));return(o,n)=>(r(),c(k,null,{default:i(()=>[d(_,{accept:"image/*",modelValue:s(e),"onUpdate:modelValue":n[0]||(n[0]=t=>C(e)?e.value=t:null),label:a.label,rules:[s(b)],"prepend-icon":"tabler-camera-up"},{selection:i(({fileNames:t})=>[(r(!0),F(L,null,h(t,u=>(r(),c(x,{key:u,label:"",size:"small",variant:"outlined",color:"primary",class:"me-2"},{default:i(()=>[z(B(u),1)]),_:2},1024))),128))]),_:1},8,["modelValue","label","rules"]),d(w,{preview:s(l),style:"","preview-style":g,class:"preview"},null,8,["preview"])]),_:1}))}});export{G as _};
