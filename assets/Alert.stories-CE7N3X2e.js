import{n as e}from"./rolldown-runtime-sJBDdmOt.js";import{i as t,n,o as r,t as i}from"./alert-B1RxH__r.js";var a,o,s,c;function l(){return(l=e((()=>{i(),a={title:`UI/Alert`,component:r,tags:[`autodocs`],argTypes:{variant:{control:`select`,options:[`default`,`destructive`]}},args:{variant:`default`}},o={render:e=>({components:{Alert:r,AlertTitle:n,AlertDescription:t},setup:()=>({args:e}),template:`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Heads up!</AlertTitle>
              <AlertDescription>
                You can add components to your app using the cli.
              </AlertDescription>
            </Alert>
          </div>
        `})},s={args:{variant:`destructive`},render:e=>({components:{Alert:r,AlertTitle:n,AlertDescription:t},setup:()=>({args:e}),template:`
          <div class="p-6">
            <Alert v-bind="args">
              <AlertTitle>Error</AlertTitle>
              <AlertDescription>
                Your session has expired. Please log in again.
              </AlertDescription>
            </Alert>
          </div>
        `})},c=[`Default`,`Destructive`]})))()}l();export{o as Default,s as Destructive,c as __namedExportsOrder,a as default};