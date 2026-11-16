import{n as e}from"./rolldown-runtime-sJBDdmOt.js";import{p as t,rt as n,vt as r}from"./iframe-C99Q27xO.js";import{n as i,t as a}from"./checkbox-CJeOY9RY.js";var o,s,c,l,u,d;function f(){return(f=e((()=>{t(),a(),o={title:`UI/Checkbox`,component:i,tags:[`autodocs`],argTypes:{modelValue:{control:`boolean`},disabled:{control:`boolean`},label:{control:`text`}},args:{modelValue:!1,disabled:!1,label:`Remember me`}},s={render:e=>({components:{Checkbox:i},setup:()=>{let t=r(e.modelValue);return n(()=>e.modelValue,e=>{t.value=e}),{args:e,value:t}},template:`
            <label class="inline-flex items-center gap-3 p-6 font-mono font-black tracking-wider uppercase">
                <Checkbox
                    :model-value="value"
                    :disabled="args.disabled"
                    @update:modelValue="(v) => (value = v)"
                />
                <span class="text-sm leading-none">{{ args.label }}</span>
            </label>
        `})},c={args:{modelValue:!0},...s},l={args:{disabled:!0},...s},u={args:{modelValue:!0,disabled:!0},...s},d=[`Default`,`Checked`,`Disabled`,`DisabledChecked`]})))()}f();export{c as Checked,s as Default,l as Disabled,u as DisabledChecked,d as __namedExportsOrder,o as default};