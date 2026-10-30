"""Package committed source only. Excludes all private local files by construction."""
from pathlib import Path
import subprocess, zipfile, hashlib, json

root=Path(__file__).resolve().parent.parent
def git(*args): return subprocess.check_output(['git',*args],cwd=root)
if git('status','--porcelain').strip():
    raise SystemExit('Commit reviewed changes before packaging. No package written.')
sha=git('rev-parse','HEAD').decode().strip()
paths=[p for p in git('ls-files','-z').decode().split('\0') if p]
forbidden=['app/config/local.php','storage/demo-accounts.json']
assert not any(p in forbidden or p.startswith('node_modules/') or p.endswith('.env') for p in paths)
out=root/'output';out.mkdir(exist_ok=True)
bundles={
    'source':paths,
    'angsila-package':[p for p in paths if p.startswith(('app/','public/','database/')) or p in ['scripts/doctor.php','scripts/migrate.php','scripts/seed.php','docs/deployment.md','docs/deployment-verification.md','README.md']]
}
manifest={'commit':sha,'deployment_status':'PREPARED ONLY - no target approved, no live access','packages':[]}
for kind,files in bundles.items():
    target=out/f'CLOZEE-round2-{sha[:7]}-{kind}.zip'
    with zipfile.ZipFile(target,'w',zipfile.ZIP_DEFLATED) as z:
        for rel in files:z.write(root/rel,'CLOZEE/'+rel)
        z.writestr('CLOZEE/PACKAGE-NOTES.txt',f'Commit: {sha}\nDeploy status: NOT DEPLOYED.\nOnly public/ may be web-visible. Follow docs/deployment.md before upload.\nPrivate config, generated passwords, .git and runtime logs are excluded.\n')
    manifest['packages'].append({'file':target.name,'files':len(files)+1,'sha256':hashlib.sha256(target.read_bytes()).hexdigest()})
    print(target)
(out/'package-manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
