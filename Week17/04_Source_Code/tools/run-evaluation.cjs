/* Week 16 evaluator. All database resets/restore operations target a NEW runtime
   directory created by this script. The user's application database is untouched. */
const fs=require('fs'),path=require('path'),os=require('os'),http=require('http'),https=require('https'),crypto=require('crypto');
const {spawn,execFileSync}=require('child_process');
const src=path.resolve(__dirname,'..'),api=path.join(src,'enrollment_validator_api');
const plan=JSON.parse(fs.readFileSync(path.join(__dirname,'WORKLOAD_PLAN.json')));
const custom=process.argv.indexOf('--output');
const out=custom>=0?path.resolve(process.argv[custom+1]):path.join(__dirname,'runtime','results-'+Date.now());
if(fs.existsSync(path.join(out,'workload-summary.json')))throw Error('Choose a fresh output directory; recorded results are not overwritten.');
fs.mkdirSync(out,{recursive:true});
const runtime=fs.mkdtempSync(path.join(__dirname,'runtime-'));
// Laravel interprets drive-letter cache paths as relative on Windows. Use paths
// relative to the API directory, resolving them explicitly for Node file I/O.
const cachePath=name=>path.relative(api,path.join(runtime,name));
const cacheFile=value=>path.resolve(api,value);
const php=process.env.PHP_BIN||'php';
const children=[];const servers={};const processSamples=[];const samplerErrors=[];
const delay=ms=>new Promise(r=>setTimeout(r,ms));
const save=(name,data)=>fs.writeFileSync(path.join(out,name),JSON.stringify(data,null,2));
const hash=file=>crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const now=()=>Date.now();
function command(args,env={}){return execFileSync(php,args,{cwd:api,env:{...process.env,...env},encoding:'utf8',windowsHide:true,maxBuffer:20*1024*1024});}
function spawnTracked(bin,args,options={}){const child=spawn(bin,args,{windowsHide:true,...options});children.push(child);return child;}
const baseEnv={APP_KEY:'base64:'+crypto.randomBytes(32).toString('base64'),APP_RELEASE:'w16-r1',APP_DEBUG:'false',CACHE_STORE:'file',SESSION_DRIVER:'file',LOG_CHANNEL:'single',LOG_LEVEL:'info',DB_BUSY_TIMEOUT:'5000',DB_JOURNAL_MODE:'WAL'};
const devDB=path.join(runtime,'development.sqlite'),prodDB=path.join(runtime,'controlled.sqlite');
const devEnv={...baseEnv,APP_ENV:'local',APP_DEBUG:'true',DB_DATABASE:devDB,APP_CONFIG_CACHE:cachePath('unused-dev-config.php'),APP_ROUTES_CACHE:cachePath('unused-dev-routes.php'),FRONTEND_URLS:'http://127.0.0.1:5046'};
const prodEnv={...baseEnv,APP_ENV:'production',DB_DATABASE:prodDB,APP_CONFIG_CACHE:cachePath('release-r1-config.php'),APP_ROUTES_CACHE:cachePath('release-r1-routes.php'),TRUSTED_PROXIES:'127.0.0.1'};
let fixtures;let cert;let sampler;
function request(url,{method='GET',token,body,timeout=12000}={}){
 return new Promise(resolve=>{
  const started=performance.now();const started_at=now();
  const req=(url.startsWith('https:')?https:http).request(url,{method,headers:{Accept:'application/json',...(token?{Authorization:'Bearer '+token}:{}),...(body?{'Content-Type':'application/json'}:{})},...(url.startsWith('https:')?{ca:cert,rejectUnauthorized:true}:{})},res=>{
   let text='';res.on('data',d=>text+=d);res.on('end',()=>resolve({started_at,duration_ms:performance.now()-started,status:res.statusCode,headers:res.headers,body:text,certificate_authorized:res.socket?.authorized??null,tls_protocol:res.socket?.getProtocol?.()??null}));res.on('error',e=>resolve({started_at,duration_ms:performance.now()-started,status:0,error:e.code||e.message}));
  });
  req.setTimeout(timeout,()=>req.destroy(Object.assign(new Error('request timeout'),{code:'TIMEOUT'})));
  req.on('error',e=>resolve({started_at,duration_ms:performance.now()-started,status:0,error:e.code||e.message}));
  if(body)req.write(JSON.stringify(body));req.end();
 });
}
function startPHP(name,env,port){
 const err=fs.createWriteStream(path.join(runtime,name+'-server.log'),{flags:'a'});
 const p=spawnTracked(php,['-S',`127.0.0.1:${port}`,'-t','public','public/index.php'],{cwd:api,env:{...process.env,...env},stdio:['ignore','ignore','pipe']});p.stderr.pipe(err);servers[name]=p;return p;
}
async function waitReady(base,release='w16-r1',limit=15000){
 const started=now();let last;
 while(now()-started<limit){last=await request(base+'/health/ready',{timeout:2000});try{if(last.status===200&&JSON.parse(last.body).release===release)return {elapsed_ms:now()-started,result:last};}catch{}await delay(150);}
 throw Error('Readiness did not recover: '+JSON.stringify(last));
}
function quantile(values,q){const a=[...values].sort((a,b)=>a-b);return a[Math.max(0,Math.ceil(q*a.length)-1)]??null;}
async function run(name,base,profile,repeat){
 const cfg=plan.profiles[profile];
 for(let i=0;i<plan.warmup_requests;i++)await request(base+'/my/enrollment',{token:fixtures.users[i].token});
 const raw=[],health=[];const began=now(),deadline=began+plan.duration_seconds*1000;
 async function call(operation,method,url,token,body){
  const r=await request(base+url,{method,token,body});
  raw.push({operation,started_at:r.started_at,duration_ms:r.duration_ms,status:r.status,expected:200,request_id:r.headers?.['x-request-id']??null,...(r.error?{transport_error:r.error}:{})});return r;
 }
 const workers=Array.from({length:cfg.virtual_users},(_,index)=>(async()=>{
  const user=fixtures.users[index+(repeat-1)*20];let iteration=index;
  while(now()<deadline){
   const op=iteration++%5;
   if(op===2)await call('staff_queue','GET','/requirements?status=Submitted',fixtures.staff_token);
   else if(op===3){
    const sent=await call('student_submit','PATCH',`/my/requirements/${user.requirement_id}/submit`,user.token);
    if(sent.status===200)await call('staff_return','PATCH',`/requirements/${user.requirement_id}`,fixtures.staff_token,{status:'Rejected',remarks:'Fictional workload correction cycle.'});
   }else await call('student_checklist','GET','/my/enrollment',user.token);
   if(cfg.think_ms)await delay(cfg.think_ms);
  }
 })());
 const monitor=(async()=>{while(now()<deadline){const h=await request(base+'/health/ready');health.push({started_at:h.started_at,duration_ms:h.duration_ms,status:h.status});await delay(plan.readiness_probe_interval_ms)}})();
 await Promise.all([...workers,monitor]);const ended=now();
 const failures=raw.filter(r=>r.status!==r.expected);const latencies=raw.map(r=>r.duration_ms);
 const summary={environment:name,profile,repeat,virtual_users:cfg.virtual_users,think_ms:cfg.think_ms,started_at:began,ended_at:ended,actual_elapsed_seconds:(ended-began)/1000,requests:raw.length,unexpected_errors:failures.length,error_percent:100*failures.length/raw.length,requests_per_second:raw.length/((ended-began)/1000),latency_ms:{p50:quantile(latencies,.5),p95:quantile(latencies,.95),p99:quantile(latencies,.99),max:Math.max(...latencies)},health:{probes:health.length,failures:health.filter(r=>r.status!==200).length,p95_ms:quantile(health.map(r=>r.duration_ms),.95)}};
 summary.gates={p95_pass:summary.latency_ms.p95<=cfg.p95_target_ms,error_pass:summary.error_percent<plan.unexpected_error_target_percent,throughput_pass:summary.requests_per_second>=cfg.minimum_rps,health_pass:summary.health.failures===0&&summary.health.p95_ms<=plan.readiness_p95_target_ms};
 save(`${name}-${profile}-${repeat}-requests.json`,raw);save(`${name}-${profile}-${repeat}-health.json`,health);save(`${name}-${profile}-${repeat}-summary.json`,summary);
 console.log(`${name} ${profile} repeat ${repeat}: ${raw.length} requests, p95=${summary.latency_ms.p95.toFixed(1)} ms, ${summary.requests_per_second.toFixed(2)} rps, ${failures.length} unexpected errors`);
 return summary;
}
async function stopServer(name){const p=servers[name];if(p){p.kill();await delay(400);}}
async function main(){
 save('workload-plan.json',plan);
 save('host-environment.json',{captured_at:new Date().toISOString(),os:os.type(),release:os.release(),architecture:os.arch(),logical_cpus:os.cpus().length,cpu_model:os.cpus()[0]?.model,total_memory_bytes:os.totalmem(),node:process.version,php:command(['--version']).split('\n')[0],measurement_scope:'Same host, sequential environments, single PHP built-in server worker. Load generator and collector share the host. No OPcache extension loaded in captured PHP.'});
 fs.writeFileSync(devDB,'');
 fs.writeFileSync(path.join(out,'fixture-migrations.txt'),command(['artisan','migrate','--force','--no-ansi'],devEnv));
 fixtures=JSON.parse(command([path.join(__dirname,'create-week16-fixture.php')],{...devEnv,W16_FIXTURE_ALLOWED:'yes'}));
 fs.writeFileSync(path.join(runtime,'private-fixture.json'),JSON.stringify(fixtures));
 fs.copyFileSync(devDB,prodDB);
 save('fixture-summary.json',{students:fixtures.students,requirements:fixtures.requirements,roles:['student','enrollment_staff'],credential_scope:'Preissued isolated fictional accounts; tokens remain in excluded runtime files. Login latency is not measured.'});
 const planCode="$db=new PDO('sqlite:'.getenv('DB_DATABASE'));$db->exec('DROP INDEX requirements_status_id_index');$before=$db->query(\"EXPLAIN QUERY PLAN SELECT * FROM requirements WHERE status='Submitted' ORDER BY id LIMIT 50\")->fetchAll(PDO::FETCH_ASSOC);$db->exec('CREATE INDEX requirements_status_id_index ON requirements(status,id)');$after=$db->query(\"EXPLAIN QUERY PLAN SELECT * FROM requirements WHERE status='Submitted' ORDER BY id LIMIT 50\")->fetchAll(PDO::FETCH_ASSOC);echo json_encode(['before'=>$before,'after'=>$after]);";
 save('queue-query-plan.json',JSON.parse(command(['-r',planCode],devEnv)));
 command(['artisan','config:cache','--no-ansi'],prodEnv);command(['artisan','route:cache','--no-ansi'],prodEnv);
 fs.writeFileSync(path.join(out,'release-manifest.json'),JSON.stringify({release:'w16-r1',created_at:new Date().toISOString(),configuration_sha256:hash(cacheFile(prodEnv.APP_CONFIG_CACHE)),routes_sha256:hash(cacheFile(prodEnv.APP_ROUTES_CACHE)),database: 'isolated 200-student / 600-requirement fixture',framework_version:JSON.parse(fs.readFileSync(path.join(api,'vendor/composer/installed.json'))).packages.find(x=>x.name==='laravel/framework').version},null,2));
 const openssl=process.env.OPENSSL_BIN||'C:/xampp/apache/bin/openssl.exe';
 const key=path.join(runtime,'localhost-key.pem'),pem=path.join(runtime,'localhost.pem');
 execFileSync(openssl,['req','-x509','-newkey','rsa:2048','-nodes','-keyout',key,'-out',pem,'-days','2','-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost,IP:127.0.0.1'],{windowsHide:true,stdio:'ignore',env:{...process.env,OPENSSL_CONF:process.env.OPENSSL_CONF||'C:/xampp/apache/conf/openssl.cnf'}});
 cert=fs.readFileSync(pem);fs.copyFileSync(pem,path.join(out,'local-test-public-certificate.pem'));
 const dev=startPHP('development',devEnv,8046);
 const prod=startPHP('controlled',prodEnv,8047);
 const proxy=spawnTracked(process.execPath,[path.join(__dirname,'tls-proxy.cjs'),'8047','8447',key,pem],{stdio:['ignore','pipe','pipe']});
 proxy.stderr.on('data',d=>samplerErrors.push('proxy: '+String(d)));
 await waitReady('http://127.0.0.1:8046/api');const secure=await waitReady('https://localhost:8447/api');
 const plain=await request('http://127.0.0.1:8047/api/health/ready');
 save('https-verification.json',{verified_https_status:secure.result.status,certificate_authorized:secure.result.certificate_authorized,tls_protocol:secure.result.tls_protocol,strict_verification:true,plain_http_status:plain.status,insecure_mode:false});
 const targets=[{id:dev.pid,role:'development_php'},{id:prod.pid,role:'controlled_php'},{id:proxy.pid,role:'tls_proxy'}];
 const sampleScript=fs.readFileSync(path.join(__dirname,'measure-processes.ps1'),'utf8').replace(/^param\([^\n]+\)\r?\n/,'').replace('$targets=$TargetsJson | ConvertFrom-Json',`$targets='${JSON.stringify(targets)}' | ConvertFrom-Json`);
 sampler=spawnTracked('powershell.exe',['-NoProfile','-NonInteractive','-Command',sampleScript],{stdio:['ignore','pipe','pipe']});
 let pending='';sampler.stdout.on('data',data=>{pending+=data;let n;while((n=pending.indexOf('\n'))>=0){const line=pending.slice(0,n).trim();pending=pending.slice(n+1);try{if(line)processSamples.push(JSON.parse(line))}catch{samplerErrors.push(line)}}});sampler.stderr.on('data',d=>samplerErrors.push(String(d)));
 await delay(2000);
 const summaries=[];
 for(const [name,base] of [['development','http://127.0.0.1:8046/api'],['controlled','https://localhost:8447/api']]){
  for(let repeat=1;repeat<=plan.repetitions;repeat++)for(const profile of ['ordinary','peak'])summaries.push(await run(name,base,profile,repeat));
 }
 // Real failed-configuration deployment and restoration drill on the isolated DB.
 await stopServer('controlled');
 const snapshot=path.join(runtime,'checkpoint.sqlite');
 command(['-r',"$db=new PDO('sqlite:'.getenv('DB_DATABASE'));$db->exec('PRAGMA wal_checkpoint(TRUNCATE)');"],prodEnv);
 fs.copyFileSync(prodDB,snapshot);const checkpointHash=hash(snapshot);
 const beforeRow=JSON.parse(command(['-r',"$db=new PDO('sqlite:'.getenv('DB_DATABASE'));echo json_encode($db->query('SELECT id,status,remarks FROM requirements WHERE id=1')->fetch(PDO::FETCH_ASSOC));"],prodEnv));
 const oldConfig=fs.readFileSync(cacheFile(prodEnv.APP_CONFIG_CACHE)),oldRoutes=fs.readFileSync(cacheFile(prodEnv.APP_ROUTES_CACHE));
 const badEnv={...prodEnv,APP_RELEASE:'w16-bad',DB_DATABASE:path.join(runtime,'NONEXISTENT-DATABASE.sqlite'),APP_CONFIG_CACHE:cachePath('bad-config.php')};
 command(['artisan','config:cache','--no-ansi'],badEnv);
 const faultStarted=now();startPHP('candidate',badEnv,8047);await delay(600);
 const live=await request('https://localhost:8447/api/health/live');const bad=await request('https://localhost:8447/api/health/ready');const faultDetected=now();
 if(live.status!==200||bad.status!==503)throw Error('The controlled fault did not produce the expected live/ready split: '+JSON.stringify({live,bad}));
 const rollbackStarted=now();await stopServer('candidate');
 fs.writeFileSync(cacheFile(prodEnv.APP_CONFIG_CACHE),oldConfig);fs.writeFileSync(cacheFile(prodEnv.APP_ROUTES_CACHE),oldRoutes);
 // Backend is stopped; discard only this isolated run's WAL/SHM before restore.
 for(const suffix of ['-wal','-shm']){try{fs.unlinkSync(prodDB+suffix)}catch(e){if(e.code!=='ENOENT')throw e}}
 fs.copyFileSync(snapshot,prodDB);const copiedHash=hash(prodDB);startPHP('restored',prodEnv,8047);
 const restored=await waitReady('https://localhost:8447/api');const rollbackEnded=now();
 const record=await request('https://localhost:8447/api/my/enrollment',{token:fixtures.users[0].token});
 const afterRow=JSON.parse(command(['-r',"$db=new PDO('sqlite:'.getenv('DB_DATABASE'));echo json_encode($db->query('SELECT id,status,remarks FROM requirements WHERE id=1')->fetch(PDO::FETCH_ASSOC));"],prodEnv));
 const drill={fault:'Candidate deployment points to an unavailable SQLite database',fault_started_at:faultStarted,fault_detected_at:faultDetected,detection_ms:faultDetected-faultStarted,candidate_liveness_status:live.status,candidate_readiness_status:bad.status,candidate_release:JSON.parse(live.body).release,rollback_started_at:rollbackStarted,rollback_ended_at:rollbackEnded,recovery_seconds:(rollbackEnded-rollbackStarted)/1000,recovery_target_seconds:plan.recovery_target_seconds,restored_readiness_status:restored.result.status,restored_release:JSON.parse(restored.result.body).release,authenticated_workflow_status:record.status,checkpoint_sha256:checkpointHash,restored_file_sha256_before_requests:copiedHash,checkpoint_record:beforeRow,restored_record:afterRow,checkpoint_restored_exactly:copiedHash===checkpointHash,record_preserved:JSON.stringify(beforeRow)===JSON.stringify(afterRow),scope:'Configuration rollback and point-in-time SQLite restore, with the backend stopped. No legitimate writes occurred after the checkpoint; not proof of continuous production RPO=0, schema rollback, or multi-host failover.'};
 drill.pass=drill.recovery_seconds<=plan.recovery_target_seconds&&drill.restored_readiness_status===200&&record.status===200&&drill.record_preserved&&drill.checkpoint_restored_exactly;
 save('rollback-recovery.json',drill);
 sampler.kill();await delay(500);
 for(const s of summaries){
  const role=s.environment==='development'?'development_php':'controlled_php';
  const points=processSamples.filter(p=>p.role===role&&p.timestamp>=s.started_at&&p.timestamp<=s.ended_at);
  if(points.length<2)throw Error('Insufficient Windows process samples for '+role);
  const first=points[0],last=points.at(-1);
  s.resources={sample_count:points.length,backend_cpu_seconds:last.cpu_seconds-first.cpu_seconds,sampled_cpu_window_seconds:(last.timestamp-first.timestamp)/1000,mean_one_core_cpu_percent:100*(last.cpu_seconds-first.cpu_seconds)/((last.timestamp-first.timestamp)/1000),sampled_peak_rss_mib:Math.max(...points.map(p=>p.working_set_bytes))/1048576,sampled_peak_private_mib:Math.max(...points.map(p=>p.private_bytes))/1048576};
 }
 save('process-samples.json',processSamples);save('collector-notes.json',{sampling_interval_ms:1000,errors:samplerErrors,interpretation:'Windows Get-Process TotalProcessorTime and WorkingSet64. CPU percent is one logical-core equivalent, not host-wide percent. Memory is sampled RSS/private bytes, not PHP heap. Collector and load generator share the host; CPU-window endpoints exclude fractions outside sample boundaries.'});
 save('workload-summary.json',{captured_at:new Date().toISOString(),plan,results:summaries,measurement_method:'Client end-to-end complete-response latency; actual completed request count / actual elapsed time including drain. Warmup excluded. Preissued tokens, no login-rate test. Monitoring probes are recorded separately and excluded from application throughput.'});
 console.log('Rollback pass='+drill.pass+', recovery='+drill.recovery_seconds.toFixed(2)+' s');
 // Retain private fixtures only in ignored runtime storage for investigation.
 save('run-provenance.json',{started_from:'Provided Week 15 Project 23 source, with Week 16 operations additions',source_tools_sha256:hash(__filename),runtime_private_directory:path.basename(runtime),output:out,finished_at:new Date().toISOString(),credential_and_database_artifacts_excluded:true});
}
main().catch(e=>{console.error(e);save('evaluation-error.json',{message:e.message,stack:e.stack});process.exitCode=1}).finally(()=>{for(const p of children){try{p.kill()}catch{}}});
