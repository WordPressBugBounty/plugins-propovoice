"use strict";(self.webpackChunkpropovoice=self.webpackChunkpropovoice||[]).push([[1507],{90285:(e,t,n)=>{n.d(t,{BB:()=>i,Sh:()=>l,y0:()=>r});var r="".concat(ndpv.apiUrl,"ndpv/v1/"),i="".concat(ndpv.apiUrl,"ndpvp/v1/"),l={headers:{"content-type":"application/json","X-WP-NONCE":ndpv.nonce,"Cache-Control":"no-cache"}}},21507:(e,t,n)=>{n.r(t),n.d(t,{default:()=>B});var r=n(64467),i=n(3453),l=n(96540),a=n(47767),c=n(36693),o=n(97253),s=n(74848);function u(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function d(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?u(Object(n),!0).forEach((function(t){(0,r.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):u(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var p=(0,l.lazy)((function(){return n.e(4201).then(n.bind(n,24201))})),b=(0,l.lazy)((function(){return n.e(7145).then(n.bind(n,87145))})),f=(0,l.lazy)((function(){return n.e(9069).then(n.bind(n,39069))})),v=(0,l.lazy)((function(){return n.e(2202).then(n.bind(n,92202))})),h=(0,l.lazy)((function(){return n.e(8378).then(n.bind(n,38378))})),y=(0,l.lazy)((function(){return n.e(4161).then(n.bind(n,34161))})),j=(0,l.lazy)((function(){return n.e(3541).then(n.bind(n,63541))})),g=(0,l.lazy)((function(){return n.e(7769).then(n.bind(n,47769))})),m=(0,l.lazy)((function(){return n.e(6734).then(n.bind(n,66734))})),O=(0,l.lazy)((function(){return n.e(2089).then(n.bind(n,92089))})),x=(0,l.lazy)((function(){return n.e(770).then(n.bind(n,70770))})),A=(0,l.lazy)((function(){return n.e(7394).then(n.bind(n,27394))})),w=(0,l.lazy)((function(){return n.e(6040).then(n.bind(n,16040))})),P=(0,l.lazy)((function(){return n.e(4031).then(n.bind(n,44031))})),_=(0,l.lazy)((function(){return n.e(7470).then(n.bind(n,7470))})),z=(0,l.lazy)((function(){return n.e(1134).then(n.bind(n,1134))})),k=(0,l.lazy)((function(){return n.e(3119).then(n.bind(n,53119))})),S=(0,l.lazy)((function(){return n.e(3703).then(n.bind(n,23703))})),N=(0,l.lazy)((function(){return n.e(9980).then(n.bind(n,99980))})),E=(0,l.lazy)((function(){return n.e(5841).then(n.bind(n,5841))})),D=(0,l.lazy)((function(){return n.e(8663).then(n.bind(n,98663))})),C=(0,l.lazy)((function(){return n.e(3944).then(n.bind(n,63944))}));const B=(0,o.A)((function(e){var t=(0,a.g)(),n=t.tab,r=t.subtab,o=(0,a.Zp)(),u=ndpv,B=u.i18n,T=u.caps,L=T.includes("ndpv_client_role")||T.includes("ndpv_staff"),R=!T.includes("ndpv_client_role")||!T.includes("ndpv_staff"),U=!T.includes("ndpv_client_role")&&!T.includes("ndpv_staff")&&!T.includes("ndpv_manager"),F=n,I=r;void 0===n&&(F="general");var M={};M.general={label:B.gen},T.includes("ndpv_lead")&&(M.lead={label:B.lead}),T.includes("ndpv_deal")&&(M.deal={label:B.deal}),(T.includes("ndpv_estimate")||T.includes("ndpv_invoice"))&&(M.estinv={label:B.est+" "+B.nd+" "+B.inv,subtabs:{common:{label:B.cmn},est:{label:B.est},inv:{label:B.inv}}}),T.includes("ndpv_project")&&(M.project={label:B.project}),T.includes("ndpv_project")&&(M.service={label:B.service}),U&&(M.payment={label:B.payment}),U&&(M.email={label:B.email,subtabs:{delivery:{label:"Email Delivery"},"system-template":{label:"System Email Template"},"custom-template":{label:"Custom Email Template"}}}),T.includes("ndpv_task")&&(M.task={label:B.task}),T.includes("ndpv_contact")&&(M.contact={label:B.ct}),R&&(M.tag={label:B.tag}),U&&(M["custom-field"]={label:B.cus+" "+B.field}),U&&(M.integration={label:B.intg}),U&&(M.team={label:B.team}),U&&(M["public-api"]={label:"Public API"}),M.notification={label:"Notification"};var W=(0,l.useState)(F),X=(0,i.A)(W,2),Z=X[0],q=X[1],G=(0,l.useState)(I),H=(0,i.A)(G,2),J=H[0],K=H[1],Q=(0,l.useState)(M),V=(0,i.A)(Q,2),Y=V[0],$=V[1];(0,l.useEffect)((function(){if(L)$({general:{label:B.gen},password:{label:B.password}});else if(has_wage.ins){var e=d({},Y);U&&(e.license={label:B.licman}),$(e)}}),[]);var ee=function(e,t){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;e.preventDefault(),n||(n=Y[t].hasOwnProperty("subtabs")&&Object.keys(Y[t].subtabs)[0]),q(t),K(n),function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;o(t?"/setting/".concat(e,"/").concat(t):"/setting/".concat(e))}(t,n)};return(0,s.jsxs)(s.Fragment,{children:[(0,s.jsx)("nav",{className:"pv-breadcrumb",children:(0,s.jsxs)("ul",{children:[(0,s.jsx)("li",{children:(0,s.jsx)("a",{href:"#",children:B.home})}),(0,s.jsx)("li",{children:(0,s.jsx)("svg",{width:5,height:10,viewBox:"0 0 5 10",fill:"none",children:(0,s.jsx)("path",{d:"M.5 1.25L4.25 5 .5 8.75",stroke:"#718096",strokeLinecap:"round",strokeLinejoin:"round"})})}),(0,s.jsx)("li",{className:"pv-active",children:B.settings})]})}),(0,s.jsx)("h2",{className:"pv-page-title",children:B.settings}),(0,s.jsx)("div",{className:"pv-settings-tab",children:(0,s.jsxs)("div",{className:"row",children:[(0,s.jsx)("div",{className:"col-md-3",children:(0,s.jsx)("ul",{className:"pv-settings-tabs",children:Object.keys(Y).map((function(e){return(0,s.jsxs)("li",{className:"pv-tab "+(e==Z?"pv-active":""),children:[(0,s.jsx)("a",{onClick:function(t){return ee(t,e)},children:Y[e].label}),Y[e].hasOwnProperty("subtabs")&&Y[e].subtabs&&(0,s.jsx)("ul",{className:"pv-settings-subtabs",children:Object.keys(Y[e].subtabs).map((function(t){return(0,s.jsx)("li",{className:"pv-subtab "+(t==J||!J&&Object.keys(Y[e].subtabs)[0]==t?"pv-active":""),children:(0,s.jsx)("a",{onClick:function(n){return ee(n,e,t)},children:Y[e].subtabs[t].label})},t)}))})]},e)}))})}),(0,s.jsx)("div",{className:"col-md-9",children:(0,s.jsxs)("div",{className:"pv-setting-tab-content",children:[(0,s.jsxs)("h4",{className:"pv-title-medium pv-mb-15",style:{textTransform:"capitalize"},children:[Y[Z]&&Y[Z].label,"custom-field"!=Z&&"integration"!=Z&&J&&Y[Z].subtabs[J]&&": "+Y[Z].subtabs[J].label," ",B.settings]}),(0,s.jsxs)(l.Suspense,{fallback:(0,s.jsx)(c.A,{}),children:["general"==Z&&(0,s.jsx)(p,{}),"password"==Z&&(0,s.jsx)(b,{}),"task"==Z&&(0,s.jsx)(f,{}),"lead"==Z&&(0,s.jsx)(v,{}),"deal"==Z&&(0,s.jsx)(h,{}),"estinv"==Z&&("common"==J||!J)&&(0,s.jsx)(y,d({},e)),"estinv"==Z&&"est"==J&&(0,s.jsx)(j,d({},e)),"estinv"==Z&&"inv"==J&&(0,s.jsx)(g,d({},e)),"service"==Z&&(0,s.jsx)(m,{}),"project"==Z&&(0,s.jsx)(O,d({},e)),"payment"==Z&&(0,s.jsx)(C,d({},e)),"email"==Z&&("delivery"==J||!J)&&(0,s.jsx)(N,d({},e)),"email"==Z&&("system-template"==J||!J)&&(0,s.jsx)(E,d({},e)),"email"==Z&&("custom-template"==J||!J)&&(0,s.jsx)(D,d({},e)),"contact"==Z&&(0,s.jsx)(x,{}),"tag"==Z&&(0,s.jsx)(A,{}),"custom-field"==Z&&(0,s.jsx)(w,d({},e)),"integration"==Z&&(0,s.jsx)(P,d({},e)),"team"==Z&&(0,s.jsx)(_,d({},e)),"public-api"==Z&&(0,s.jsx)(z,d({},e)),"notification"==Z&&(0,s.jsx)(k,d({},e)),"license"==Z&&(0,s.jsx)(S,d({},e))]})]})})]})})]})}))},97253:(e,t,n)=>{n.d(t,{A:()=>j});var r=n(23029),i=n(92901),l=n(56822),a=n(53954),c=n(9417),o=n(85501),s=n(64467),u=n(96540),d=n(57536),p=n(90285),b=n(74848);function f(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function v(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?f(Object(n),!0).forEach((function(t){(0,s.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):f(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function h(e,t,n){return t=(0,a.A)(t),(0,l.A)(e,y()?Reflect.construct(t,n||[],(0,a.A)(e).constructor):t.apply(e,n))}function y(){try{var e=!Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){})))}catch(e){}return(y=function(){return!!e})()}const j=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=p.y0+t,l=function(t){function l(e){var t;return(0,r.A)(this,l),t=h(this,l,[e]),(0,s.A)((0,c.A)(t),"getAll",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";return e&&(n=p.y0+e),d.A.get("".concat(n,"/?").concat(t),p.Sh)})),(0,s.A)((0,c.A)(t),"get",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),d.A.get("".concat(n,"/").concat(t),p.Sh)})),(0,s.A)((0,c.A)(t),"create",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),d.A.post(n,t,p.Sh)})),(0,s.A)((0,c.A)(t),"update",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0,r=arguments.length>2?arguments[2]:void 0;return e&&(n=p.y0+e),d.A.put("".concat(n,"/").concat(t),r,p.Sh)})),(0,s.A)((0,c.A)(t),"remove",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),d.A.delete("".concat(n,"/").concat(t),p.Sh)})),(0,s.A)((0,c.A)(t),"findByArg",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";return e&&(n=p.y0+e),d.A.get("".concat(n,"?title=").concat(t),p.Sh)})),t}return(0,o.A)(l,t),(0,i.A)(l,[{key:"render",value:function(){return(0,b.jsx)(e,v(v({},this.props),{},{getAll:this.getAll,get:this.get,create:this.create,update:this.update,remove:this.remove,findByArg:this.findByArg}))}}]),l}(u.Component);return l}},9417:(e,t,n)=>{function r(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}n.d(t,{A:()=>r})},23029:(e,t,n)=>{function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}n.d(t,{A:()=>r})},92901:(e,t,n)=>{n.d(t,{A:()=>l});var r=n(49922);function i(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,(0,r.A)(i.key),i)}}function l(e,t,n){return t&&i(e.prototype,t),n&&i(e,n),Object.defineProperty(e,"prototype",{writable:!1}),e}},53954:(e,t,n)=>{function r(e){return r=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)},r(e)}n.d(t,{A:()=>r})},85501:(e,t,n)=>{n.d(t,{A:()=>i});var r=n(63662);function i(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&(0,r.A)(e,t)}},56822:(e,t,n)=>{n.d(t,{A:()=>l});var r=n(82284),i=n(9417);function l(e,t){if(t&&("object"===(0,r.A)(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return(0,i.A)(e)}},63662:(e,t,n)=>{function r(e,t){return r=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e},r(e,t)}n.d(t,{A:()=>r})}}]);