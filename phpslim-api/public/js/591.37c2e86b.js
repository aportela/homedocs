"use strict";(globalThis["webpackChunkhomedocs"]=globalThis["webpackChunkhomedocs"]||[]).push([[591],{5320:(e,a,t)=>{t.d(a,{Z:()=>D});var l=t(9835),r=t(499),o=t(6970),s=t(1569),i=t(3276);const d={__name:"TagSelector",props:{modelValue:Array,disabled:Boolean,dense:Boolean},emits:["update:modelValue","error"],setup(e,{emit:a}){const t=e,{t:d}=(0,i.QT)(),n=(0,r.iH)(""),u=(0,r.iH)(!1),m=(0,r.iH)(!1),c=(0,r.iH)([]),f=(0,r.iH)([]),D=(0,r.iH)([]),p=(0,l.Fl)((()=>t.modelValue||[]));function g(e,a){a(""===e?()=>{f.value=c.value}:()=>{const a=e.toLowerCase();f.value=c.value.filter((e=>e.toLowerCase().indexOf(a)>-1))})}function b(){m.value=!1,u.value=!0,s.api.tag.search().then((e=>{c.value=e.data.tags,D.value=p.value,u.value=!1})).catch((e=>{m.value=!0,u.value=!1,a("error",e.response)}))}function v(e){n.value.hidePopup()}function h(e){D.value.splice(e,1)}return(0,l.YP)(p,(e=>{D.value=e||[]})),(0,l.YP)(D,(e=>{a("update:modelValue",e||[])})),b(),(a,t)=>{const s=(0,l.up)("q-chip"),i=(0,l.up)("q-select");return(0,l.wg)(),(0,l.j4)(i,{ref_key:"selectRef",ref:n,label:(0,r.SU)(d)("Tags"),modelValue:D.value,"onUpdate:modelValue":t[0]||(t[0]=e=>D.value=e),dense:e.dense,outlined:"","use-input":"","use-chips":"",multiple:"","hide-dropdown-icon":"",options:f.value,"input-debounce":"0","new-value-mode":"add-unique",clearable:"",disable:e.disabled||u.value||m.value,loading:u.value,error:m.value,errorMessage:(0,r.SU)(d)("Error loading available tags"),onFilter:g,onAdd:v},{selected:(0,l.w5)((()=>[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)(D.value,((e,a)=>((0,l.wg)(),(0,l.j4)(s,{removable:"",key:e,onRemove:e=>h(a),color:"dark","text-color":"white",icon:"label_important"},{default:(0,l.w5)((()=>[(0,l.Uk)((0,o.zw)(e),1)])),_:2},1032,["onRemove"])))),128))])),_:1},8,["label","modelValue","dense","options","disable","loading","error","errorMessage"])}}};var n=t(6997),u=t(7691),m=t(9984),c=t.n(m);const f=d,D=f;c()(d,"components",{QSelect:n.Z,QChip:u.Z})},8591:(e,a,t)=>{t.r(a),t.d(a,{default:()=>$});t(9665);var l=t(9835),r=t(6970),o=t(499),s=t(1957),i=t(8339),d=t(4170),n=t(9302),u=t(3276),m=t(1569),c=t(1809);const f=(0,c.Q_)("advancedSearchData",{state:()=>({filter:{title:null,description:null,dateFilterType:null,dateRange:null,fromDate:null,toDate:null,fromTimestamp:null,toTimestamp:null,tags:[]},sort:{field:"createdOnTimestamp",order:"DESC"},pager:{currentPage:1,resultsPage:32,totalResults:0,totalPages:0},results:[]}),getters:{isSortAscending:e=>"ASC"==e.sort,isSortDescending:e=>"DESC"==e.sort,sortField:e=>e.sort.field,sortOrder:e=>e.sort.order,hasResults:e=>e.results&&e.results.length>0,hasFromDateFilter:e=>e.filter.dateFilterType&&[3,4,5,6,8,10].includes(e.filter.dateFilterType.value),hasToDateFilter:e=>e.filter.dateFilterType&&[3,4,5,6,9,10].includes(e.filter.dateFilterType.value),hasFixedDateFilter:e=>e.filter.dateFilterType&&[1,2,7].includes(e.filter.dateFilterType.value),denyChangeDateFilters:e=>e.filter.dateFilterType&&[1,2,3,4,5,6].includes(e.filter.dateFilterType.value)},actions:{isSortedByField(e){return this.sort.field==e},toggleSort(e){this.sort.field==e?this.sort.order="ASC"==this.sort.order?"DESC":"ASC":(this.sort.field=e,this.sort.order="ASC")},setCurrentPage(e){advancedSearchData.pager.currentPage=e},recalcDates(e){switch(e.value){case 0:this.filter.fromDate=null,this.filter.fromTimestamp=null,this.filter.toDate=null,this.filter.toTimestamp=null;break;case 1:this.filter.fromDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD");break;case 2:this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD");break;case 3:this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-7}),"YYYY/MM/DD"),this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD");break;case 4:this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-15}),"YYYY/MM/DD"),this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD");break;case 5:this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-31}),"YYYY/MM/DD"),this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD");break;case 6:this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-365}),"YYYY/MM/DD"),this.filter.fromDate=d.ZP.formatDate(d.ZP.addToDate(Date.now(),{days:-1}),"YYYY/MM/DD");break;case 7:this.filter.fromDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD");break;case 8:this.filter.fromDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD");break;case 9:this.filter.fromDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD");break;case 10:this.filter.fromDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD"),this.filter.toDate=d.ZP.formatDate(Date.now(),"YYYY/MM/DD");break}}}});var D=t(5320);const p={class:"q-pa-md"},g={class:"q-mt-sm"},b={class:"q-gutter-y-md"},v={class:"row"},h={class:"col"},w={key:0,class:"col"},Y={class:"row items-center justify-end"},y={key:1,class:"col"},S={class:"row items-center justify-end"},U={key:2,class:"col"},P={class:"row items-center justify-end"},k={key:0,class:"q-pa-lg flex flex-center"},_={class:"text-left"},Z={class:"text-left"},M={class:"text-right"},T={key:1,class:"q-pa-lg flex flex-center"},F={__name:"AdvancedSearchPage",setup(e){const a=(0,n.Z)(),{t}=(0,u.QT)(),c=(0,i.yj)(),F=f(),V=(0,o.iH)(!1),q=(0,o.iH)(!1),C=(0,o.iH)(!0),x=(0,o.iH)(!1),W=(0,o.iH)([{label:t("Any date"),value:0},{label:t("Today"),value:1},{label:t("Yesterday"),value:2},{label:t("Last 7 days"),value:3},{label:t("Last 15 days"),value:4},{label:t("Last 31 days"),value:5},{label:t("Last 365 days"),value:6},{label:t("Fixed date"),value:7},{label:t("From date"),value:8},{label:t("To date"),value:9},{label:t("Between dates"),value:10}]);F.filter.dateFilterType=W.value[0],F.filter.tags=void 0!==c.params.tag?[c.params.tag]:[];const Q=(0,l.Fl)((()=>F.isSortAscending?"keyboard_double_arrow_up":"keyboard_double_arrow_down"));function H(e){e&&(F.pager.currentPage=1),V.value=!0,d.ZP.isValid(F.filter.fromDate)?F.filter.fromTimestamp=d.ZP.formatDate(d.ZP.adjustDate(d.ZP.extractDate(F.filter.fromDate,"YYYY/MM/DD"),{hour:0,minute:0,second:0,millisecond:0}),"X"):F.filter.fromTimestamp=null,d.ZP.isValid(F.filter.toDate)?F.filter.toTimestamp=d.ZP.formatDate(d.ZP.adjustDate(d.ZP.extractDate(F.filter.toDate,"YYYY/MM/DD"),{hour:23,minute:59,second:59,millisecond:999}),"X"):F.filter.toTimestamp=null,m.api.document.search(F.pager.currentPage,F.pager.resultsPage,F.filter,F.sortField,F.sortOrder).then((e=>{F.pager=e.data.results.pagination,F.results=e.data.results.documents.map((e=>(e.createdOn=d.ZP.formatDate(1e3*e.createdOnTimestamp,"YYYY-MM-DD HH:mm:ss"),e))),V.value=!1,q.value=!0,F.hasResults&&(x.value=!0)})).catch((e=>{switch(e.response.status){case 400:a.notify({type:"negative",message:t("API Error: invalid/missing param")});break;case 401:this.$router.push({name:"signIn"});break;default:a.notify({type:"negative",message:t("API Error: fatal error"),caption:t("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}V.value=!1}))}function j(e){F.setCurrentPage(e),H(!1)}function z(e){F.toggleSort(e),H(!1)}return(0,l.YP)((()=>F.filter.dateFilterType),(e=>{F.recalcDates(e)})),F.filter.tags.length>0&&H(!0),(e,a)=>{const i=(0,l.up)("q-icon"),d=(0,l.up)("q-input"),n=(0,l.up)("q-select"),u=(0,l.up)("q-btn"),m=(0,l.up)("q-date"),c=(0,l.up)("q-popup-proxy"),f=(0,l.up)("q-spinner-hourglass"),A=(0,l.up)("q-expansion-item"),R=(0,l.up)("q-card-section"),B=(0,l.up)("q-card"),O=(0,l.up)("q-pagination"),E=(0,l.up)("router-link"),I=(0,l.up)("q-markup-table"),L=(0,l.up)("q-banner"),K=(0,l.up)("q-page"),X=(0,l.Q2)("close-popup");return(0,l.wg)(),(0,l.j4)(K,{class:"bg-grey-2"},{default:(0,l.w5)((()=>[(0,l._)("div",p,[(0,l._)("h3",g,(0,r.zw)((0,o.SU)(t)("Advanced search")),1),(0,l._)("div",b,[(0,l.Wm)(B,null,{default:(0,l.w5)((()=>[(0,l.Wm)(R,null,{default:(0,l.w5)((()=>[(0,l.Wm)(A,{"expand-separator":"",icon:"filter_alt",label:(0,o.SU)(t)("Conditions"),"model-value":C.value},{default:(0,l.w5)((()=>[(0,l._)("form",{onSubmit:a[10]||(a[10]=(0,s.iM)((e=>H(!0)),["prevent","stop"])),autocorrect:"off",autocapitalize:"off",autocomplete:"off",spellcheck:"false",class:"q-mt-md"},[(0,l.Wm)(d,{class:"q-mb-md",dense:"",outlined:"",modelValue:(0,o.SU)(F).filter.title,"onUpdate:modelValue":a[0]||(a[0]=e=>(0,o.SU)(F).filter.title=e),type:"text",name:"title",clearable:"",label:(0,o.SU)(t)("Document title"),disable:V.value,autofocus:!0},{prepend:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"search"})])),_:1},8,["modelValue","label","disable"]),(0,l.Wm)(d,{class:"q-mb-md",dense:"",outlined:"",modelValue:(0,o.SU)(F).filter.description,"onUpdate:modelValue":a[1]||(a[1]=e=>(0,o.SU)(F).filter.description=e),type:"text",name:"description",clearable:"",label:(0,o.SU)(t)("Document description"),disable:V.value},{prepend:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"search"})])),_:1},8,["modelValue","label","disable"]),(0,l._)("div",v,[(0,l._)("div",h,[(0,l.Wm)(n,{class:"q-mb-md",dense:"",outlined:"",modelValue:(0,o.SU)(F).filter.dateFilterType,"onUpdate:modelValue":a[2]||(a[2]=e=>(0,o.SU)(F).filter.dateFilterType=e),options:W.value,label:(0,o.SU)(t)("Document date"),disable:V.value},null,8,["modelValue","options","label","disable"])]),(0,o.SU)(F).hasFromDateFilter?((0,l.wg)(),(0,l.iD)("div",w,[(0,l.Wm)(d,{dense:"",outlined:"",mask:"date",modelValue:(0,o.SU)(F).filter.fromDate,"onUpdate:modelValue":a[4]||(a[4]=e=>(0,o.SU)(F).filter.fromDate=e),label:(0,o.SU)(t)("From date"),disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{append:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"event",class:"cursor-pointer"},{default:(0,l.w5)((()=>[(0,l.Wm)(c,{cover:"","transition-show":"scale","transition-hide":"scale"},{default:(0,l.w5)((()=>[(0,l.Wm)(m,{modelValue:(0,o.SU)(F).filter.fromDate,"onUpdate:modelValue":a[3]||(a[3]=e=>(0,o.SU)(F).filter.fromDate=e),"today-btn":"",disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{default:(0,l.w5)((()=>[(0,l._)("div",Y,[(0,l.wy)((0,l.Wm)(u,{label:"Close",color:"primary",flat:""},null,512),[[X]])])])),_:1},8,["modelValue","disable"])])),_:1})])),_:1})])),_:1},8,["modelValue","label","disable"])])):(0,l.kq)("",!0),(0,o.SU)(F).hasToDateFilter?((0,l.wg)(),(0,l.iD)("div",y,[(0,l.Wm)(d,{dense:"",outlined:"",mask:"date",modelValue:(0,o.SU)(F).filter.toDate,"onUpdate:modelValue":a[6]||(a[6]=e=>(0,o.SU)(F).filter.toDate=e),label:(0,o.SU)(t)("To date"),disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{append:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"event",class:"cursor-pointer"},{default:(0,l.w5)((()=>[(0,l.Wm)(c,{cover:"","transition-show":"scale","transition-hide":"scale"},{default:(0,l.w5)((()=>[(0,l.Wm)(m,{modelValue:(0,o.SU)(F).filter.toDate,"onUpdate:modelValue":a[5]||(a[5]=e=>(0,o.SU)(F).filter.toDate=e),"today-btn":"",disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{default:(0,l.w5)((()=>[(0,l._)("div",S,[(0,l.wy)((0,l.Wm)(u,{label:"Close",color:"primary",flat:""},null,512),[[X]])])])),_:1},8,["modelValue","disable"])])),_:1})])),_:1})])),_:1},8,["modelValue","label","disable"])])):(0,l.kq)("",!0),(0,o.SU)(F).hasFixedDateFilter?((0,l.wg)(),(0,l.iD)("div",U,[(0,l.Wm)(d,{dense:"",outlined:"",mask:"date",modelValue:(0,o.SU)(F).filter.fromDate,"onUpdate:modelValue":a[8]||(a[8]=e=>(0,o.SU)(F).filter.fromDate=e),label:(0,o.SU)(t)("Fixed date"),disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{append:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"event",class:"cursor-pointer"},{default:(0,l.w5)((()=>[(0,l.Wm)(c,{cover:"","transition-show":"scale","transition-hide":"scale"},{default:(0,l.w5)((()=>[(0,l.Wm)(m,{modelValue:(0,o.SU)(F).filter.fromDate,"onUpdate:modelValue":a[7]||(a[7]=e=>(0,o.SU)(F).filter.fromDate=e),"today-btn":"",disable:V.value||(0,o.SU)(F).denyChangeDateFilters},{default:(0,l.w5)((()=>[(0,l._)("div",P,[(0,l.wy)((0,l.Wm)(u,{label:"Close",color:"primary",flat:""},null,512),[[X]])])])),_:1},8,["modelValue","disable"])])),_:1})])),_:1})])),_:1},8,["modelValue","label","disable"])])):(0,l.kq)("",!0)]),(0,l.Wm)(D.Z,{modelValue:(0,o.SU)(F).filter.tags,"onUpdate:modelValue":a[9]||(a[9]=e=>(0,o.SU)(F).filter.tags=e),disabled:V.value||(0,o.SU)(F).denyChangeDateFilters,dense:""},null,8,["modelValue","disabled"]),(0,l.Wm)(u,{color:"dark",size:"md",label:e.$t("Search"),"no-caps":"",class:"full-width",icon:"search",disable:V.value,loading:V.value,type:"submit"},{loading:(0,l.w5)((()=>[(0,l.Wm)(f,{class:"on-left"}),(0,l.Uk)(" "+(0,r.zw)((0,o.SU)(t)("Searching...")),1)])),_:1},8,["label","disable","loading"])],32)])),_:1},8,["label","model-value"])])),_:1})])),_:1}),q.value?((0,l.wg)(),(0,l.j4)(B,{key:0},{default:(0,l.w5)((()=>[(0,l.Wm)(R,null,{default:(0,l.w5)((()=>[(0,o.SU)(F).hasResults?((0,l.wg)(),(0,l.j4)(A,{key:0,"expand-separator":"",icon:"folder_open",label:(0,o.SU)(t)("Results")+" ("+(0,o.SU)(F).pager.totalResults+")","model-value":x.value},{default:(0,l.w5)((()=>[(0,o.SU)(F).pager.totalPages>1?((0,l.wg)(),(0,l.iD)("div",k,[(0,l.Wm)(O,{modelValue:(0,o.SU)(F).pager.currentPage,"onUpdate:modelValue":[a[11]||(a[11]=e=>(0,o.SU)(F).pager.currentPage=e),j],color:"dark",max:(0,o.SU)(F).pager.totalPages,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:V.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0),(0,l.Wm)(I,null,{default:(0,l.w5)((()=>[(0,l._)("thead",null,[(0,l._)("tr",null,[(0,l._)("th",{style:{width:"60%"},class:"text-left cursor-pointer",onClick:a[12]||(a[12]=e=>z("title"))},[(0,l.Uk)((0,r.zw)((0,o.SU)(t)("Title"))+" ",1),(0,o.SU)(F).isSortedByField("title")?((0,l.wg)(),(0,l.j4)(i,{key:0,name:Q.value,size:"sm"},null,8,["name"])):(0,l.kq)("",!0)]),(0,l._)("th",{style:{width:"20%"},class:"text-left cursor-pointer",onClick:a[13]||(a[13]=e=>z("createdOnTimestamp"))},[(0,l.Uk)((0,r.zw)((0,o.SU)(t)("Date"))+" ",1),(0,o.SU)(F).isSortedByField("createdOnTimestamp")?((0,l.wg)(),(0,l.j4)(i,{key:0,name:Q.value,size:" sm"},null,8,["name"])):(0,l.kq)("",!0)]),(0,l._)("th",{style:{width:"10%"},class:"text-right cursor-pointer",onClick:a[14]||(a[14]=e=>z("fileCount"))},[(0,l.Uk)((0,r.zw)((0,o.SU)(t)("Files")),1),(0,o.SU)(F).isSortedByField("fileCount")?((0,l.wg)(),(0,l.j4)(i,{key:0,name:Q.value,size:"sm"},null,8,["name"])):(0,l.kq)("",!0)])])]),(0,l._)("tbody",null,[((0,l.wg)(!0),(0,l.iD)(l.HY,null,(0,l.Ko)((0,o.SU)(F).results,(e=>((0,l.wg)(),(0,l.iD)("tr",{key:e.id},[(0,l._)("td",_,[(0,l.Wm)(E,{to:{name:"document",params:{id:e.id}}},{default:(0,l.w5)((()=>[(0,l.Uk)((0,r.zw)(e.title),1)])),_:2},1032,["to"])]),(0,l._)("td",Z,(0,r.zw)(e.createdOn),1),(0,l._)("td",M,(0,r.zw)(e.fileCount),1)])))),128))])])),_:1}),(0,o.SU)(F).pager.totalPages>1?((0,l.wg)(),(0,l.iD)("div",T,[(0,l.Wm)(O,{modelValue:(0,o.SU)(F).pager.currentPage,"onUpdate:modelValue":[a[15]||(a[15]=e=>(0,o.SU)(F).pager.currentPage=e),j],color:"dark",max:(0,o.SU)(F).pager.totalPages,"max-pages":5,"boundary-numbers":"","direction-links":"","boundary-links":"",disable:V.value},null,8,["modelValue","max","disable"])])):(0,l.kq)("",!0)])),_:1},8,["label","model-value"])):((0,l.wg)(),(0,l.j4)(L,{key:1,dense:""},{avatar:(0,l.w5)((()=>[(0,l.Wm)(i,{name:"error"})])),default:(0,l.w5)((()=>[(0,l.Uk)(" "+(0,r.zw)((0,o.SU)(t)("No results found with current filter")),1)])),_:1}))])),_:1})])),_:1})):(0,l.kq)("",!0)])])])),_:1})}}};var V=t(9885),q=t(4458),C=t(3190),x=t(1123),W=t(6611),Q=t(2857),H=t(6997),j=t(2765),z=t(6711),A=t(8879),R=t(3358),B=t(996),O=t(6933),E=t(7128),I=t(2146),L=t(9984),K=t.n(L);const X=F,$=X;K()(F,"components",{QPage:V.Z,QCard:q.Z,QCardSection:C.Z,QExpansionItem:x.Z,QInput:W.Z,QIcon:Q.Z,QSelect:H.Z,QPopupProxy:j.Z,QDate:z.Z,QBtn:A.Z,QSpinnerHourglass:R.Z,QPagination:B.Z,QMarkupTable:O.Z,QBanner:E.Z}),K()(F,"directives",{ClosePopup:I.Z})}}]);