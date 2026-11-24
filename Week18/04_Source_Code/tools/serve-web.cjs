const http=require('http'),fs=require('fs'),path=require('path');
const root=path.resolve(__dirname,'../build/web');
http.createServer((q,s)=>{
 const route=decodeURIComponent(q.url.split('?')[0]);
 const file=path.resolve(root,'.'+(route==='/'?'/index.html':route));
 if(!file.startsWith(root+path.sep)){s.writeHead(403);s.end();return}
 s.setHeader('Content-Type',({'.html':'text/html','.js':'application/javascript','.json':'application/json','.wasm':'application/wasm','.png':'image/png','.ttf':'font/ttf'})[path.extname(file)]||'application/octet-stream');
 fs.createReadStream(file).on('error',()=>{s.statusCode=404;s.end('Not found')}).pipe(s);
}).listen(5046,'127.0.0.1',()=>console.log('Local fictional demo: http://127.0.0.1:5046'));
