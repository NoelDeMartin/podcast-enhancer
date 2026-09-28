import{n as e}from"./chunk-DnJy8xQt.js";import{A as t,I as n,S as r,Y as i,g as a,pt as o,t as s,z as c}from"./vue.esm-bundler-BO0T5OF3.js";import{Q as l,t as u}from"./dist-LEu-QKMZ.js";import{i as d,t as f}from"./core-Be5yQh-H.js";import{r as p,t as m}from"./utils-DZj8lXFV.js";import{n as h,t as g}from"./dist-B1QQJCeP.js";var _,v=e((()=>{s(),f(),u(),p(),S(),_=r({__name:`Badge`,props:{asChild:{type:Boolean},as:{},variant:{},class:{type:[Boolean,null,String,Object,Array]}},setup(e){let r=e,s=d(r,`class`);return(u,d)=>(n(),a(o(l),t({"data-slot":`badge`,class:o(m)(o(x)({variant:e.variant}),r.class)},o(s)),{default:i(()=>[c(u.$slots,`default`)]),_:3},16,[`class`]))}})})),y,b=e((()=>{v(),v(),y=_,_.__docgenInfo=Object.assign({displayName:_.name??_.__name},{exportName:`default`,displayName:`Badge`,description:``,tags:{},slots:[{name:`default`}],sourceFiles:[`/home/runner/work/podcast-enhancer/podcast-enhancer/resources/js/components/ui/badge/Badge.vue`]})})),x,S=e((()=>{h(),b(),x=g(`inline-flex items-center justify-center rounded-none border-3 px-2.5 py-0.5 text-xs font-bold w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:ring-neo-blue focus-visible:ring-2 aria-invalid:ring-destructive aria-invalid:border-destructive transition-[color,box-shadow-neo-hard] overflow-hidden border-neo-dark`,{variants:{variant:{default:`bg-primary text-primary-foreground`,secondary:`bg-secondary text-secondary-foreground`,destructive:`bg-destructive text-white`,outline:`text-foreground bg-background`}},defaultVariants:{variant:`default`}})})),C,w,T,E,D,O;e((()=>{S(),C={title:`UI/Badge`,component:y,tags:[`autodocs`],argTypes:{variant:{control:`select`,options:[`default`,`secondary`,`destructive`,`outline`]}},args:{variant:`default`}},w={render:e=>({components:{Badge:y},setup:()=>({args:e}),template:`<Badge v-bind="args">Badge</Badge>`})},T={args:{variant:`secondary`},render:e=>({components:{Badge:y},setup:()=>({args:e}),template:`<Badge v-bind="args">Secondary</Badge>`})},E={args:{variant:`destructive`},render:e=>({components:{Badge:y},setup:()=>({args:e}),template:`<Badge v-bind="args">Destructive</Badge>`})},D={args:{variant:`outline`},render:e=>({components:{Badge:y},setup:()=>({args:e}),template:`<Badge v-bind="args">Outline</Badge>`})},w.parameters={...w.parameters,docs:{...w.parameters?.docs,source:{originalSource:`{
  render: args => ({
    components: {
      Badge
    },
    setup: () => ({
      args
    }),
    template: '<Badge v-bind="args">Badge</Badge>'
  })
}`,...w.parameters?.docs?.source}}},T.parameters={...T.parameters,docs:{...T.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'secondary'
  },
  render: args => ({
    components: {
      Badge
    },
    setup: () => ({
      args
    }),
    template: '<Badge v-bind="args">Secondary</Badge>'
  })
}`,...T.parameters?.docs?.source}}},E.parameters={...E.parameters,docs:{...E.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'destructive'
  },
  render: args => ({
    components: {
      Badge
    },
    setup: () => ({
      args
    }),
    template: '<Badge v-bind="args">Destructive</Badge>'
  })
}`,...E.parameters?.docs?.source}}},D.parameters={...D.parameters,docs:{...D.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'outline'
  },
  render: args => ({
    components: {
      Badge
    },
    setup: () => ({
      args
    }),
    template: '<Badge v-bind="args">Outline</Badge>'
  })
}`,...D.parameters?.docs?.source}}},O=[`Default`,`Secondary`,`Destructive`,`Outline`]}))();export{w as Default,E as Destructive,D as Outline,T as Secondary,O as __namedExportsOrder,C as default};