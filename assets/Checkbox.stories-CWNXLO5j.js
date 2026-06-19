import{n as e}from"./chunk-DnJy8xQt.js";import{K as t,ot as n,t as r}from"./vue.esm-bundler-BO0T5OF3.js";import{n as i,t as a}from"./checkbox-BmS2U12s.js";var o,s,c,l,u,d;e((()=>{r(),a(),o={title:`UI/Checkbox`,component:i,tags:[`autodocs`],argTypes:{modelValue:{control:`boolean`},disabled:{control:`boolean`},label:{control:`text`}},args:{modelValue:!1,disabled:!1,label:`Remember me`}},s={render:e=>({components:{Checkbox:i},setup:()=>{let r=n(e.modelValue);return t(()=>e.modelValue,e=>{r.value=e}),{args:e,value:r}},template:`
            <label class="inline-flex items-center gap-3 p-6 font-mono font-black tracking-wider uppercase">
                <Checkbox
                    :model-value="value"
                    :disabled="args.disabled"
                    @update:modelValue="(v) => (value = v)"
                />
                <span class="text-sm leading-none">{{ args.label }}</span>
            </label>
        `})},c={args:{modelValue:!0},...s},l={args:{disabled:!0},...s},u={args:{modelValue:!0,disabled:!0},...s},s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  render: args => ({
    components: {
      Checkbox
    },
    setup: () => {
      const value = ref(args.modelValue);
      watch(() => args.modelValue, next => {
        value.value = next;
      });
      return {
        args,
        value
      };
    },
    template: \`
            <label class="inline-flex items-center gap-3 p-6 font-mono font-black tracking-wider uppercase">
                <Checkbox
                    :model-value="value"
                    :disabled="args.disabled"
                    @update:modelValue="(v) => (value = v)"
                />
                <span class="text-sm leading-none">{{ args.label }}</span>
            </label>
        \`
  })
}`,...s.parameters?.docs?.source}}},c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  args: {
    modelValue: true
  },
  ...Default
}`,...c.parameters?.docs?.source}}},l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  args: {
    disabled: true
  },
  ...Default
}`,...l.parameters?.docs?.source}}},u.parameters={...u.parameters,docs:{...u.parameters?.docs,source:{originalSource:`{
  args: {
    modelValue: true,
    disabled: true
  },
  ...Default
}`,...u.parameters?.docs?.source}}},d=[`Default`,`Checked`,`Disabled`,`DisabledChecked`]}))();export{c as Checked,s as Default,l as Disabled,u as DisabledChecked,d as __namedExportsOrder,o as default};