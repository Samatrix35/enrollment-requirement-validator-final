const https=require('https'),http=require('http'),fs=require('fs');
const [backendPort,tlsPort,keyFile,certFile]=process.argv.slice(2);
https.createServer({key:fs.readFileSync(keyFile),cert:fs.readFileSync(certFile)},(req,res)=>{
 const upstream=http.request({hostname:'127.0.0.1',port:Number(backendPort),method:req.method,path:req.url,headers:{...req.headers,'x-forwarded-proto':'https'}},r=>{res.writeHead(r.statusCode,r.headers);r.pipe(res)});
 upstream.on('error',()=>{res.writeHead(503,{'Content-Type':'application/json','Cache-Control':'no-store'});res.end('{"status":"upstream_unavailable"}')});
 req.pipe(upstream);
}).listen(Number(tlsPort),'127.0.0.1',()=>console.log('TLS proxy ready'));
