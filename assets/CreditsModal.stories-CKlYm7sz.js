import{n as e}from"./chunk-DnJy8xQt.js";import{N as t,t as n}from"./vue.esm-bundler-BO0T5OF3.js";import{n as r,r as i}from"./iframe-6UZCWRTq.js";import{m as a,n as o,p as s,t as c}from"./modal-DlA2V8wY.js";import{C as l,n as u,t as d,w as f}from"./CreditsModal-CIDl6A6E.js";var p,m,h,g,_,v,y;e((()=>{a(),n(),c(),l(),r(),u(),p=e=>({usages:{data:Array.from({length:8},(t,n)=>{let r=(n+(e-1)*8)%3!=0;return{id:(e-1)*8+n+1,created_at:new Date(2026,4,8-((e-1)*8+n)).toISOString(),credits:Math.floor(Math.random()*150)+1,type:r?`usage`:`topup`,description:r?void 0:`Top-up transaction ${(e-1)*8+n+1}`,entry:r?{name:n===0&&e===1?`Episode ${(e-1)*8+n+1}: This is an extremely long episode name that should definitely break the UI if it is not handled correctly with truncation or wrapping`:`Episode ${(e-1)*8+n+1}: Standard podcast episode title`}:void 0}}),links:[{url:e>1?`/credits?page=${e-1}`:null,label:`&larr; Previous`,active:!1},{url:`/credits?page=1`,label:`1`,active:e===1},{url:`/credits?page=2`,label:`2`,active:e===2},{url:`/credits?page=3`,label:`3`,active:e===3},{url:`/credits?page=4`,label:`4`,active:e===4},{url:`/credits?page=5`,label:`5`,active:e===5},{url:`/credits?page=6`,label:`6`,active:e===6},{url:`/credits?page=7`,label:`7`,active:e===7},{url:`/credits?page=8`,label:`8`,active:e===8},{url:`/credits?page=9`,label:`9`,active:e===9},{url:`/credits?page=10`,label:`10`,active:e===10},{url:`/credits?page=11`,label:`11`,active:e===11},{url:`/credits?page=12`,label:`12`,active:e===12},{url:`/credits?page=13`,label:`13`,active:e===13},{url:`/credits?page=14`,label:`14`,active:e===14},{url:`/credits?page=15`,label:`15`,active:e===15},{url:`/credits?page=16`,label:`16`,active:e===16},{url:`/credits?page=17`,label:`17`,active:e===17},{url:e<17?`/credits?page=${e+1}`:null,label:`Next &rarr;`,active:!1}],path:`/credits`,current_page:e},current_credits:100}),m={title:`Modals/CreditsModal`,component:d,args:{delay:1e3},parameters:{inertia:{props:{appUrl:`http://localhost`,auth:{user:{...f,credits:100}}}}},decorators:[(e,{args:t})=>(i({props:{auth:{user:{...f,credits:t.credits}}}}),window.fetch=(async e=>{if(e.includes(`/credits`)){let n=e.match(/page=(\d+)/),r=n?parseInt(n[1]):1;return t.delay&&await new Promise(e=>setTimeout(e,t.delay)),{ok:!0,json:()=>Promise.resolve(t.response||p(r))}}return Promise.reject(Error(`Unknown URL`))}),e())],render:()=>({components:{ModalsPortal:o},setup(){return t(()=>{s(d)}),{}},template:`<ModalsPortal />`})},h={args:{credits:100}},g={args:{credits:0,response:{usages:{data:[],links:[]},current_credits:0}}},_={args:{credits:42,response:{usages:{data:[{id:1,type:`usage`,created_at:new Date(2026,4,8).toISOString(),credits:12,entry:{id:1,name:`Episode 1: Failed processing should show an icon button`,slug:`episode-1-failed-processing`,latest_job_batch:{job_batch:{id:`test-batch-id`,finished_at:null,cancelled_at:Date.now(),failed_job_details:[{exception:`Something went wrong.`}]}}}},{id:2,type:`usage`,created_at:new Date(2026,4,8,1).toISOString(),credits:7,entry:{id:2,name:`Episode 2: Still processing should show a spinner`,slug:`episode-2-still-processing`,latest_job_batch:{job_batch:{id:`test-batch-id-2`,finished_at:null,cancelled_at:null}}}}],links:[]},current_credits:42}}},v={decorators:[e=>(window.fetch=(()=>new Promise(()=>{})),e())]},h.parameters={...h.parameters,docs:{...h.parameters?.docs,source:{originalSource:`{
  args: {
    credits: 100
  }
}`,...h.parameters?.docs?.source}}},g.parameters={...g.parameters,docs:{...g.parameters?.docs,source:{originalSource:`{
  args: {
    credits: 0,
    response: {
      usages: {
        data: [],
        links: []
      },
      current_credits: 0
    }
  }
}`,...g.parameters?.docs?.source}}},_.parameters={..._.parameters,docs:{..._.parameters?.docs,source:{originalSource:`{
  args: {
    credits: 42,
    response: {
      usages: {
        data: [{
          id: 1,
          type: 'usage',
          created_at: new Date(2026, 4, 8).toISOString(),
          credits: 12,
          entry: {
            id: 1,
            name: 'Episode 1: Failed processing should show an icon button',
            slug: 'episode-1-failed-processing',
            latest_job_batch: {
              job_batch: {
                id: 'test-batch-id',
                finished_at: null,
                cancelled_at: Date.now(),
                failed_job_details: [{
                  exception: 'Something went wrong.'
                }]
              }
            }
          }
        }, {
          id: 2,
          type: 'usage',
          created_at: new Date(2026, 4, 8, 1).toISOString(),
          credits: 7,
          entry: {
            id: 2,
            name: 'Episode 2: Still processing should show a spinner',
            slug: 'episode-2-still-processing',
            latest_job_batch: {
              job_batch: {
                id: 'test-batch-id-2',
                finished_at: null,
                cancelled_at: null
              }
            }
          }
        }],
        links: []
      },
      current_credits: 42
    }
  }
}`,..._.parameters?.docs?.source}}},v.parameters={...v.parameters,docs:{...v.parameters?.docs,source:{originalSource:`{
  decorators: [story => {
    window.fetch = (() => new Promise(() => {})) as any;
    return story();
  }]
}`,...v.parameters?.docs?.source}}},y=[`Many`,`Empty`,`ProcessingFeedback`,`Loading`]}))();export{g as Empty,v as Loading,h as Many,_ as ProcessingFeedback,y as __namedExportsOrder,m as default};