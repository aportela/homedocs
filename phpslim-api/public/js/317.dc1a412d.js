"use strict";(globalThis["webpackChunkhomedocs"]=globalThis["webpackChunkhomedocs"]||[]).push([[317],{1745:(e,a,l)=>{l.d(a,{H:()=>s});var t=l(1809),o=l(8612),n=l.n(o),i=l(1569);const u={namespace:"homedocs",storages:["local","cookie","session","memory"],storage:"local",expireDays:3650},s=(0,t.Q_)("initialState",{state:()=>({initialState:{allowSignUp:!1,maxUploadFileSize:1}}),getters:{isSignUpAllowed:e=>e.initialState.allowSignUp,maxUploadFileSize:e=>e.initialState.maxUploadFileSize},actions:{load(){i.api.common.initialState().then((e=>{this.initialState.allowSignUp=e.data.initialState.allowSignUp,this.initialState.maxUploadFileSize=e.data.initialState.maxUploadFileSize})).catch((e=>{console.error(e.response)}))},save(e){const a=n()(u);a.set("initialState",e)}}})},8317:(e,a,l)=>{l.r(a),l.d(a,{default:()=>T});l(9665);var t=l(9835),o=l(499),n=l(6970),i=l(1569),u=l(5253),s=l(1745),r=l(8339),d=l(6647),c=l(4170),m=l(9302),p=l(4537);const w=(0,t._)("img",{src:"icons/favicon-128x128.png"},null,-1),g={class:"text-center"},f={__name:"MainLayout",setup(e){const{t:a}=(0,d.QT)(),l=(0,m.Z)(),f=(0,u.F)(),v=(0,s.H)(),_=(0,r.tv)(),h=(0,t.Fl)((()=>f.isLogged)),b=(0,o.iH)(l.screen.gt.lg),W=(0,o.iH)(""),S=(0,o.iH)([]),k=(0,o.iH)(!1),x=[{shortLabel:"EN",label:"English",value:"en-US"},{shortLabel:"ES",label:"Español",value:"es-ES"},{shortLabel:"GL",label:"Galego",value:"gl-GL"}],y=[{icon:"storage",text:"Dashboard",routeName:"index"},{icon:"note_add",text:"Add",routeName:"newDocument"},{icon:"find_in_page",text:"Advanced search",routeName:"advancedSearch"}],q=x.find((e=>e.value==p.defaultLocale)),U=(0,o.iH)(q||x[0]);function Q(e,t){e&&e.trim().length>0?(S.value=[],k.value=!0,t((()=>{i.api.document.search(1,8,{title:e},"title","ASC").then((e=>{S.value=e.data.results.documents.map((e=>({id:e.id,label:e.title,caption:a("Fast search caption",{creation:c.ZP.formatDate(1e3*e.createdOnTimestamp,"YYYY-MM-DD HH:mm:ss"),attachmentCount:e.fileCount})}))),k.value=!1})).catch((e=>{k.value=!1,l.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}))):t((()=>{S.value=[]}))}function Z(e,a){U.value=e,p.i18n.global.locale.value=e.value,a&&f.saveLocale(e.value)}function z(){i.api.user.signOut().then((e=>{f.signOut(),_.push({name:"signIn"})})).catch((e=>{l.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}return v.load(),(e,l)=>{const i=(0,t.up)("q-btn"),u=(0,t.up)("q-avatar"),s=(0,t.up)("q-spinner-pie"),r=(0,t.up)("q-item-section"),d=(0,t.up)("q-item"),c=(0,t.up)("q-icon"),m=(0,t.up)("q-item-label"),p=(0,t.up)("q-list"),f=(0,t.up)("q-select"),v=(0,t.up)("q-space"),_=(0,t.up)("q-separator"),q=(0,t.up)("q-menu"),L=(0,t.up)("q-toolbar"),H=(0,t.up)("q-header"),C=(0,t.up)("q-scroll-area"),A=(0,t.up)("q-drawer"),D=(0,t.up)("router-view"),E=(0,t.up)("q-page-container"),F=(0,t.up)("q-layout"),I=(0,t.Q2)("close-popup"),P=(0,t.Q2)("ripple");return(0,t.wg)(),(0,t.j4)(F,{class:"bg-grey-1"},{default:(0,t.w5)((()=>[(0,t.Wm)(H,{elevated:"",class:"text-white",style:{background:"#24292e"},"height-hint":"61.59"},{default:(0,t.w5)((()=>[(0,t.Wm)(L,{class:"q-py-sm q-px-md"},{default:(0,t.w5)((()=>[h.value?((0,t.wg)(),(0,t.j4)(i,{key:0,class:"mobile-only",flat:"",dense:"",round:"",onClick:l[0]||(l[0]=e=>b.value=!b.value),"aria-label":"Menu",icon:"menu"})):(0,t.kq)("",!0),(0,t.Wm)(u,{square:"",size:"42px"},{default:(0,t.w5)((()=>[w])),_:1}),(0,t.Uk)(" HomeDocs "),h.value?((0,t.wg)(),(0,t.j4)(f,{key:1,ref:"search",dark:"",dense:"",standout:"","use-input":"","hide-selected":"",class:"q-mx-md",color:"black","stack-label":!1,label:(0,o.SU)(a)("Search..."),modelValue:W.value,"onUpdate:modelValue":l[1]||(l[1]=e=>W.value=e),options:S.value,onFilter:Q,style:{width:"100%"}},(0,t.Nv)({option:(0,t.w5)((e=>[(0,t.Wm)(p,{class:"bg-grey-9 text-white"},{default:(0,t.w5)((()=>[(0,t.Wm)(d,(0,t.dG)(e.itemProps,{to:{name:"document",params:{id:e.opt.id}}}),{default:(0,t.w5)((()=>[(0,t.Wm)(r,{side:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{name:"collections_bookmark"})])),_:1}),(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t.Wm)(m,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)(e.opt.label),1)])),_:2},1024),(0,t.Wm)(m,{caption:""},{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)(e.opt.caption),1)])),_:2},1024)])),_:2},1024)])),_:2},1040,["to"])])),_:2},1024)])),_:2},[k.value?{name:"no-option",fn:(0,t.w5)((()=>[(0,t.Wm)(d,null,{default:(0,t.w5)((()=>[(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t._)("div",g,[(0,t.Wm)(s,{color:"grey-5",size:"24px"})])])),_:1})])),_:1})])),key:"0"}:void 0]),1032,["label","modelValue","options"])):(0,t.kq)("",!0),(0,t.Wm)(v),(0,t.Wm)(i,{dense:"",flat:"","no-wrap":""},{default:(0,t.w5)((()=>[(0,t.Wm)(u,{rounded:"",size:"24px",class:"q-mr-sm"},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{name:"language"})])),_:1}),(0,t.Uk)(" "+(0,n.zw)(U.value.shortLabel)+" ",1),(0,t.Wm)(c,{name:"arrow_drop_down",size:"16px"}),(0,t.Wm)(q,{"auto-close":""},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{dense:"",style:{"min-width":"200px"}},{default:(0,t.w5)((()=>[(0,t.Wm)(d,{class:"GL__menu-link-signed-in"},{default:(0,t.w5)((()=>[(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t._)("div",null,[(0,t.Uk)((0,n.zw)((0,o.SU)(a)("Selected language"))+": ",1),(0,t._)("strong",null,(0,n.zw)(U.value.label),1)])])),_:1})])),_:1}),(0,t.Wm)(_),((0,t.wg)(),(0,t.iD)(t.HY,null,(0,t.Ko)(x,(e=>(0,t.wy)((0,t.Wm)(d,{clickable:"",disable:U.value.value==e.value,key:e.value,onClick:a=>Z(e,!0)},{default:(0,t.w5)((()=>[(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t._)("div",null,(0,n.zw)(e.label),1)])),_:2},1024)])),_:2},1032,["disable","onClick"]),[[I]]))),64))])),_:1})])),_:1})])),_:1})])),_:1})])),_:1}),h.value?((0,t.wg)(),(0,t.j4)(A,{key:0,modelValue:b.value,"onUpdate:modelValue":l[2]||(l[2]=e=>b.value=e),"show-if-above":"",bordered:"",class:"bg-grey-2",width:240},{default:(0,t.w5)((()=>[(0,t.Wm)(C,{class:"fit"},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{padding:""},{default:(0,t.w5)((()=>[(0,t.Wm)(m,{header:"",class:"text-weight-bold text-uppercase"},{default:(0,t.w5)((()=>[(0,t.Uk)(" Menu ")])),_:1}),((0,t.wg)(),(0,t.iD)(t.HY,null,(0,t.Ko)(y,(e=>(0,t.wy)((0,t.Wm)(d,{key:e.text,clickable:"",to:{name:e.routeName}},{default:(0,t.w5)((()=>[(0,t.Wm)(r,{avatar:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{color:"grey",name:e.icon},null,8,["name"])])),_:2},1024),(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t.Wm)(m,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)((0,o.SU)(a)(e.text)),1)])),_:2},1024)])),_:2},1024)])),_:2},1032,["to"]),[[P]]))),64)),(0,t.wy)(((0,t.wg)(),(0,t.j4)(d,{clickable:"",onClick:z},{default:(0,t.w5)((()=>[(0,t.Wm)(r,{avatar:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{color:"grey",name:"logout"})])),_:1}),(0,t.Wm)(r,null,{default:(0,t.w5)((()=>[(0,t.Wm)(m,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)((0,o.SU)(a)("Sign out")),1)])),_:1})])),_:1})])),_:1})),[[P]])])),_:1})])),_:1})])),_:1},8,["modelValue"])):(0,t.kq)("",!0),(0,t.Wm)(E,null,{default:(0,t.w5)((()=>[(0,t.Wm)(D)])),_:1})])),_:1})}}};var v=l(249),_=l(6602),h=l(1663),b=l(8879),W=l(1357),S=l(6997),k=l(490),x=l(1233),y=l(132),q=l(3246),U=l(2857),Q=l(3115),Z=l(136),z=l(7858),L=l(926),H=l(906),C=l(6663),A=l(2133),D=l(2146),E=l(1136),F=l(9984),I=l.n(F);const P=f,T=P;I()(f,"components",{QLayout:v.Z,QHeader:_.Z,QToolbar:h.Z,QBtn:b.Z,QAvatar:W.Z,QSelect:S.Z,QItem:k.Z,QItemSection:x.Z,QSpinnerPie:y.Z,QList:q.Z,QIcon:U.Z,QItemLabel:Q.Z,QSpace:Z.Z,QMenu:z.Z,QSeparator:L.Z,QDrawer:H.Z,QScrollArea:C.Z,QPageContainer:A.Z}),I()(f,"directives",{ClosePopup:D.Z,Ripple:E.Z})}}]);