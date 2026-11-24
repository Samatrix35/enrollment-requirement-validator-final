const https=require('https'),http=require('http'),fs=require('fs');
const [url='http://127.0.0.1:8046/api/health/ready',caFile]=process.argv.slice(2);
const started=performance.now();
const req=(url.startsWith('https:')?https:http).get(url,{headers:{Accept:'application/json'},...(caFile?{ca:fs.readFileSync(caFile)}:{}),rejectUnauthorized:true},res=>{let body='';res.on('data',d=>body+=d);res.on('end',()=>{console.log(JSON.stringify({time:new Date().toISOString(),status:res.statusCode,duration_ms:performance.now()-started,request_id:res.headers['x-request-id'],body:JSON.parse(body)},null,2));if(res.statusCode!==200)process.exitCode=1})});
req.setTimeout(5000,()=>req.destroy(new Error('Health probe timed out')));
req.on('error',e=>{console.error(JSON.stringify({status:'unavailable',message:e.message}));process.exitCode=1});
