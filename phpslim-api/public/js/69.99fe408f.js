"use strict";(globalThis["webpackChunkhomedocs"]=globalThis["webpackChunkhomedocs"]||[]).push([[69],{4069:(e,t,a)=>{a.r(t),a.d(t,{default:()=>O});var l=a(9835),n=a(499),r=a(6970),s=a(6647),o=a(4170),c=a(9302),u=a(1569);const i={key:0,class:"text-center"},d={key:1},m={class:"text-left"},p={class:"text-left"},g={class:"text-right"},w={class:"text-left"},v={class:"text-left"},f={class:"text-right"},k={__name:"RecentDocuments",setup(e){const{t}=(0,s.QT)(),a=(0,c.Z)(),k=(0,n.iH)(!1),h=(0,n.iH)(!1);let _=!a.screen.lt.md,y=[],x=!1;function b(){y=[],x=!1,h.value=!0,k.value=!1,u.api.document.searchRecent(16).then((e=>{y=e.data.recentDocuments.map((e=>(e.createdOn=o.ZP.formatDate(1e3*e.createdOnTimestamp,"YYYY-MM-DD HH:mm:ss"),e))),x=y.length>0,h.value=!1})).catch((e=>{h.value=!1,k.value=!0,a.notify({type:"negative",message:t("API Error: fatal error"),caption:t("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}return b(),(e,a)=>{const s=(0,l.up)("q-spinner-pie"),o=(0,l.up)("router-link"),c=(0,l.up)("q-markup-table"),u=(0,l.up)("q-icon"),b=(0,l.up)("q-banner"),q=(0,l.up)("q-expansion-item"),S=(0,l.up)("q-card-section"),U=(0,l.up)("q-card");return(0,l.wg)(),(0,l.j4)(U,{class:"my-card fit",flat:"",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(S,null,{default:(0,l.w5)((()=>[(0,l.Wm)(q,{"header-class":k.value?"bg-red":"","expand-separator":"",icon:k.value?"error":"work_history",label:(0,n.SU)(t)("Recent documents"),caption:(0,n.SU)(t)(k.value?"Error loading data":"Click on title to open document"),"model-value":(0,n.SU)(_)},{default:(0,l.w5)((()=>[h.value?((0,l.wg)(),(0,l.iD)("p",i,[h.value?((0,l.wg)(),(0,l.j4)(s,{key:0,color:"grey-5",size:"md"})):(0,l.kq)("",!0)])):((0,l.wg)(),(0,l.iD)("div",d,[(0,n.SU)(x)?((0,l.wg)(),(0,l.j4)(c,{key:0},{default:(0,l.w5)((()=>[(0,l._)("thead",null,[(0,l._)("tr",null,[(0,l._)("th",m,(0,r.zw)((0,n.SU)(t)("Title")),1),(0,l._)("th",p,(0,r.zw)((0,n.SU)(t)("Created on")),1),(0,l._)("th",g,(0,r.zw)((0,n.SU)(t)("Files")),1)])]),(0,l._)("tbody",null,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)((0,n.SU)(y),(e=>((0,l.wg)(),(0,l.iD)("tr",{key:e.id},[(0,l._)("td",w,[(0,l.Wm)(o,{to:{name:"document",params:{id:e.id}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,r.zw)(e.title),1)])),_:2},1032,["to"])]),(0,l._)("td",v,(0,r.zw)(e.createdOn),1),(0,l._)("td",f,(0,r.zw)(e.fileCount),1)])))),128))])])),_:1})):k.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.j4)(b,{key:1,class:"bg-grey text-white"},{default:(0,l.w5)((()=>[(0,l.Wm)(u,{name:"info",size:"md",class:"q-mr-sm"}),(0,l.Uk)(" "+(0,r.zw)((0,n.SU)(t)("You haven't created any documents yet")),1)])),_:1}))]))])),_:1},8,["header-class","icon","label","caption","model-value"])])),_:1})])),_:1})}}};var h=a(4458),_=a(3190),y=a(1123),x=a(132),b=a(6933),q=a(7128),S=a(2857),U=a(9984),Z=a.n(U);const Q=k,z=Q;Z()(k,"components",{QCard:h.Z,QCardSection:_.Z,QExpansionItem:y.Z,QSpinnerPie:x.Z,QMarkupTable:b.Z,QBanner:q.Z,QIcon:S.Z});const C={key:0,class:"text-center"},T={key:1},D={__name:"TagCloud",setup(e){const{t}=(0,s.QT)(),a=(0,c.Z)(),o=(0,n.iH)(!1),i=(0,n.iH)(!1);let d=!a.screen.lt.md,m=[],p=!1;function g(){m=[],p=!1,i.value=!0,o.value=!1,u.api.tag.getCloud().then((e=>{m=e.data.tags,p=m.length>0,i.value=!1})).catch((e=>{i.value=!1,o.value=!0,a.notify({type:"negative",message:t("API Error: fatal error"),caption:t("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))}return g(),(e,a)=>{const s=(0,l.up)("q-spinner-pie"),c=(0,l.up)("q-avatar"),u=(0,l.up)("router-link"),g=(0,l.up)("q-chip"),w=(0,l.up)("q-icon"),v=(0,l.up)("q-banner"),f=(0,l.up)("q-expansion-item"),k=(0,l.up)("q-card-section"),h=(0,l.up)("q-card");return(0,l.wg)(),(0,l.j4)(h,{class:"my-card fit",flat:"",bordered:""},{default:(0,l.w5)((()=>[(0,l.Wm)(k,null,{default:(0,l.w5)((()=>[(0,l.Wm)(f,{"header-class":o.value?"bg-red":"","expand-separator":"",icon:o.value?"error":"bookmark",label:(0,n.SU)(t)("Tag cloud"),caption:(0,n.SU)(t)(o.value?"Error loading data":"Click on tag to browse by tag"),"model-value":(0,n.SU)(d)},{default:(0,l.w5)((()=>[i.value?((0,l.wg)(),(0,l.iD)("p",C,[i.value?((0,l.wg)(),(0,l.j4)(s,{key:0,color:"grey-5",size:"md"})):(0,l.kq)("",!0)])):(0,l.kq)("",!0),(0,n.SU)(p)?((0,l.wg)(),(0,l.iD)("div",T,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)((0,n.SU)(m),(e=>((0,l.wg)(),(0,l.j4)(g,{square:"",outline:"","text-color":"dark",key:e.tag,title:(0,n.SU)(t)("Click here to browse documents containing this tag")},{default:(0,l.w5)((()=>[(0,l.Wm)(c,{color:"grey-9","text-color":"white"},{default:(0,l.w5)((()=>[(0,l.Uk)((0,r.zw)(e.total),1)])),_:2},1024),(0,l.Wm)(u,{to:{name:"advancedSearchByTag",params:{tag:e.tag}},style:{"text-decoration":"none"},class:"text-dark"},{default:(0,l.w5)((()=>[(0,l.Uk)((0,r.zw)(e.tag),1)])),_:2},1032,["to"])])),_:2},1032,["title"])))),128))])):o.value?(0,l.kq)("",!0):((0,l.wg)(),(0,l.j4)(v,{key:2,class:"bg-grey text-white"},{default:(0,l.w5)((()=>[(0,l.Wm)(w,{name:"info",size:"md",class:"q-mr-sm"}),(0,l.Uk)(" "+(0,r.zw)((0,n.SU)(t)("You haven't created any tags yet")),1)])),_:1}))])),_:1},8,["header-class","icon","label","caption","model-value"])])),_:1})])),_:1})}}};var W=a(7691),j=a(1357);const I=D,P=I;Z()(D,"components",{QCard:h.Z,QCardSection:_.Z,QExpansionItem:y.Z,QSpinnerPie:x.Z,QChip:W.Z,QAvatar:j.Z,QBanner:q.Z,QIcon:S.Z});const E={class:"row"},H={class:"col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex"},Y={class:"col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex"},A={__name:"IndexPage",setup(e){return(e,t)=>{const a=(0,l.up)("q-page");return(0,l.wg)(),(0,l.j4)(a,{class:"bg-grey-2"},{default:(0,l.w5)((()=>[(0,l._)("div",E,[(0,l._)("div",H,[(0,l.Wm)(z)]),(0,l._)("div",Y,[(0,l.Wm)(P)])])])),_:1})}}};var B=a(9885);const M=A,O=M;Z()(A,"components",{QPage:B.Z})}}]);