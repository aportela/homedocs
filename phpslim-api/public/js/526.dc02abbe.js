"use strict";(globalThis["webpackChunkhomedocs"]=globalThis["webpackChunkhomedocs"]||[]).push([[526],{1745:(e,a,l)=>{l.d(a,{H:()=>i});var t=l(1809),n=l(1569);Array.from(window.location.host).reduce(((e,a)=>0|31*e+a.charCodeAt(0)),0);const i=(0,t.Q_)("initialState",{state:()=>({initialState:{allowSignUp:!1,maxUploadFileSize:1}}),getters:{isSignUpAllowed:e=>e.initialState.allowSignUp,maxUploadFileSize:e=>e.initialState.maxUploadFileSize},actions:{load(){n.api.common.initialState().then((e=>{this.initialState.allowSignUp=e.data.initialState.allowSignUp,this.initialState.maxUploadFileSize=e.data.initialState.maxUploadFileSize})).catch((e=>{console.error(e.response)}))}}})},9259:(e,a,l)=>{l.d(a,{Z:()=>v});var t=l(9835),n=l(499),i=l(6970),u=l(1569),s=l(6647);const o={__name:"TagSelector",props:{modelValue:Array,disabled:Boolean,dense:Boolean},emits:["update:modelValue","error"],setup(e,{emit:a}){const l=e,{t:o}=(0,s.QT)(),r=(0,n.iH)(""),d=(0,n.iH)(!1),m=(0,n.iH)(!1),c=(0,n.iH)([]),p=(0,n.iH)([]),v=(0,t.Fl)((()=>l.modelValue||[]));let f=[];function w(e,a){a(""===e?()=>{c.value=f.value}:()=>{const a=e.toLowerCase();c.value=f.filter((e=>e.toLowerCase().indexOf(a)>-1))})}function g(){m.value=!1,d.value=!0,u.api.tag.search().then((e=>{f=e.data.tags,p.value=v.value,d.value=!1})).catch((e=>{m.value=!0,d.value=!1,a("error",e.response)}))}function b(e){r.value.hidePopup()}function h(e){p.value.splice(e,1)}return(0,t.YP)(v,(e=>{p.value=e||[]})),(0,t.YP)(p,(e=>{a("update:modelValue",e||[])})),g(),(a,l)=>{const u=(0,t.up)("q-chip"),s=(0,t.up)("q-select");return(0,t.wg)(),(0,t.j4)(s,{ref_key:"selectRef",ref:r,label:(0,n.SU)(o)("Tags"),modelValue:p.value,"onUpdate:modelValue":l[0]||(l[0]=e=>p.value=e),dense:e.dense,outlined:"","use-input":"","use-chips":"",multiple:"","hide-dropdown-icon":"",options:c.value,"input-debounce":"0","new-value-mode":"add-unique",clearable:"",disable:e.disabled||d.value||m.value,loading:d.value,error:m.value,errorMessage:(0,n.SU)(o)("Error loading available tags"),onFilter:w,onAdd:b},{selected:(0,t.w5)((()=>[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(p.value,((e,a)=>((0,t.wg)(),(0,t.j4)(u,{removable:"",key:e,onRemove:e=>h(a),color:"dark","text-color":"white",icon:"label_important"},{default:(0,t.w5)((()=>[(0,t.Uk)((0,i.zw)(e),1)])),_:2},1032,["onRemove"])))),128))])),_:1},8,["label","modelValue","dense","options","disable","loading","error","errorMessage"])}}};var r=l(6997),d=l(7691),m=l(9984),c=l.n(m);const p=o,v=p;c()(o,"components",{QSelect:r.Z,QChip:d.Z})},2526:(e,a,l)=>{l.r(a),l.d(a,{default:()=>ve});l(9665);var t=l(9835),n=l(6970),i=l(499),u=l(1957),s=l(8339),o=l(796),r=l(321),d=l(4170),m=l(9302),c=l(6647),p=l(1569),v=l(9259);const f={__name:"ConfirmationModal",emits:["close","ok"],setup(e,{emit:a}){const{t:l}=(0,c.QT)(),s=(0,i.iH)(!0);function o(){s.value=!1,a("close")}function r(){s.value=!1,a("cancel")}function d(){s.value=!1,a("ok")}return(e,a)=>{const m=(0,t.up)("q-card-section"),c=(0,t.up)("q-icon"),p=(0,t.up)("q-btn"),v=(0,t.up)("q-card-actions"),f=(0,t.up)("q-card"),w=(0,t.up)("q-dialog");return(0,t.wg)(),(0,t.j4)(w,{modelValue:s.value,"onUpdate:modelValue":a[0]||(a[0]=e=>s.value=e),onHide:o},{default:(0,t.w5)((()=>[(0,t.Wm)(f,null,{default:(0,t.w5)((()=>[(0,t.Wm)(m,{class:"bg-grey-8 text-white q-p-none"},{default:(0,t.w5)((()=>[(0,t.WI)(e.$slots,"header")])),_:3}),(0,t.Wm)(m,{class:"q-p-none"},{default:(0,t.w5)((()=>[(0,t.WI)(e.$slots,"body")])),_:3}),(0,t.Wm)(v,{align:"right"},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{outline:"",onClick:(0,u.iM)(r,["stop"]),class:"bg-grey-6 text-white"},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{left:"",name:"close"}),(0,t.Uk)((0,n.zw)((0,i.SU)(l)("Cancel")),1)])),_:1},8,["onClick"]),(0,t.Wm)(p,{outline:"",onClick:(0,u.iM)(d,["stop"]),class:"bg-dark text-white"},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{left:"",name:"done"}),(0,t.Uk)((0,n.zw)((0,i.SU)(l)("Ok")),1)])),_:1},8,["onClick"])])),_:1})])),_:3})])),_:3},8,["modelValue"])}}};var w=l(2074),g=l(4458),b=l(3190),h=l(1821),k=l(8879),y=l(2857),S=l(9984),_=l.n(S);const x=f,U=x;_()(f,"components",{QDialog:w.Z,QCard:g.Z,QCardSection:b.Z,QCardActions:h.Z,QBtn:k.Z,QIcon:y.Z});const q={class:"text-h6"},z={key:0,class:"flex flex-center"},W={key:1},Z={controls:"",class:"q-mt-md",style:{width:"100%"}},D=["src"],Q={key:2},C={key:3,class:"text-subtitle1 text-center"},P={__name:"FilePreviewModal",props:{files:Array,index:Number},emits:["close"],setup(e,{emit:a}){const l=e,{t:u}=(0,c.QT)(),s=(0,i.iH)(l.index+1||1),o=(0,i.iH)(l.files?l.files.length:0);s.value>o.value&&(s.value=1);const r=(0,t.Fl)((()=>l.files&&o.value>0?l.files[s.value-1]:{id:null,name:null,humanSize:0})),d=(0,i.iH)(!0),m=(0,i.iH)(!1);function p(e){return e.match(/.(jpg|jpeg|png|gif)$/i)}function v(e){return e.match(/.(mp3)$/i)}return(l,c)=>{const f=(0,t.up)("q-space"),w=(0,t.up)("q-btn"),g=(0,t.up)("q-card-section"),b=(0,t.up)("q-banner"),h=(0,t.up)("q-pagination"),k=(0,t.up)("q-img"),y=(0,t.up)("q-icon"),S=(0,t.up)("q-card-actions"),_=(0,t.up)("q-card"),x=(0,t.up)("q-dialog"),U=(0,t.Q2)("close-popup");return(0,t.wg)(),(0,t.j4)(x,{modelValue:d.value,"onUpdate:modelValue":c[3]||(c[3]=e=>d.value=e),onHide:c[4]||(c[4]=e=>a("close"))},{default:(0,t.w5)((()=>[(0,t.Wm)(_,{style:{width:"700px","max-width":"80vw"}},{default:(0,t.w5)((()=>[(0,t.Wm)(g,{class:"row items-center bg-grey-8 text-white q-p-none"},{default:(0,t.w5)((()=>[(0,t._)("div",q,(0,n.zw)((0,i.SU)(u)("File preview")),1),(0,t.Wm)(f),(0,t.wy)((0,t.Wm)(w,{icon:"close",flat:"",round:"",dense:""},null,512),[[U]])])),_:1}),(0,t.Wm)(g,{class:"q-pt-none"},{default:(0,t.w5)((()=>[(0,t.Wm)(b,{"inline-actions":"",class:"text-center"},{default:(0,t.w5)((()=>[(0,t._)("strong",null,(0,n.zw)(r.value.name)+" ("+(0,n.zw)(r.value.humanSize)+")",1)])),_:1}),o.value>1?((0,t.wg)(),(0,t.iD)("div",z,[e.files.length>0?((0,t.wg)(),(0,t.j4)(h,{key:0,modelValue:s.value,"onUpdate:modelValue":[c[0]||(c[0]=e=>s.value=e),c[1]||(c[1]=e=>m.value=!1)],max:o.value,color:"dark","max-pages":5,"boundary-numbers":"","direction-links":"","icon-first":"skip_previous","icon-last":"skip_next","icon-prev":"fast_rewind","icon-next":"fast_forward",gutter:"md"},null,8,["modelValue","max"])):(0,t.kq)("",!0)])):(0,t.kq)("",!0)])),_:1}),(0,t.Wm)(g,{class:"q-pt-none"},{default:(0,t.w5)((()=>[(0,t._)("div",null,[p(r.value.name)?((0,t.wg)(),(0,t.j4)(k,{key:0,src:r.value.url,loading:"lazy","spinner-color":"white",onError:c[2]||(c[2]=e=>m.value=!0)},null,8,["src"])):v(r.value.name)?((0,t.wg)(),(0,t.iD)("div",W,[(0,t._)("audio",Z,[(0,t._)("source",{src:r.value.url,type:"audio/mpeg"},null,8,D),(0,t.Uk)(" "+(0,n.zw)((0,i.SU)(u)("Your browser does not support the audio element")),1)])])):((0,t.wg)(),(0,t.iD)("div",Q,[(0,t.Wm)(b,{"inline-actions":"",class:"text-white bg-grey"},{default:(0,t.w5)((()=>[(0,t.Wm)(y,{name:"error",size:"sm"}),(0,t.Uk)(" "+(0,n.zw)((0,i.SU)(u)("Preview not available")),1)])),_:1})])),m.value?((0,t.wg)(),(0,t.iD)("div",C,[(0,t.Wm)(b,{"inline-actions":"",class:"text-white bg-red"},{default:(0,t.w5)((()=>[(0,t.Wm)(y,{name:"error",size:"sm"}),(0,t.Uk)(" "+(0,n.zw)((0,i.SU)(u)("Error loading preview")),1)])),_:1})])):(0,t.kq)("",!0)]),(0,t.Wm)(S,{align:"right"},{default:(0,t.w5)((()=>[(0,t.Wm)(w,{outline:"",href:r.value.url,label:(0,i.SU)(u)("Download"),icon:"download",disable:m.value,class:"bg-grey-6 text-white"},null,8,["href","label","disable"]),(0,t.wy)((0,t.Wm)(w,{outline:"",label:(0,i.SU)(u)("Close"),icon:"close",class:"bg-dark text-white"},null,8,["label"]),[[U]])])),_:1})])),_:1})])),_:1})])),_:1},8,["modelValue"])}}};var Y=l(136),H=l(7128),T=l(996),V=l(335),M=l(2146);const I=P,A=I;_()(P,"components",{QDialog:w.Z,QCard:g.Z,QCardSection:b.Z,QSpace:Y.Z,QBtn:k.Z,QBanner:H.Z,QPagination:T.Z,QImg:V.Z,QIcon:y.Z,QCardActions:h.Z}),_()(P,"directives",{ClosePopup:M.Z});var O=l(1745);const j={class:"q-pa-md"},E={class:"row items-center q-pb-md"},F={key:0,class:"q-mt-sm q-mb-sm"},N={key:1,class:"q-mt-sm q-mb-sm"},B={class:"q-gutter-y-md"},R=["onSubmit"],$=(0,t._)("thead",null,[(0,t._)("tr",null,[(0,t._)("th",{class:"text-left"},"Created on"),(0,t._)("th",{class:"text-left"},"Name"),(0,t._)("th",{class:"text-right"},"Size"),(0,t._)("th",{class:"text-center"},"Actions")])],-1),L={class:"text-left"},K={class:"text-left"},G={class:"text-right"},J={class:"text-center"},X={class:"text-h6"},ee={class:"text-subtitle2"},ae={__name:"DocumentPage",setup(e){const a=(0,m.Z)(),{t:l}=(0,c.QT)(),f=(0,s.yj)(),w=(0,s.tv)(),g=(0,O.H)(),b=(0,t.Fl)((()=>g.maxUploadFileSize)),h=(0,i.iH)(null),k=(0,i.iH)(null),y=(0,i.iH)(!1),S=(0,i.iH)(!1),_=(0,i.iH)(!1),x=(0,i.iH)(null),q=(0,i.iH)(!1),z=(0,i.iH)(!1),W=(0,i.iH)(!1),Z=(0,i.iH)(!1),D=(0,i.iH)({id:null,title:null,description:null,created:null,createdOnTimestamp:null,createdBy:null,files:[],tags:[]});function Q(){z.value=!0,p.api.document.get(D.value.id).then((e=>{D.value=e.data.data,D.value.createdOn=d.ZP.formatDate(1e3*D.value.createdOnTimestamp,"YYYY-MM-DD HH:mm:ss"),D.value.date=d.ZP.formatDate(1e3*D.value.createdOnTimestamp,"YYYY/MM/DD"),D.value.files.map((e=>(e.isNew=!1,e.uploadedOn=d.ZP.formatDate(e.uploadedOnTimestamp,"YYYY-MM-DD HH:mm:ss"),e.humanSize=r.ZP.humanStorageSize(e.size),e.url="api2/file/"+e.id,e))),z.value=!1,x.value&&(0,t.Y3)((()=>x.value.focus()))})).catch((e=>{switch(z.value=!1,e.response.status){case 401:this.$router.push({name:"signIn"});break;default:a.notify({type:"negative",message:l("API Error: error loading document"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function C(){z.value=!0,q.value?(D.value.id||(D.value.id=(0,o.Z)()),p.api.document.add(D.value).then((e=>{z.value=!1,(0,t.Y3)((()=>{h.value.reset(),x.value.focus()})),w.push({name:"document",params:{id:D.value.id}})})).catch((e=>{switch(D.value.id=null,z.value=!1,e.response.status){case 400:e.response.data.invalidOrMissingParams.find((function(e){return"title"===e}))&&(a.notify({type:"negative",message:l("API Error: missing document title param")}),(0,t.Y3)((()=>x.value.focus())));break;case 401:this.$router.push({name:"signIn"});break;default:a.notify({type:"negative",message:l("API Error: error adding document"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))):p.api.document.update(D.value).then((e=>{D.value=e.data.data,D.value.createdOn=d.ZP.formatDate(1e3*D.value.createdOnTimestamp,"YYYY-MM-DD HH:mm:ss"),D.value.date=d.ZP.formatDate(1e3*D.value.createdOnTimestamp,"YYYY/MM/DD"),D.value.files.map((e=>(e.isNew=!1,e.url="api2/file/"+e.id,e.uploadedOn=d.ZP.formatDate(e.uploadedOnTimestamp,"YYYY-MM-DD HH:mm:ss"),e.humanSize=r.ZP.humanStorageSize(e.size),e))),z.value=!1,(0,t.Y3)((()=>{h.value.reset(),x.value.focus()}))})).catch((e=>{switch(z.value=!1,e.response.status){case 400:e.response.data.invalidOrMissingParams.find((function(e){return"title"===e}))&&(a.notify({type:"negative",message:l("API Error: missing document title param")}),(0,t.Y3)((()=>x.value.focus())));break;case 401:this.$router.push({name:"signIn"});break;default:a.notify({type:"negative",message:l("API Error: error updating document"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}function P(e){return e.match(/.(jpg|jpeg|png|gif|mp3)$/i)}function Y(e){k.value=e,y.value=!0}function H(e,a){k.value=a,S.value=!0}function T(){S.value=!1,_.value=!1}function V(){S.value?M():_.value&&ie()}function M(){k.value>-1&&(D.value.files[k.value].isNew?(z.value=!0,p.api.document.removeFile(D.value.files[k.value].id).then((e=>{D.value.files.splice(k.value,1),k.value=null,S.value=!1,z.value=!1})).catch((e=>{z.value=!1,a.notify({type:"negative",message:l("API Error: error removing file"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})})}))):(D.value.files.splice(k.value,1),k.value=null,S.value=!1))}function I(e){D.value.files.push({id:JSON.parse(e.xhr.response).data.id,uploadedOn:d.ZP.formatDate(new Date,"YYYY-MM-DD HH:mm:ss"),name:e.files[0].name,size:e.files[0].size,hash:null,humanSize:r.ZP.humanStorageSize(e.files[0].size),isNew:!0})}function ae(e){"max-file-size"==e[0].failedPropValidation?a.notify({type:"negative",message:"Can not upload file "+e[0].file.name+" (max upload filesize: "+r.ZP.humanStorageSize(b.value)+", current file size: "+r.ZP.humanStorageSize(e[0].file.size)+")"}):a.notify({type:"negative",message:l("Can not upload file",{filename:e[0].file.name})})}function le(e){a.notify({type:"negative",message:"Can not upload file "+e.files[0].name+", API error: "+e.xhr.status+" - "+e.xhr.statusText})}function te(e){Z.value=!0}function ne(e){Z.value=!1}function ie(){z.value=!0,p.api.document.remove(D.value.id).then((e=>{z.value=!1,a.notify({type:"positive",message:l("Document has been removed")}),w.push({name:"index"})})).catch((e=>{switch(_.value=!1,z.value=!1,e.response.status){case 401:this.$router.push({name:"signIn"});break;case 403:a.notify({type:"negative",message:l("Access denied")});break;case 404:a.notify({type:"negative",message:l("Document not found")});break;default:a.notify({type:"negative",message:l("API Error: error deleting document"),caption:l("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}return w.beforeEach((async(e,a)=>{"newDocument"==e.name?(D.value={id:null,title:null,description:null,created:null,createdOnTimestamp:null,date:null,createdBy:null,files:[],tags:[]},q.value=!0):("newDocument"==a.name&&"document"==e.name&&e.params.id||"newDocument"!=a.name&&"document"==e.name&&e.params.id)&&(q.value=!1,D.value.id=e.params.id,Q())})),D.value.id=f.params.id||null,D.value.id?(q.value=!1,Q()):q.value=!0,(e,a)=>{const s=(0,t.up)("q-space"),o=(0,t.up)("q-btn"),r=(0,t.up)("q-input"),d=(0,t.up)("q-uploader"),m=(0,t.up)("q-btn-group"),c=(0,t.up)("q-icon"),p=(0,t.up)("q-item-section"),f=(0,t.up)("q-item-label"),w=(0,t.up)("q-item"),g=(0,t.up)("q-list"),Q=(0,t.up)("q-btn-dropdown"),M=(0,t.up)("q-markup-table"),O=(0,t.up)("q-card-section"),ie=(0,t.up)("q-spinner-hourglass"),ue=(0,t.up)("q-card"),se=(0,t.up)("q-page"),oe=(0,t.Q2)("close-popup");return(0,t.wg)(),(0,t.j4)(se,{class:"bg-grey-2"},{default:(0,t.w5)((()=>[(0,t._)("div",j,[(0,t._)("div",E,[D.value.id?((0,t.wg)(),(0,t.iD)("h3",N,(0,n.zw)((0,i.SU)(l)("Document")),1)):((0,t.wg)(),(0,t.iD)("h3",F,(0,n.zw)((0,i.SU)(l)("New document")),1)),(0,t.Wm)(s),(0,t.Wm)(o,{icon:"save",flat:"",round:"",title:(0,i.SU)(l)("Save document"),onClick:C,disable:z.value||W.value||Z.value||!D.value.title},null,8,["title","disable"]),q.value?(0,t.kq)("",!0):((0,t.wg)(),(0,t.j4)(o,{key:2,icon:"delete",flat:"",round:"",title:(0,i.SU)(l)("Remove document"),onClick:a[0]||(a[0]=e=>_.value=!0)},null,8,["title"]))]),(0,t._)("div",B,[(0,t.Wm)(ue,null,{default:(0,t.w5)((()=>[(0,t._)("form",{onSubmit:(0,u.iM)(C,["prevent","stop"]),autocorrect:"off",autocapitalize:"off",autocomplete:"off",spellcheck:"false"},[(0,t.Wm)(O,null,{default:(0,t.w5)((()=>[D.value.id?((0,t.wg)(),(0,t.j4)(r,{key:0,class:"q-mb-md",outlined:"",mask:"date",modelValue:D.value.date,"onUpdate:modelValue":a[1]||(a[1]=e=>D.value.date=e),label:(0,i.SU)(l)("Document date"),disable:!0},null,8,["modelValue","label"])):(0,t.kq)("",!0),(0,t.Wm)(r,{class:"q-mb-md",ref_key:"titleRef",ref:x,maxlength:"128",outlined:"",modelValue:D.value.title,"onUpdate:modelValue":a[2]||(a[2]=e=>D.value.title=e),type:"text",name:"title",label:(0,i.SU)(l)("Document title"),disable:z.value||W.value,autofocus:!0},null,8,["modelValue","label","disable"]),(0,t.Wm)(r,{class:"q-mb-md",outlined:"",modelValue:D.value.description,"onUpdate:modelValue":a[3]||(a[3]=e=>D.value.description=e),type:"textarea",maxlength:"4096",autogrow:"",name:"description",label:(0,i.SU)(l)("Document description"),disable:z.value||W.value,clearble:""},null,8,["modelValue","label","disable"]),(0,t.Wm)(v.Z,{modelValue:D.value.tags,"onUpdate:modelValue":a[4]||(a[4]=e=>D.value.tags=e),disabled:z.value||W.value},null,8,["modelValue","disabled"]),(0,t.Wm)(d,{ref_key:"uploaderRef",ref:h,class:"q-mb-md",label:(0,i.SU)(l)("Add new file (Drag & Drop supported)"),flat:"",bordered:"","auto-upload":"","hide-upload-btn":"",color:"dark","field-name":"file",url:"api2/file","max-file-size":b.value,multiple:"",onUploaded:I,onRejected:ae,onFailed:le,method:"post",style:{width:"100%"},disable:z.value||W.value,"no-thumbnails":"",onStart:te,onFinish:ne},null,8,["label","max-file-size","disable"]),D.value.files.length>0?((0,t.wg)(),(0,t.j4)(M,{key:1},{default:(0,t.w5)((()=>[$,(0,t._)("tbody",null,[((0,t.wg)(!0),(0,t.iD)(t.HY,null,(0,t.Ko)(D.value.files,((e,a)=>((0,t.wg)(),(0,t.iD)("tr",{key:e.id},[(0,t._)("td",L,(0,n.zw)(e.uploadedOn),1),(0,t._)("td",K,(0,n.zw)(e.name),1),(0,t._)("td",G,(0,n.zw)(e.humanSize),1),(0,t._)("td",J,[(0,t.Wm)(m,{spread:"",class:"desktop-only",disable:z.value},{default:(0,t.w5)((()=>[(0,t.Wm)(o,{label:(0,i.SU)(l)("Open/Preview"),icon:"preview",onClick:(0,u.iM)((e=>Y(a)),["prevent"]),disable:z.value||!P(e.name)||e.isNew},null,8,["label","onClick","disable"]),(0,t.Wm)(o,{label:(0,i.SU)(l)("Download"),icon:"download",href:e.url,disable:z.value||e.isNew},null,8,["label","href","disable"]),(0,t.Wm)(o,{label:(0,i.SU)(l)("Remove"),icon:"delete",disable:z.value,onClick:(0,u.iM)((l=>H(e,a)),["prevent"])},null,8,["label","disable","onClick"])])),_:2},1032,["disable"]),(0,t.Wm)(Q,{label:"Operations",class:"mobile-only",disable:z.value},{default:(0,t.w5)((()=>[(0,t.Wm)(g,null,{default:(0,t.w5)((()=>[(0,t.wy)(((0,t.wg)(),(0,t.j4)(w,{clickable:"",onClick:(0,u.iM)((e=>Y(a)),["prevent"]),disable:z.value||!P(e.name)||e.isNew},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{avatar:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{name:"preview"})])),_:1}),(0,t.Wm)(p,null,{default:(0,t.w5)((()=>[(0,t.Wm)(f,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)((0,i.SU)(l)("Open/Preview")),1)])),_:1})])),_:1})])),_:2},1032,["onClick","disable"])),[[oe]]),(0,t.wy)(((0,t.wg)(),(0,t.j4)(w,{clickable:"",href:e.url,disable:z.value||e.isNew},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{avatar:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{name:"download"})])),_:1}),(0,t.Wm)(p,null,{default:(0,t.w5)((()=>[(0,t.Wm)(f,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)((0,i.SU)(l)("Download")),1)])),_:1})])),_:1})])),_:2},1032,["href","disable"])),[[oe]]),(0,t.wy)(((0,t.wg)(),(0,t.j4)(w,{clickable:"",onClick:(0,u.iM)((l=>H(e,a)),["prevent"]),disable:z.value},{default:(0,t.w5)((()=>[(0,t.Wm)(p,{avatar:""},{default:(0,t.w5)((()=>[(0,t.Wm)(c,{name:"delete"})])),_:1}),(0,t.Wm)(p,null,{default:(0,t.w5)((()=>[(0,t.Wm)(f,null,{default:(0,t.w5)((()=>[(0,t.Uk)((0,n.zw)((0,i.SU)(l)("Remove")),1)])),_:1})])),_:1})])),_:2},1032,["onClick","disable"])),[[oe]])])),_:2},1024)])),_:2},1032,["disable"])])])))),128))])])),_:1})):(0,t.kq)("",!0)])),_:1}),(0,t.Wm)(O,null,{default:(0,t.w5)((()=>[(0,t.Wm)(o,{label:(0,i.SU)(l)("Save changes"),type:"submit",icon:"save",class:"full-width",color:"dark",disable:z.value||W.value||Z.value||!D.value.title},(0,t.Nv)({_:2},[W.value?{name:"loading",fn:(0,t.w5)((()=>[(0,t.Wm)(ie,{class:"on-left"}),(0,t.Uk)(" "+(0,n.zw)((0,i.SU)(l)("Saving...")),1)])),key:"0"}:void 0]),1032,["label","disable"])])),_:1})],40,R)])),_:1})])]),y.value?((0,t.wg)(),(0,t.j4)(A,{key:0,files:D.value.files,index:k.value,onClose:a[5]||(a[5]=e=>y.value=!1)},null,8,["files","index"])):(0,t.kq)("",!0),S.value||_.value?((0,t.wg)(),(0,t.j4)(U,{key:1,onClose:T,onCancel:T,onOk:V},(0,t.Nv)({_:2},[S.value?{name:"header",fn:(0,t.w5)((()=>[(0,t._)("div",X,(0,n.zw)((0,i.SU)(l)("Remove document file")),1),(0,t._)("div",ee,(0,n.zw)(D.value.files[k.value].name),1)])),key:"0"}:_.value?{name:"header",fn:(0,t.w5)((()=>[(0,t._)("div",{class:"text-h6"},(0,n.zw)((0,i.SU)(l)("Delete document")),1),(0,t._)("div",{class:"text-subtitle2"},(0,n.zw)((0,i.SU)(l)("Document title")+": "+D.value.title),1)])),key:"1"}:void 0,S.value?{name:"body",fn:(0,t.w5)((()=>[(0,t._)("strong",null,(0,n.zw)((0,i.SU)(l)("Are you sure ? (You must save the document after deleting this file)")),1)])),key:"2"}:_.value?{name:"body",fn:(0,t.w5)((()=>[(0,t._)("strong",null,(0,n.zw)((0,i.SU)(l)("This operation cannot be undone. Would you like to proceed ?")),1)])),key:"3"}:void 0]),1024)):(0,t.kq)("",!0)])),_:1})}}};var le=l(9885),te=l(6611),ne=l(4343),ie=l(6933),ue=l(7236),se=l(2045),oe=l(3246),re=l(490),de=l(1233),me=l(3115),ce=l(3358);const pe=ae,ve=pe;_()(ae,"components",{QPage:le.Z,QSpace:Y.Z,QBtn:k.Z,QCard:g.Z,QCardSection:b.Z,QInput:te.Z,QUploader:ne.Z,QMarkupTable:ie.Z,QBtnGroup:ue.Z,QBtnDropdown:se.Z,QList:oe.Z,QItem:re.Z,QItemSection:de.Z,QIcon:y.Z,QItemLabel:me.Z,QSpinnerHourglass:ce.Z}),_()(ae,"directives",{ClosePopup:M.Z})}}]);