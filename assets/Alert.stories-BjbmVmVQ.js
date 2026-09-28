import{n as e}from"./chunk-DnJy8xQt.js";import{i as t,n,r,t as i}from"./alert-I8gQIQlE.js";var a,o,s,c;e((()=>{i(),a={title:`UI/Alert`,component:t,tags:[`autodocs`],argTypes:{variant:{control:`select`,options:[`default`,`destructive`]}},args:{variant:`default`}},o={render:e=>({components:{Alert:t,AlertTitle:n,AlertDescription:r},setup:()=>({args:e}),template:`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Heads up!</AlertTitle>
              <AlertDescription>
                You can add components to your app using the cli.
              </AlertDescription>
            </Alert>
          </div>
        `})},s={args:{variant:`destructive`},render:e=>({components:{Alert:t,AlertTitle:n,AlertDescription:r},setup:()=>({args:e}),template:`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>
                Your session has expired. Please log in again.
              </AlertDescription>
            </Alert>
          </div>
        `})},o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  render: args => ({
    components: {
      Alert,
      AlertTitle,
      AlertDescription
    },
    setup: () => ({
      args
    }),
    template: \`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Heads up!</AlertTitle>
              <AlertDescription>
                You can add components to your app using the cli.
              </AlertDescription>
            </Alert>
          </div>
        \`
  })
}`,...o.parameters?.docs?.source}}},s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'destructive'
  },
  render: args => ({
    components: {
      Alert,
      AlertTitle,
      AlertDescription
    },
    setup: () => ({
      args
    }),
    template: \`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>
                Your session has expired. Please log in again.
              </AlertDescription>
            </Alert>
          </div>
        \`
  })
}`,...s.parameters?.docs?.source}}},c=[`Default`,`Destructive`]}))();export{o as Default,s as Destructive,c as __namedExportsOrder,a as default};