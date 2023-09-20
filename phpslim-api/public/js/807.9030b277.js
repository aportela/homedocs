"use strict";(globalThis["webpackChunkhomedocs"]=globalThis["webpackChunkhomedocs"]||[]).push([[807],{1745:(e,a,l)=>{l.d(a,{H:()=>r});var s=l(1809),t=l(1569);Array.from(window.location.host).reduce(((e,a)=>0|31*e+a.charCodeAt(0)),0);const r=(0,s.Q_)("initialState",{state:()=>({initialState:{allowSignUp:!1,maxUploadFileSize:1}}),getters:{isSignUpAllowed:e=>e.initialState.allowSignUp,maxUploadFileSize:e=>e.initialState.maxUploadFileSize},actions:{load(){t.api.common.initialState().then((e=>{this.initialState.allowSignUp=e.data.initialState.allowSignUp,this.initialState.maxUploadFileSize=e.data.initialState.maxUploadFileSize})).catch((e=>{console.error(e.response)}))}}})},3807:(e,a,l)=>{l.r(a),l.d(a,{default:()=>W});l(9665);var s=l(9835),t=l(1957),r=l(6970),i=l(499),o=l(796),n=l(9302),u=l(8339),d=l(6647),m=l(1569),c=l(1745);const p=["onSubmit"],g={class:"text-grey-8"},v={class:"text-grey-8"},f={class:"text-dark text-weight-bold",style:{"text-decoration":"none"}},w={class:"text-red-8 text-bold"},h={__name:"SignUpPage",setup(e){const{t:a}=(0,d.QT)(),l=(0,n.Z)(),h=(0,u.tv)(),S=(0,i.iH)(!1),b=(0,c.H)(),y=(0,s.Fl)((()=>b.isSignUpAllowed)),_=(0,i.iH)({email:{hasErrors:!1,message:null},password:{hasErrors:!1,message:null}}),U=[e=>!!e||a("Field is required")],k=(0,i.iH)(null),x=(0,i.iH)(null),E=(0,i.iH)(null),z=(0,i.iH)(null);function q(){_.value.email.hasErrors=!1,_.value.email.message=null,_.value.password.hasErrors=!1,_.value.password.message=null,x.value.resetValidation(),z.value.resetValidation()}function W(){q(),x.value.validate(),z.value.validate(),(0,s.Y3)((()=>{x.value.hasError||z.value.hasError||A()}))}function A(){S.value=!0,m.api.user.signUp((0,o.Z)(),k.value,E.value).then((e=>{l.notify({type:"positive",message:a("Your account has been created"),actions:[{label:a("Sign in"),color:"white",handler:()=>{h.push({name:"signIn"})}}]}),S.value=!1})).catch((e=>{switch(S.value=!1,e.response.status){case 400:e.response.data.invalidOrMissingParams.find((function(e){return"email"===e}))?(l.notify({type:"negative",message:a("API Error: missing email param")}),x.value.focus()):e.response.data.invalidOrMissingParams.find((function(e){return"password"===e}))?(l.notify({type:"negative",message:a("API Error: missing password param")}),z.value.focus()):l.notify({type:"negative",message:a("API Error: invalid/missing param")});break;case 409:_.value.email.hasErrors=!0,_.value.email.message=a("Email already used"),(0,s.Y3)((()=>{x.value.focus()}));break;default:l.notify({type:"negative",message:a("API Error: fatal error"),caption:a("API Error: fatal error details",{status:e.response.status,statusText:e.response.statusText})});break}}))}return(e,l)=>{const o=(0,s.up)("q-card-section"),n=(0,s.up)("q-icon"),u=(0,s.up)("q-input"),d=(0,s.up)("q-spinner-hourglass"),m=(0,s.up)("q-btn"),c=(0,s.up)("router-link"),h=(0,s.up)("q-card"),b=(0,s.up)("q-page");return(0,s.wg)(),(0,s.j4)(b,{class:"flex flex-center bg-grey-2"},{default:(0,s.w5)((()=>[(0,s.Wm)(h,{class:"q-pa-md shadow-2 my_card",bordered:""},{default:(0,s.w5)((()=>[(0,s._)("form",{onSubmit:(0,t.iM)(W,["prevent","stop"]),autocorrect:"off",autocapitalize:"off",autocomplete:"off",spellcheck:"false"},[(0,s.Wm)(o,{class:"text-center"},{default:(0,s.w5)((()=>[(0,s._)("h3",null,(0,r.zw)(e.$t("Homedocs")),1),(0,s._)("div",g,(0,r.zw)(e.$t("Sign up below to create your account")),1)])),_:1}),(0,s.Wm)(o,null,{default:(0,s.w5)((()=>[(0,s.Wm)(u,{dense:"",outlined:"",ref_key:"emailRef",ref:x,modelValue:k.value,"onUpdate:modelValue":l[0]||(l[0]=e=>k.value=e),type:"email",name:"email",label:(0,i.SU)(a)("Email"),disable:S.value,autofocus:!0,rules:U,"lazy-rules":"",error:_.value.email.hasErrors,errorMessage:_.value.email.message},{prepend:(0,s.w5)((()=>[(0,s.Wm)(n,{name:"alternate_email"})])),_:1},8,["modelValue","label","disable","error","errorMessage"]),(0,s.Wm)(u,{dense:"",outlined:"",class:"q-mt-md",ref_key:"passwordRef",ref:z,modelValue:E.value,"onUpdate:modelValue":l[1]||(l[1]=e=>E.value=e),name:"password",type:"password",label:(0,i.SU)(a)("Password"),disable:S.value,rules:U,"lazy-rules":"",error:_.value.password.hasErrors,errorMessage:_.value.password.message},{prepend:(0,s.w5)((()=>[(0,s.Wm)(n,{name:"key"})])),_:1},8,["modelValue","label","disable","error","errorMessage"])])),_:1}),(0,s.Wm)(o,null,{default:(0,s.w5)((()=>[(0,s.Wm)(m,{color:"dark",size:"md",label:e.$t("Sign up"),"no-caps":"",class:"full-width",icon:"account_circle",disable:S.value||!(k.value&&E.value)||!y.value,loading:S.value,type:"submit"},{loading:(0,s.w5)((()=>[(0,s.Wm)(d,{class:"on-left"}),(0,s.Uk)(" "+(0,r.zw)((0,i.SU)(a)("Loading...")),1)])),_:1},8,["label","disable","loading"])])),_:1}),(0,s.Wm)(o,{class:"text-center q-pt-none"},{default:(0,s.w5)((()=>[(0,s._)("div",v,[(0,s.Uk)((0,r.zw)((0,i.SU)(a)("Already have an account ?"))+" ",1),(0,s.Wm)(c,{to:{name:"signIn"}},{default:(0,s.w5)((()=>[(0,s._)("span",f,(0,r.zw)((0,i.SU)(a)("Click here to sign in")),1)])),_:1})])])),_:1}),y.value?(0,s.kq)("",!0):((0,s.wg)(),(0,s.j4)(o,{key:0,class:"text-center q-pt-none"},{default:(0,s.w5)((()=>[(0,s._)("div",w,[(0,s.Wm)(n,{name:"info"}),(0,s.Uk)(" "+(0,r.zw)((0,i.SU)(a)("New sign ups are not allowed on this system")),1)])])),_:1}))],40,p)])),_:1})])),_:1})}}};var S=l(9885),b=l(4458),y=l(3190),_=l(6611),U=l(2857),k=l(8879),x=l(3358),E=l(9984),z=l.n(E);const q=h,W=q;z()(h,"components",{QPage:S.Z,QCard:b.Z,QCardSection:y.Z,QInput:_.Z,QIcon:U.Z,QBtn:k.Z,QSpinnerHourglass:x.Z})}}]);