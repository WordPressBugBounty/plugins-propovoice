"use strict";(globalThis.webpackChunkpropovoice=globalThis.webpackChunkpropovoice||[]).push([[6840],{31293:(e,t,a)=>{a.d(t,{A:()=>i});var n=a(51609),r=a(21241),s=a(56303),c=a(394),l=a(25928);class i extends n.Component{constructor(e){super(e),this.state={form:{status:!1,due_date:!1,before:[],after:[]}}}componentDidMount(){s.A.get("settings","tab="+this.props.path+"_reminder").then((e=>{e.data.success&&this.setState({form:e.data.data})}))}handleChange=(e,t)=>{let a={...this.state.form};const n=e.target,r=n.name,s="status"===r||"due_date"===r?n.checked:n.value;if(t){let e=a[t];n.checked?e.push(parseInt(s)):e.splice(e.indexOf(parseInt(s)),1)}else a[r]=s;wage.length>0&&"status"==r?(0,l.A)():this.setState({form:a})};handleSubmit=e=>{if(e.preventDefault(),ndpv.isDemo)return void r.oR.error(ndpv.demoMsg);let t=this.state.form;t.tab=this.props.path+"_reminder",s.A.add("settings",t).then((e=>{e.data.success?r.oR.success(ndpv.i18n.aUpd):e.data.data.forEach((function(e){r.oR.error(e)}))}))};render(){const e=this.state.form,t=ndpv.i18n;return(0,n.createElement)("form",{onSubmit:this.handleSubmit,className:"pv-form-style-one"},(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",null,t.status,(0,n.createElement)(c.A,null)),(0,n.createElement)("div",{className:"pv-field-switch pv-ml-10"},(0,n.createElement)("label",{className:"pv-switch"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-status",name:"status",checked:e.status?"checked":"",onChange:this.handleChange}),(0,n.createElement)("span",{className:"pv-switch-slider pv-round"}))))),(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",null,t.rem),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-due_date",name:"due_date",checked:e.due_date?"checked":"",onChange:this.handleChange}),(0,n.createElement)("label",{htmlFor:"reminder-due_date"},t.on," ",t.due_date)))),(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",null,t.rem," ",t.before),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-before-1",name:"before",value:1,checked:e.before.includes(1)?"checked":"",onChange:e=>this.handleChange(e,"before")}),(0,n.createElement)("label",{htmlFor:"reminder-before-1"},"1 ",t.day)),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-before-7",name:"before",value:7,checked:e.before.includes(7)?"checked":"",onChange:e=>this.handleChange(e,"before")}),(0,n.createElement)("label",{htmlFor:"reminder-before-7"},"7 ",t.days)),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-before-15",name:"before",value:15,checked:e.before.includes(15)?"checked":"",onChange:e=>this.handleChange(e,"before")}),(0,n.createElement)("label",{htmlFor:"reminder-before-15"},"15 ",t.days)),!1),(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",null,t.rem," ",t.after),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-after-1",name:"after",value:1,checked:e.after.includes(1)?"checked":"",onChange:e=>this.handleChange(e,"after")}),(0,n.createElement)("label",{htmlFor:"reminder-after-1"},"1 ",t.day)),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-after-7",name:"after",value:7,checked:e.after.includes(7)?"checked":"",onChange:e=>this.handleChange(e,"after")}),(0,n.createElement)("label",{htmlFor:"reminder-after-7"},"7 ",t.days)),(0,n.createElement)("div",{className:"pv-field-checkbox"},(0,n.createElement)("input",{type:"checkbox",id:"reminder-after-15",name:"after",value:15,checked:e.after.includes(15)?"checked":"",onChange:e=>this.handleChange(e,"after")}),(0,n.createElement)("label",{htmlFor:"reminder-after-15"},"15 ",t.days)),!1)),(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("button",{className:"pv-btn pv-bg-blue pv-bg-hover-blue"},t.save))))}}},25928:(e,t,a)=>{function n(){document.getElementById("pv-pro-alert").style.display="block"}a.d(t,{A:()=>n})},394:(e,t,a)=>{a.d(t,{A:()=>r});var n=a(51609);const r=e=>(0,n.createElement)(n.Fragment,null,wage.length>0&&(0,n.createElement)("span",{className:"pv-pro-label",onClick:()=>{document.getElementById("pv-pro-alert").style.display="block"},style:{background:e.blueBtn?"#FFEED9":"auto"}},(0,n.createElement)("svg",{width:13,height:10,viewBox:"0 0 13 10",fill:"none"},(0,n.createElement)("path",{d:"M1.71013 8.87452C1.72412 8.93204 1.7495 8.98616 1.78477 9.0337C1.82003 9.08124 1.86447 9.12123 1.91545 9.15131C1.96643 9.18139 2.02292 9.20094 2.08158 9.20882C2.14025 9.2167 2.19989 9.21274 2.257 9.19718C4.86395 8.47534 7.61803 8.47534 10.225 9.19718C10.2821 9.21274 10.3417 9.2167 10.4004 9.20882C10.4591 9.20094 10.5156 9.18139 10.5665 9.15131C10.6175 9.12123 10.6619 9.08124 10.6972 9.0337C10.7325 8.98616 10.7579 8.93204 10.7718 8.87452L12.1664 2.95187C12.1855 2.87259 12.1821 2.78954 12.1566 2.71209C12.131 2.63464 12.0843 2.56588 12.0218 2.51356C11.9592 2.46123 11.8833 2.42744 11.8025 2.41599C11.7218 2.40454 11.6395 2.41588 11.5648 2.44874L8.79763 3.67921C8.69737 3.72336 8.5843 3.72879 8.48027 3.69445C8.37624 3.6601 8.28863 3.58843 8.23435 3.49327L6.62653 0.594837C6.5887 0.526455 6.53324 0.469456 6.46592 0.429765C6.3986 0.390074 6.32187 0.369141 6.24372 0.369141C6.16557 0.369141 6.08885 0.390074 6.02153 0.429765C5.95421 0.469456 5.89874 0.526455 5.86091 0.594837L4.2531 3.49327C4.19882 3.58843 4.1112 3.6601 4.00717 3.69445C3.90314 3.72879 3.79008 3.72336 3.68982 3.67921L0.922629 2.44874C0.847988 2.41588 0.765648 2.40454 0.684901 2.41599C0.604154 2.42744 0.528216 2.46123 0.465658 2.51356C0.403099 2.56588 0.35641 2.63464 0.330861 2.71209C0.305312 2.78954 0.301919 2.87259 0.321067 2.95187L1.71013 8.87452Z",fill:"#FF6B00"})),"Pro"))},43105:(e,t,a)=>{a.d(t,{A:()=>i});var n=a(51609),r=a(21241),s=a(56303),c=a(25928);const l=ndpv.i18n,i=e=>{const[t,a]=(0,n.useState)(null);(0,n.useEffect)((()=>{i()}),[]);const i=()=>{s.A.get("settings",`tab=${e.path}_template`).then((e=>{if(e.data.success){const t=e.data.data.default_template;t&&a(t)}}))},d=ndpv.assetImgUri+"template/";return(0,n.createElement)("div",{className:"pv-form-style-one"},(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",null,l.def+" "+l.template),(0,n.createElement)("div",{className:"pv-template-list"},[{id:5,est_src:"estimate-5.png",inv_src:"invoice-5.png"},{id:6,est_src:"estimate-6.png",inv_src:"invoice-6.png"},{id:7,est_src:"estimate-7.png",inv_src:"invoice-7.png"},{id:8,est_src:"estimate-8.png",inv_src:"invoice-8.png"},{id:1,est_src:"estimate-1.png",inv_src:"invoice-1.png"},{id:2,est_src:"estimate-2.png",inv_src:"invoice-2.png"},{id:3,est_src:"estimate-3.png",inv_src:"invoice-3.png"},{id:4,est_src:"estimate-4.png",inv_src:"invoice-4.png"}].map(((l,i)=>(0,n.createElement)("div",{key:i,className:"pv-template-item",onClick:()=>(t=>{if(wage.length>0)return void(0,c.A)();const n=t.id;if(n){a(n);let t={};t.tab=`${e.path}_template`,t.default_template=n,s.A.add("settings",t).then((e=>{e.data.success?r.oR.success(ndpv.i18n.aUpd):e.data.data.forEach((function(e){r.oR.error(e)}))}))}})(l)},t==l.id&&(0,n.createElement)("div",{className:"pv-checked"},(0,n.createElement)("svg",{width:12,height:11,viewBox:"3.4 5.6 17.6 13.4",xmlSpace:"preserve"},(0,n.createElement)("path",{d:"M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"}))),(0,n.createElement)("img",{src:"invoice"==e.path?d+l.inv_src:d+l.est_src}))))))))}},14459:(e,t,a)=>{a.r(t),a.d(t,{default:()=>h});var n=a(51609),r=a(31293),s=a(21241),c=a(56303);class l extends n.Component{constructor(e){super(e),this.state={form:{prefix:""}}}componentDidMount(){c.A.get("settings","tab=invoice_general").then((e=>{e.data.success&&this.setState({form:e.data.data})}))}handleChange=e=>{let t={...this.state.form};const a=e.target,n=a.name,r=a.value;t[n]=r,this.setState({form:t})};handleSubmit=e=>{if(e.preventDefault(),ndpv.isDemo)return void s.oR.error(ndpv.demoMsg);let t=this.state.form;t.tab="invoice_general",c.A.add("settings",t).then((e=>{e.data.success?s.oR.success(ndpv.i18n.aUpd):e.data.data.forEach((function(e){s.oR.error(e)}))}))};render(){const e=this.state.form,t=ndpv.i18n;return(0,n.createElement)("form",{onSubmit:this.handleSubmit,className:"pv-form-style-one"},(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("label",{htmlFor:"field-prefix"},t.inv," ",t.num," ",t.pre),(0,n.createElement)("input",{id:"field-prefix",type:"text",name:"prefix",value:e.prefix,onChange:this.handleChange})),(0,n.createElement)("div",{className:"col"})),(0,n.createElement)("div",{className:"row"},(0,n.createElement)("div",{className:"col"},(0,n.createElement)("button",{className:"pv-btn pv-bg-blue pv-bg-hover-blue"},t.save))))}}class i extends n.Component{constructor(e){super(e),this.state={form:{status:!1,due_date:!1,before:[],after:[]}}}componentDidMount(){c.A.get("settings","tab=invoice_recurring").then((e=>{e.data.success&&this.setState({form:e.data.data})}))}handleChange=(e,t)=>{let a={...this.state.form};const n=e.target,r=n.name,s="status"===r||"due_date"===r?n.checked:n.value;if(t){let e=a[t];n.checked?e.push(parseInt(s)):e.splice(e.indexOf(parseInt(s)),1)}else a[r]=s;this.setState({form:a})};handleSubmit=e=>{if(e.preventDefault(),ndpv.isDemo)return void s.oR.error(ndpv.demoMsg);let t=this.state.form;t.tab="invoice_recurring",c.A.add("settings",t).then((e=>{e.data.success?s.oR.success(ndpv.i18n.aUpd):e.data.data.forEach((function(e){s.oR.error(e)}))}))};render(){return(0,n.createElement)("form",{onSubmit:this.handleSubmit,className:"pv-form-style-one"})}}var d=a(43105);const m=ndpv.i18n;class o extends n.Component{constructor(e){super(e),this.state={tabs:[{id:"general",text:m.gen},{id:"reminder",text:m.rem},{id:"template",text:m.template}],currentTab:""}}componentDidMount(){this.setState({currentTab:"general"})}setActiveTab(e){this.setState({currentTab:e})}render(){const{tabs:e=[],currentTab:t}=this.state;return(0,n.createElement)(n.Fragment,null,(0,n.createElement)("ul",{className:"pv-horizontal-tab"},e.map(((e,a)=>(0,n.createElement)("li",{key:a,className:"pv-tab "+(e.id==t?"pv-active":""),onClick:t=>this.setActiveTab(e.id)},e.text)))),"general"==t&&(0,n.createElement)(l,null),"reminder"==t&&(0,n.createElement)(r.A,{...this.props,path:"invoice"}),"recurring"==t&&(0,n.createElement)(i,{...this.props}),"template"==t&&(0,n.createElement)(d.A,{path:"invoice"}))}}const h=o}}]);