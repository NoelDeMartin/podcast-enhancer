import{n as e}from"./chunk-DnJy8xQt.js";import{n as t,t as n}from"./input-A07Z3YZK.js";var r,i,a,o,s;e((()=>{n(),r={title:`UI/Input`,component:t,tags:[`autodocs`],argTypes:{type:{control:`select`,options:[`text`,`password`,`email`,`number`,`url`]},placeholder:{control:`text`},disabled:{control:`boolean`}},args:{type:`text`,placeholder:`Enter text...`,disabled:!1}},i={render:e=>({components:{Input:t},setup:()=>({args:e}),template:`<div class="max-w-sm p-6"><Input v-bind="args" /></div>`})},a={args:{type:`password`,placeholder:`Enter password...`},render:e=>({components:{Input:t},setup:()=>({args:e}),template:`<div class="max-w-sm p-6"><Input v-bind="args" /></div>`})},o={args:{disabled:!0,placeholder:`Disabled input`},render:e=>({components:{Input:t},setup:()=>({args:e}),template:`<div class="max-w-sm p-6"><Input v-bind="args" /></div>`})},i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  render: args => ({
    components: {
      Input
    },
    setup: () => ({
      args
    }),
    template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>'
  })
}`,...i.parameters?.docs?.source}}},a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  args: {
    type: 'password',
    placeholder: 'Enter password...'
  },
  render: args => ({
    components: {
      Input
    },
    setup: () => ({
      args
    }),
    template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>'
  })
}`,...a.parameters?.docs?.source}}},o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    disabled: true,
    placeholder: 'Disabled input'
  },
  render: args => ({
    components: {
      Input
    },
    setup: () => ({
      args
    }),
    template: '<div class="max-w-sm p-6"><Input v-bind="args" /></div>'
  })
}`,...o.parameters?.docs?.source}}},s=[`Default`,`Password`,`Disabled`]}))();export{i as Default,o as Disabled,a as Password,s as __namedExportsOrder,r as default};