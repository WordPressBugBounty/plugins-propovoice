"use strict";(globalThis.webpackChunkpropovoice=globalThis.webpackChunkpropovoice||[]).push([[5897],{25897:(e,t,n)=>{n.r(t),n.d(t,{default:()=>s});var o=n(51609),i=n(56303);const l=(0,o.lazy)((()=>n.e(4064).then(n.bind(n,24064)))),a={border:"none",cursor:"pointer",marginLeft:"10px",background:"none"},r={display:"flex",width:"100%",justifyContent:"space-between",paddingTop:"17px",paddingBottom:"19px",borderBottom:"1px solid #E2E8F0"},d={width:"30%",textAlign:"right"},m={color:"#000",fontFamily:"Inter",fontSize:"12px",fontStyle:"normal",fontWeight:700,lineHeight:"14px",paddingBottom:"12px"},c={color:"#000",fontFamily:"Inter",fontSize:"14px",fontStyle:"normal",fontWeight:"400",lineHeight:"14px",paddingBottom:"10px"},p={color:"#718096",fontFamily:"Inter",fontSize:"12px",fontStyle:"normal",fontWeight:400,lineHeight:"16px",width:"85%",maxHeight:"2.6em",overflow:"hidden",position:"relative"};function s(){const[e,t]=(0,o.useState)([]);(0,o.useEffect)((()=>{n()}),[]),(0,o.useEffect)((()=>{const e=e=>{n()};return window.addEventListener("emailSaved",e),()=>{window.removeEventListener("emailSaved",e)}}),[]);const n=()=>{i.A.add("custom-email-templates").then((e=>{t(e.data)}))};return(0,o.createElement)("div",null,e.map(((e,t)=>{return(0,o.createElement)("div",{key:t,style:r},(0,o.createElement)("div",null,(0,o.createElement)("div",{style:c},e.name),(0,o.createElement)("div",{style:m},"Subject: ",e.subject),(0,o.createElement)("div",{style:p},null!==(s=e.message)&&""!==s&&(s=s.toString()).replace(/(<([^>]+)>)/gi,""))),(0,o.createElement)("div",{style:d},(0,o.createElement)("button",{onClick:()=>{return t=e.id,void i.A.add("delete-custom-email-template",{id:t}).then((e=>{n()}));var t},style:a},(0,o.createElement)(l,null))));var s})))}}}]);