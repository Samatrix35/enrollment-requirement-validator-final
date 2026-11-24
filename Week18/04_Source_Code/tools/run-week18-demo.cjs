const fs=require('fs'),path=require('path'),http=require('http');
const {spawn,execFileSync}=require('child_process');
const {chromium}=require('playwright');
if(!process.argv.includes('--reset-demo'))throw Error('Run only on disposable fictional demo data; --reset-demo is required.');
const source=path.resolve(__dirname,'..'),root=path.resolve(source,'..'),api=path.join(source,'enrollment_validator_api');
const logs=path.join(root,'03_Logs'),evidence=path.join(root,'02_Evidence/1 Final Demonstration');
fs.mkdirSync(evidence,{recursive:true});
execFileSync('php',['artisan','migrate:fresh','--seed','--force','--no-ansi'],{cwd:api,windowsHide:true,stdio:'inherit'});
execFileSync('php',['artisan','cache:clear'],{cwd:api,windowsHide:true,stdio:'inherit'});
let backend,browser,page;
function start(broken=false){backend=spawn('php',['-S','127.0.0.1:8046','-t','public','public/index.php'],{cwd:api,windowsHide:true,env:{...process.env,APP_ENV:'local',APP_RELEASE:'w18-final',FRONTEND_URLS:'http://127.0.0.1:5046',...(broken?{DB_DATABASE:path.join(api,'database/missing-week18.sqlite')}:{})},stdio:'ignore'})}
const delay=ms=>new Promise(r=>setTimeout(r,ms));
async function stop(){backend?.kill();await delay(400)}
const web=http.createServer((q,s)=>{let url;try{url=decodeURIComponent(q.url.split('?')[0])}catch{s.writeHead(400);s.end();return}const webRoot=path.join(source,'build/web');let f=path.resolve(webRoot,'.'+(url==='/'?'/index.html':url));if(!f.startsWith(webRoot+path.sep)){s.writeHead(403);s.end();return}s.setHeader('Content-Type',({'.html':'text/html','.js':'application/javascript','.json':'application/json','.wasm':'application/wasm','.png':'image/png','.ttf':'font/ttf'})[path.extname(f)]||'application/octet-stream');fs.createReadStream(f).on('error',()=>{s.statusCode=404;s.end()}).pipe(s)});
const captures=[],checks=[],errors=[],responses=[];const recovery={scope:'Configuration failure/recovery on fictional local DB; no schema rollback or production RTO claim'};async function signal(endpoint){const r=await fetch('http://127.0.0.1:8046/api/health/'+endpoint);return {status:r.status,body:await r.json(),captured_at:new Date().toISOString()}}
function record(id,observation){checks.push({id,passed:true,type:'Developer-run final demonstration rehearsal',observation,timestamp:new Date().toISOString()})}
(async()=>{
 start();await new Promise(r=>web.listen(5046,'127.0.0.1',r));await delay(1000);
 browser=await chromium.launch({executablePath:process.env.CHROME_BIN||'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 page=await browser.newPage({viewport:{width:1366,height:940}});page.on('pageerror',e=>errors.push(e.message));
 page.on('response',r=>{if(r.url().includes(':8046/api/'))responses.push({method:r.request().method(),path:new URL(r.url()).pathname,status:r.status()})});
 async function enter(locator,value){await locator.click();await locator.press('ControlOrMeta+A');await locator.pressSequentially(value,{delay:10});await locator.press('Tab')}
 async function shot(file,caption){await delay(300);await page.screenshot({path:path.join(evidence,file)});console.log('Captured '+file);captures.push({file:'1 Final Demonstration/'+file,caption,origin:'Actual compiled Flutter Web in Chrome',timestamp:new Date().toISOString()})}
 async function login(role){await enter(page.getByRole('textbox').nth(0),role+'.demo@example.test');await enter(page.getByRole('textbox').nth(1),role==='staff'?'StaffDemo-2026!':'StudentDemo-2026!');await page.getByRole('button',{name:'Sign in',exact:true}).click();await page.getByRole('button',{name:role==='staff'?'Refresh queue':'Refresh status',exact:true}).waitFor()}
 const logout=()=>page.getByRole('button',{name:'Sign out',exact:true}).click();
 await page.goto('http://127.0.0.1:5046');await delay(4000);await page.locator('flt-semantics-placeholder').dispatchEvent('click');
 await page.getByRole('button',{name:'Sign in',exact:true}).click();await page.getByText('Enter your password.',{exact:true}).waitFor();
 await shot('01_blank_login_validation.png','AC05: required login fields show visible validation before any login request.');record('AC05','Blank form gives email/password feedback; protected screen is not shown.');
 await login('student');await page.getByText('0 of 3 requirements approved',{exact:true}).waitFor();await shot('02_student_pending_checklist.png','AC01: fictional student sees their own Pending checklist.');record('AC01','Student identity and three Pending checklist items visible.');
 await page.getByRole('button',{name:'Submit for review',exact:true}).first().click();await page.getByLabel('Submitted',{exact:true}).waitFor();await shot('03_submitted_for_staff_review.png','AC02: submitted is awaiting staff, not approved.');record('AC02','Submission transitions to Submitted and approved count remains zero.');
 await logout();await login('staff');await page.getByLabel(/Birth Certificate/).waitFor();await shot('04_staff_submitted_queue.png','AC02: authorized staff sees the student submission.');
 await page.getByRole('button',{name:'Request correction',exact:true}).first().click();await page.getByRole('button',{name:'Send note',exact:true}).click();await page.getByText('Enter a correction note.',{exact:true}).first().waitFor();
 await shot('05_empty_correction_feedback.png','AC07/D01: fixed dialog explains an empty correction note; no decision is sent.');
 await enter(page.getByRole('textbox').first(),'Please check the fictional reference before resubmitting.');await page.getByRole('button',{name:'Send note',exact:true}).click();await page.getByText('No submissions need review.',{exact:true}).waitFor();record('AC07','Blank note shows actionable validation; valid note returns requirement.');
 await logout();await login('student');await page.getByLabel('Correction needed',{exact:true}).waitFor();await page.getByLabel(/Correction note: Please check the fictional reference before resubmitting./).or(page.getByText(/Correction note: Please check the fictional reference before resubmitting./)).first().waitFor();
 await shot('06_student_correction_note.png','AC03: student receives the exact staff correction note.');record('AC03','Returned item and correction text appear on student checklist.');
 await page.getByRole('button',{name:'Resubmit',exact:true}).click();await page.getByLabel('Submitted',{exact:true}).waitFor();await shot('07_student_resubmitted.png','AC04: resubmission clears the obsolete correction feedback.');
 if(await page.getByLabel(/Correction note: Please check the fictional reference before resubmitting./).or(page.getByText(/Correction note: Please check the fictional reference before resubmitting./)).first().count())throw Error('Stale correction note remained');
 await logout();await login('staff');await page.getByRole('button',{name:'Approve',exact:true}).first().click();await page.getByText('No submissions need review.',{exact:true}).waitFor();await shot('08_staff_review_completed.png','AC04: staff approval removes the item from the submitted queue.');
 await page.getByRole('button',{name:'Check service health',exact:true}).click();await page.getByText(/Status: ready/).waitFor();await shot('09_service_ready.png','AC09: Laravel reports database/schema ready with release w18-final.');await page.getByRole('button',{name:'Close',exact:true}).click();
 recovery.before=await signal('ready');await stop();start(true);await delay(800);recovery.failed_live=await signal('live');recovery.failed_ready=await signal('ready');if(recovery.failed_live.status!==200||recovery.failed_ready.status!==503)throw Error('Expected live200/ready503 during dependency failure');await page.getByRole('button',{name:'Check service health',exact:true}).click();await page.getByText('The service is not ready. Contact the operator or retry after recovery.',{exact:true}).waitFor();await shot('10_safe_dependency_failure.png','AC09: real missing database produces HTTP503 and a safe UI message.');await page.getByRole('button',{name:'Close',exact:true}).click();
 recovery.restore_started_at=new Date().toISOString();const recoveryStart=performance.now();await stop();start();await delay(800);await page.getByRole('button',{name:'Check service health',exact:true}).click();await page.getByText(/Status: ready/).waitFor();recovery.restored_ready=await signal('ready');recovery.configuration_restore_to_observed_ready_ms=+(performance.now()-recoveryStart).toFixed(1);if(recovery.restored_ready.status!==200)throw Error('Recovery failed');await shot('11_service_recovered.png','AC09: restoring valid local configuration restores readiness.');await page.getByRole('button',{name:'Close',exact:true}).click();record('AC09','Actual readiness200 ->503 ->200 after controlled DB config failure/recovery.');
 await logout();await login('student');await page.getByText('1 of 3 requirements approved',{exact:true}).waitFor();await shot('12_student_approved_result.png','AC04: approved count increases to1/3; other items remain Pending.');record('AC04','Resubmit/approve loop preserves staff authority and updates student result.');recovery.business_state_after_recovery='Student UI confirmed 1 of 3 requirements approved after service restoration';recovery.pass=true;
 await page.setViewportSize({width:390,height:844});await shot('13_narrow_student_view.png','AC08: actual390x844 browser rendering; human accessibility/UAT evaluation pending.');record('AC08','Student result renders at narrow viewport; no full accessibility certification inferred.');
 await logout();await page.getByRole('button',{name:'Sign in',exact:true}).waitFor();record('AC10','Sign-out returns to login; token revocation separately verified by API tests.');
 if(errors.length)throw Error('Browser JavaScript errors: '+errors.join(';'));
 console.log(`${checks.length} developer browser checks; ${captures.length} real screenshots; zero JavaScript errors.`);
})().catch(async e=>{console.error(e);if(page){console.log(await page.locator('body').innerText());fs.writeFileSync(path.join(logs,'browser-failure-dom.html'),await page.content())}if(page)await page.screenshot({path:path.join(logs,'browser-failure.png')});process.exitCode=1}).finally(async()=>{
 fs.writeFileSync(path.join(logs,'final-demo.json'),JSON.stringify({captured_at:new Date().toISOString(),release:'w18-final',scope:'Week18 developer demonstration; stakeholder acceptance remains pending',checks,captures,api_responses:responses,page_errors:errors},null,2));
 fs.writeFileSync(path.join(logs,'failure-recovery-demo.json'),JSON.stringify(recovery,null,2));await browser?.close();backend?.kill();web.close();
});




