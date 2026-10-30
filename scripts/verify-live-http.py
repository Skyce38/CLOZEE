"""Read-only/anonymous HTTP checks for the explicitly approved CLOZEE target.
Never loads credentials, browser cookies, password files or private response bodies.
"""
from urllib.request import Request, urlopen
from urllib.error import HTTPError
from http.cookies import SimpleCookie
from pathlib import Path
import argparse, json, datetime

parser=argparse.ArgumentParser()
parser.add_argument('--approved-target',required=True)
opt=parser.parse_args()
base=opt.approved_target.rstrip('/')
if base!='https://angsila.informatics.buu.ac.th/~68160207/clozee':
    raise SystemExit('This verifier is restricted to the explicitly approved target.')
results=[]
def request(path, method='HEAD', data=None):
    req=Request(base+path,method=method,data=data,headers={'User-Agent':'CLOZEE-approved-deployment-verifier/1.0'})
    try:
        with urlopen(req,timeout=15) as response:
            return response.status,response.headers,response.geturl()
    except HTTPError as response:
        return response.code,response.headers,response.geturl()
def check(name, ok):
    results.append({'check':name,'status':'PASS' if ok else 'FAIL'})
    print(results[-1]['status'],name,flush=True)

status,headers,final=request('/','GET')
check('Anonymous HTTPS catalog opens',status==200 and final.startswith(base))
check('MIME sniffing protection',headers.get('X-Content-Type-Options')=='nosniff')
check('Frame embedding denied',headers.get('X-Frame-Options')=='DENY')
check('CSP restricts script to same origin',"script-src 'self'" in headers.get('Content-Security-Policy',''))
check('Personal pages are not cached','no-store' in [v.strip().lower() for v in headers.get('Cache-Control','').split(',')])
cookie=SimpleCookie()
for value in headers.get_all('Set-Cookie',[]):cookie.load(value)
session=next(iter(cookie.values()),None)
check('Session cookie Secure HttpOnly SameSite=Lax',bool(session and session['secure'] and session['httponly'] and session['samesite'].lower()=='lax'))
status,_,_=request('/assets/css/app.css')
check('Public stylesheet reachable',status==200)
with urlopen(Request('https://angsila.informatics.buu.ac.th/~68160207/',method='HEAD'),timeout=15) as original:
    check('Existing Lab homepage still opens',original.status==200)
status,_,final=request('/seller/products.php','GET')
check('Anonymous seller page redirects to login',status==200 and final==base+'/login.php')
status,_,_=request('/logout.php')
check('Logout rejects HEAD instead of changing session',status==405)
status,_,_=request('/logout.php','POST',b'')
check('POST without CSRF rejected',status==403)
for path in ['/app/config/local.php','/app/config/db-password.txt','/database/angsila-schema.sql','/storage/','/.git/config','/../clozee-private/app/config/db-password.txt']:
    status,_,_=request(path)
    check('Private resource blocked: '+path,status in (403,404))
report={'executed_at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'target':base,
        'network_scope':'This local machine with university VPN; off-VPN reachability not established',
        'passed':sum(r['status']=='PASS' for r in results),'failed':sum(r['status']=='FAIL' for r in results),'results':results}
target=Path(__file__).resolve().parent.parent/'docs/evidence/angsila-http-results.json'
target.write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
raise SystemExit(1 if report['failed'] else 0)
