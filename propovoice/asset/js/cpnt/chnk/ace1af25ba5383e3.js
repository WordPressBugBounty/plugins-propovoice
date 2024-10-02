"use strict";(self.webpackChunkpropovoice=self.webpackChunkpropovoice||[]).push([[7031],{90285:(e,t,n)=>{n.d(t,{BB:()=>o,Sh:()=>i,y0:()=>r});var r="".concat(ndpv.apiUrl,"ndpv/v1/"),o="".concat(ndpv.apiUrl,"ndpvp/v1/"),i={headers:{"content-type":"application/json","X-WP-NONCE":ndpv.nonce,"Cache-Control":"no-cache"}}},27031:(e,t,n)=>{n.r(t),n.d(t,{default:()=>C});var r=n(64467),o=n(3453),i=n(96540),c=n(36693),s=n(74717),l=n(97253),a=n(74848);function d(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function u(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?d(Object(n),!0).forEach((function(t){(0,r.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):d(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var p=(0,i.lazy)((function(){return n.e(3637).then(n.bind(n,16018))})),h=(0,i.lazy)((function(){return n.e(4979).then(n.bind(n,24979))})),v=(0,i.lazy)((function(){return n.e(2041).then(n.bind(n,2041))})),f=(0,i.lazy)((function(){return n.e(7517).then(n.bind(n,97517))})),b=(0,i.lazy)((function(){return n.e(1747).then(n.bind(n,41747))})),y=(0,i.lazy)((function(){return n.e(1160).then(n.bind(n,21160))})),g=(0,i.lazy)((function(){return n.e(1979).then(n.bind(n,1979))})),j=(0,i.lazy)((function(){return n.e(6655).then(n.bind(n,19036))})),x=(0,i.lazy)((function(){return n.e(8518).then(n.bind(n,96137))})),m=function(){var e=ndpv.i18n;return(0,a.jsxs)("div",{className:"pv-bg-white pv-border-gray",style:{padding:"10px 20px 5px 30px",borderRadius:"8px"},children:[(0,a.jsx)("h3",{className:"pv-title-medium pv-mb-20",style:{fontWeight:"bold",color:"#718096",marginLeft:"-10px"},children:e.latest_task}),(0,a.jsx)(i.Suspense,{fallback:(0,a.jsx)(c.A,{}),children:(0,a.jsx)(p,{tab_id:null,dashboard:!0,source:!0})})]})};const C=(0,l.A)((function(e){var t=(0,i.useState)(!1),n=(0,o.A)(t,2),r=(n[0],n[1]),l=(0,i.useState)(2023),d=(0,o.A)(l,2),p=d[0],C=(d[1],(0,i.useRef)()),A=(0,i.useCallback)((function(){return r(!1)}),[]);(0,s.A)(C,A);ndpv.i18n.create;var w=ndpv,O=w.i18n,k=w.caps,P=k.includes("ndpv_client_role"),N=k.includes("ndpv_staff");return(0,a.jsxs)("div",{className:"ndpv-dashboard",children:[(0,a.jsxs)("div",{className:"row",children:[(0,a.jsxs)("div",{className:"col",children:[(0,a.jsx)("h2",{className:"pv-page-title",style:{color:"#2d3748",display:"inline-block",marginBottom:0},children:O.ov}),!1]}),(0,a.jsx)("div",{className:"col",children:!1})]}),(0,a.jsx)(i.Suspense,{fallback:(0,a.jsx)(c.A,{}),children:(0,a.jsx)(h,u({},e))}),!wage.length&&!P&&(0,a.jsxs)("div",{className:"pv-db-task-deal",children:[k.includes("ndpv_task")&&(0,a.jsx)(m,{}),k.includes("ndpv_deal")&&(0,a.jsx)(i.Suspense,{fallback:(0,a.jsx)(c.A,{}),children:(0,a.jsx)(v,u({},e))})]}),(0,a.jsxs)("div",{className:"row",children:[P&&(0,a.jsxs)("div",{className:"col-lg-12",children:[(0,a.jsxs)("div",{className:"pv-widget pv-summery pv-bg-white pv-border-gray",children:[(0,a.jsx)("h3",{className:"pv-title-medium pv-mb-20",style:{fontWeight:"bold",color:"#718096"},children:O.latest+" "+O.est}),(0,a.jsx)("div",{style:{width:"100%"},children:(0,a.jsx)(j,{dashboard:!0,path:"estimate"})})]}),(0,a.jsxs)("div",{className:"pv-widget pv-summery pv-bg-white pv-border-gray",children:[(0,a.jsx)("h3",{className:"pv-title-medium pv-mb-20",style:{fontWeight:"bold",color:"#718096"},children:O.latest+" "+O.inv}),(0,a.jsx)("div",{style:{width:"100%"},children:(0,a.jsx)(j,{dashboard:!0,path:"invoice"})})]}),(0,a.jsxs)("div",{className:"pv-widget pv-summery pv-bg-white pv-border-gray",children:[(0,a.jsx)("h3",{className:"pv-title-medium pv-mb-20",style:{fontWeight:"bold",color:"#718096"},children:O.latest+" "+O.project}),(0,a.jsx)("div",{style:{width:"100%"},children:(0,a.jsx)(x,{dashboard:!0})})]})]}),!P&&!N&&(0,a.jsxs)(a.Fragment,{children:[(0,a.jsx)("div",{className:"col-lg-8",children:(0,a.jsxs)(i.Suspense,{fallback:(0,a.jsx)(c.A,{}),children:[wage.length>0&&(0,a.jsx)("div",{style:{marginBottom:25},children:(0,a.jsx)(m,{})}),!wage.length&&k.includes("ndpv_deal")&&(0,a.jsx)(b,u(u({},e),{},{type:"deal_tracking",year:p}),"1"+p),(0,a.jsx)(y,u(u({},e),{},{type:"estimate",year:p}),"2"+p),(0,a.jsx)(y,u(u({},e),{},{type:"invoice",year:p}),"3"+p),!1]})}),(0,a.jsx)("div",{className:"col-lg-4",children:(0,a.jsxs)(i.Suspense,{fallback:(0,a.jsx)(c.A,{}),children:[k.includes("ndpv_lead")&&(0,a.jsx)(f,u(u({},e),{},{type:"lead_level"})),k.includes("ndpv_deal")&&(0,a.jsx)(f,u(u({},e),{},{type:"lead_source"})),wage.length>0&&(0,a.jsx)(g,{title:"Upgrade Pro to Get Access All Insight",desc:"Please upgrade to pro and get access to enjoy all the amazing features that accelerate your business growth and take your business experience to the next level.",btnTxt:"Upgrade Now",btnUrl:"https://propovoice.com/pricing",bgColor:"FFEED9",children:(0,a.jsxs)("svg",{width:37,height:37,viewBox:"0 0 37 37",fill:"none",children:[(0,a.jsx)("path",{d:"M7.0147 29.1727C7.05068 29.3206 7.11594 29.4598 7.20662 29.5821C7.2973 29.7043 7.41156 29.8071 7.54265 29.8845C7.67375 29.9618 7.81901 30.0121 7.96986 30.0324C8.12071 30.0526 8.27409 30.0424 8.42095 30.0024C15.1245 28.1463 22.2064 28.1463 28.91 30.0024C29.0569 30.0424 29.2102 30.0526 29.3611 30.0324C29.5119 30.0121 29.6572 29.9618 29.7883 29.8845C29.9194 29.8071 30.0337 29.7043 30.1243 29.5821C30.215 29.4598 30.2803 29.3206 30.3163 29.1727L33.9022 13.9431C33.9514 13.7392 33.9427 13.5257 33.877 13.3265C33.8113 13.1273 33.6913 12.9505 33.5304 12.816C33.3695 12.6814 33.1743 12.5945 32.9666 12.5651C32.759 12.5356 32.5473 12.5648 32.3553 12.6493L25.2397 15.8134C24.9819 15.9269 24.6911 15.9408 24.4236 15.8525C24.1561 15.7642 23.9308 15.5799 23.7913 15.3352L19.6569 7.88212C19.5596 7.70628 19.417 7.55971 19.2439 7.45765C19.0708 7.35559 18.8735 7.30176 18.6725 7.30176C18.4715 7.30176 18.2743 7.35559 18.1011 7.45765C17.928 7.55971 17.7854 7.70628 17.6881 7.88212L13.5538 15.3352C13.4142 15.5799 13.1889 15.7642 12.9214 15.8525C12.6539 15.9408 12.3631 15.9269 12.1053 15.8134L4.9897 12.6493C4.79776 12.5648 4.58603 12.5356 4.3784 12.5651C4.17076 12.5945 3.97549 12.6814 3.81463 12.816C3.65376 12.9505 3.53371 13.1273 3.46801 13.3265C3.40231 13.5257 3.39359 13.7392 3.44282 13.9431L7.0147 29.1727Z",stroke:"#FF6B00",strokeWidth:"2.5",strokeLinecap:"round",strokeLinejoin:"round"}),(0,a.jsx)("path",{d:"M14.1725 23.4148C17.1638 23.0914 20.1812 23.0914 23.1725 23.4148",stroke:"#FF6B00",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"})]})}),!1,wage.length>0&&(0,a.jsx)(g,{title:"Help and Support",desc:"Feel free to ping us whenever you need any support. We will be happy to assist you and make your journey smooth. We keep our users satisfaction top of our minds.",btnTxt:"See Documentation",btnUrl:"https://propovoice.com/docs",bgColor:"E0F0EC",contact:!0,children:(0,a.jsx)("svg",{width:34,height:34,viewBox:"0 0 34 34",fill:"none",children:(0,a.jsx)("path",{d:"M28.907 11H30.5C31.2956 11 32.0587 11.316 32.6213 11.8787C33.1839 12.4413 33.5 13.2043 33.5 14V20C33.5 20.7956 33.1839 21.5587 32.6213 22.1213C32.0587 22.6839 31.2956 23 30.5 23H28.907C28.5413 25.8999 27.1299 28.5667 24.9376 30.5C22.7453 32.4332 19.9229 33.4999 17 33.5V30.5C19.3869 30.5 21.6761 29.5518 23.364 27.8639C25.0518 26.1761 26 23.8869 26 21.5V12.5C26 10.113 25.0518 7.82384 23.364 6.13601C21.6761 4.44818 19.3869 3.49997 17 3.49997C14.6131 3.49997 12.3239 4.44818 10.636 6.13601C8.94821 7.82384 8 10.113 8 12.5V23H3.5C2.70435 23 1.94129 22.6839 1.37868 22.1213C0.816071 21.5587 0.5 20.7956 0.5 20V14C0.5 13.2043 0.816071 12.4413 1.37868 11.8787C1.94129 11.316 2.70435 11 3.5 11H5.093C5.45905 8.10032 6.87062 5.43388 9.06286 3.50099C11.2551 1.56809 14.0773 0.501587 17 0.501587C19.9227 0.501587 22.7449 1.56809 24.9371 3.50099C27.1294 5.43388 28.541 8.10032 28.907 11ZM3.5 14V20H5V14H3.5ZM29 14V20H30.5V14H29ZM10.64 22.6775L12.23 20.1335C13.6596 21.029 15.313 21.5027 17 21.5C18.687 21.5027 20.3404 21.029 21.77 20.1335L23.36 22.6775C21.4539 23.8717 19.2493 24.5034 17 24.5C14.7507 24.5034 12.5461 23.8717 10.64 22.6775Z",fill:"#4BB99E"})})})]})})]})]})]})}))},97253:(e,t,n)=>{n.d(t,{A:()=>g});var r=n(23029),o=n(92901),i=n(56822),c=n(53954),s=n(9417),l=n(85501),a=n(64467),d=n(96540),u=n(57536),p=n(90285),h=n(74848);function v(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function f(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?v(Object(n),!0).forEach((function(t){(0,a.A)(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):v(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function b(e,t,n){return t=(0,c.A)(t),(0,i.A)(e,y()?Reflect.construct(t,n||[],(0,c.A)(e).constructor):t.apply(e,n))}function y(){try{var e=!Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){})))}catch(e){}return(y=function(){return!!e})()}const g=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=p.y0+t,i=function(t){function i(e){var t;return(0,r.A)(this,i),t=b(this,i,[e]),(0,a.A)((0,s.A)(t),"getAll",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";return e&&(n=p.y0+e),u.A.get("".concat(n,"/?").concat(t),p.Sh)})),(0,a.A)((0,s.A)(t),"get",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),u.A.get("".concat(n,"/").concat(t),p.Sh)})),(0,a.A)((0,s.A)(t),"create",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),u.A.post(n,t,p.Sh)})),(0,a.A)((0,s.A)(t),"update",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0,r=arguments.length>2?arguments[2]:void 0;return e&&(n=p.y0+e),u.A.put("".concat(n,"/").concat(t),r,p.Sh)})),(0,a.A)((0,s.A)(t),"remove",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1?arguments[1]:void 0;return e&&(n=p.y0+e),u.A.delete("".concat(n,"/").concat(t),p.Sh)})),(0,a.A)((0,s.A)(t),"findByArg",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";return e&&(n=p.y0+e),u.A.get("".concat(n,"?title=").concat(t),p.Sh)})),t}return(0,l.A)(i,t),(0,o.A)(i,[{key:"render",value:function(){return(0,h.jsx)(e,f(f({},this.props),{},{getAll:this.getAll,get:this.get,create:this.create,update:this.update,remove:this.remove,findByArg:this.findByArg}))}}]),i}(d.Component);return i}},9417:(e,t,n)=>{function r(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}n.d(t,{A:()=>r})},23029:(e,t,n)=>{function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}n.d(t,{A:()=>r})},92901:(e,t,n)=>{n.d(t,{A:()=>i});var r=n(49922);function o(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,(0,r.A)(o.key),o)}}function i(e,t,n){return t&&o(e.prototype,t),n&&o(e,n),Object.defineProperty(e,"prototype",{writable:!1}),e}},53954:(e,t,n)=>{function r(e){return r=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)},r(e)}n.d(t,{A:()=>r})},85501:(e,t,n)=>{n.d(t,{A:()=>o});var r=n(63662);function o(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&(0,r.A)(e,t)}},56822:(e,t,n)=>{n.d(t,{A:()=>i});var r=n(82284),o=n(9417);function i(e,t){if(t&&("object"===(0,r.A)(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return(0,o.A)(e)}},63662:(e,t,n)=>{function r(e,t){return r=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e},r(e,t)}n.d(t,{A:()=>r})}}]);