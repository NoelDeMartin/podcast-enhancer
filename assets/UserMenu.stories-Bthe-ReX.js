import{n as e}from"./chunk-DnJy8xQt.js";import{n as t,r as n}from"./iframe-6UZCWRTq.js";import{n as r,t as i}from"./UserMenu-Chppxu5Q.js";var a,o,s,c,l;e((()=>{t(),r(),a={title:`UI/UserMenu`,component:i,parameters:{layout:`centered`},decorators:[e=>({components:{story:e},template:`<div class="flex items-center justify-center p-12 bg-neo-bg min-h-[200px]"><story /></div>`})]},o={args:{},play:()=>{n({props:{auth:{user:{name:`John Doe`,email:`john@example.com`,avatar:`https://github.com/shadcn.png`,plan:`basic`}}}})}},s={args:{},play:()=>{n({props:{auth:{user:{name:`Jane Pro`,email:`jane@example.com`,avatar:`https://github.com/shadcn.png`,plan:`pro`}}}})}},c={args:{},play:()=>{n({props:{auth:{user:{name:`No Avatar User`,email:`noavatar@example.com`,avatar:null,plan:`basic`}}}})}},o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {},
  play: () => {
    setInertiaPage({
      props: {
        auth: {
          user: {
            name: 'John Doe',
            email: 'john@example.com',
            avatar: 'https://github.com/shadcn.png',
            plan: 'basic'
          }
        }
      }
    });
  }
}`,...o.parameters?.docs?.source}}},s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {},
  play: () => {
    setInertiaPage({
      props: {
        auth: {
          user: {
            name: 'Jane Pro',
            email: 'jane@example.com',
            avatar: 'https://github.com/shadcn.png',
            plan: 'pro'
          }
        }
      }
    });
  }
}`,...s.parameters?.docs?.source}}},c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  args: {},
  play: () => {
    setInertiaPage({
      props: {
        auth: {
          user: {
            name: 'No Avatar User',
            email: 'noavatar@example.com',
            avatar: null,
            plan: 'basic'
          }
        }
      }
    });
  }
}`,...c.parameters?.docs?.source}}},l=[`Default`,`ProPlan`,`NoAvatar`]}))();export{o as Default,c as NoAvatar,s as ProPlan,l as __namedExportsOrder,a as default};