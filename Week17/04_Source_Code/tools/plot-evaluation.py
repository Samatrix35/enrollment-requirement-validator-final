from pathlib import Path
import os
os.environ.setdefault('MPLCONFIGDIR',str(Path(__file__).resolve().parent/'runtime-plot-cache'))
import json
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import numpy as np

import argparse
root=Path(__file__).resolve().parents[2]
parser=argparse.ArgumentParser(description='Plot completed Week 16 measurement records')
parser.add_argument('--results',type=Path,default=root/'03_Logs/performance')
parser.add_argument('--output',type=Path,default=root/'02_Evidence/2 Measured Results')
args=parser.parse_args()
runs=json.loads((args.results/'workload-summary.json').read_text())['results']
drill=json.loads((args.results/'rollback-recovery.json').read_text())
out=args.output;out.mkdir(parents=True,exist_ok=True)
plt.rcParams.update({'font.family':'DejaVu Sans','font.size':12,'axes.spines.top':False,'axes.spines.right':False,'axes.titleweight':'bold','axes.labelcolor':'#234236','text.color':'#19392D','axes.edgecolor':'#B7CFC1','grid.color':'#DCE9E2','figure.facecolor':'#F5F9F6','axes.facecolor':'white'})
labels=[('Dev' if r['environment']=='development' else 'TLS')+' '+('O' if r['profile']=='ordinary' else 'P')+str(r['repeat']) for r in runs]
x=np.arange(len(runs));colors=['#4276A6' if r['environment']=='development' else '#16866F' for r in runs]
def finish(fig,name):
 fig.text(.08,.025,'Dev = development HTTP; TLS = controlled cached HTTPS. O = ordinary (2 VUs), P = peak (8 VUs).\nSource: saved Week 16 workload and Windows process records; same-host single-worker prototype.',fontsize=8,color='#476B5A')
 fig.subplots_adjust(left=.09,right=.96,top=.87,bottom=.2)
 fig.savefig(out/name,dpi=180,bbox_inches='tight');plt.close(fig)

fig,ax=plt.subplots(figsize=(9.2,5.4));values=[r['latency_ms']['p95'] for r in runs]
bars=ax.bar(x,values,color=colors,width=.68)
for i,r in enumerate(runs):
 target=2000 if r['profile']=='ordinary' else 5000;ax.hlines(target,i-.38,i+.38,color='#B45033',linestyle='--',linewidth=2)
 ax.text(i,values[i]+110,f'{values[i]:.0f}',ha='center',fontsize=10)
ax.set_xticks(x,labels);ax.set_ylabel('Complete-response p95 latency (ms)');ax.set_ylim(0,max(values)*1.22);ax.grid(axis='y',alpha=.6);ax.set_axisbelow(True);ax.set_title('Application latency: 6 of 8 runs met the pre-set p95 goal',pad=22)
ax.text(.01,1.02,'Dashed marks: ordinary 2000 ms / peak 5000 ms targets',transform=ax.transAxes,fontsize=9,color='#9B412A');finish(fig,'01_client_p95_latency.png')

fig,ax=plt.subplots(figsize=(9.2,5.4));values=[r['requests_per_second'] for r in runs];ax.bar(x,values,color=colors,width=.68)
for i,r in enumerate(runs):
 target=1 if r['profile']=='ordinary' else 2;ax.hlines(target,i-.38,i+.38,color='#B45033',linestyle='--',linewidth=2);ax.text(i,values[i]+.045,f'{values[i]:.2f}',ha='center',fontsize=10)
ax.set_xticks(x,labels);ax.set_ylabel('Completed application requests per second');ax.set_ylim(0,2.6);ax.grid(axis='y',alpha=.6);ax.set_axisbelow(True);ax.set_title('Throughput: only 1 of 4 peak runs met the 2 requests/s goal',pad=22)
ax.text(.01,1.02,'Actual elapsed time includes drain; warmup and health probes excluded',transform=ax.transAxes,fontsize=10);finish(fig,'02_completed_throughput.png')

fig,(ax,ax2)=plt.subplots(2,1,figsize=(9.2,7.7),sharex=True)
cpu=[r['resources']['mean_one_core_cpu_percent'] for r in runs];rss=[r['resources']['sampled_peak_rss_mib'] for r in runs]
ax.bar(x,cpu,color=colors);ax.set_ylim(0,105);ax.set_ylabel('CPU: % of one core');ax.set_title('PHP backend resource use (Windows process samples)',pad=20)
ax2.bar(x,rss,color=colors);ax2.set_ylim(0,60);ax2.set_ylabel('Sampled peak RSS (MiB)');ax2.set_xticks(x,labels)
for a,vals in [(ax,cpu),(ax2,rss)]:
 a.grid(axis='y',alpha=.6);a.set_axisbelow(True)
 for i,v in enumerate(vals):a.text(i,v+1,f'{v:.1f}',ha='center',fontsize=10)
fig.text(.09,.04,'CPU is a per-process one-core equivalent, not total host utilization. RSS is working set, not PHP heap.\nOnly observed sample windows are summarized; raw TLS-proxy and private-byte samples are also retained.',fontsize=8,color='#476B5A')
fig.subplots_adjust(left=.1,right=.96,top=.9,bottom=.15,hspace=.28);fig.savefig(out/'03_backend_cpu_and_memory.png',dpi=180,bbox_inches='tight');plt.close(fig)

fig,ax=plt.subplots(figsize=(9.2,5.4));values=[r['health']['p95_ms'] for r in runs];ax.bar(x,values,color=colors)
for i,v in enumerate(values):ax.text(i,v+100,f'{v:.0f}',ha='center',fontsize=10)
ax.axhline(1000,color='#B45033',linestyle='--',linewidth=2,label='Readiness p95 goal: 1000 ms');ax.legend(frameon=False,loc='upper left');ax.set_xticks(x,labels);ax.set_ylabel('Readiness complete-response p95 (ms)');ax.set_ylim(0,max(values)*1.25);ax.set_title('Readiness responses stayed HTTP 200, but all 8 latency gates failed',pad=22);ax.grid(axis='y',alpha=.6);ax.set_axisbelow(True);finish(fig,'04_readiness_under_load.png')

fig,ax=plt.subplots(figsize=(9.2,4.7));actual=drill['recovery_seconds'];ax.barh([1,0],[actual,15],color=['#16866F','#C5D7CD'],height=.5);ax.set_yticks([1,0],['Observed rollback-to-ready','Pre-set recovery target']);ax.set_xlim(0,16.5);ax.set_xlabel('Seconds');ax.grid(axis='x',alpha=.5);ax.set_axisbelow(True);ax.set_title('Stopped-server configuration and SQLite checkpoint recovery',pad=20)
ax.text(actual+.18,1,f'{actual:.3f} s',va='center',fontweight='bold');ax.text(15.1,0,'15 s',va='center')
fig.text(.09,.035,'Candidate: live 200 / ready 503. Restored: release w16-r1, ready 200, authenticated checklist 200.\nCopy hash and selected record preserved. This is not proof of production RPO=0 or schema rollback.',fontsize=8,color='#476B5A')
fig.subplots_adjust(left=.25,right=.96,top=.82,bottom=.24);fig.savefig(out/'05_rollback_recovery.png',dpi=180,bbox_inches='tight');plt.close(fig)
print('Created five plots from actual Week 16 records')

