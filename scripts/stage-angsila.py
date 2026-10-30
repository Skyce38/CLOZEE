"""Prepare a split public/private upload tree; performs no network operations.

Only Git-tracked app/public files are copied, never local credentials or storage.
The generated public entry points refer to the approved private absolute path.
"""
from pathlib import Path
import argparse, json, re, shutil, subprocess

root = Path(__file__).resolve().parent.parent
args = argparse.ArgumentParser()
args.add_argument('--private-root', required=True)
args.add_argument('--output', required=True)
opt = args.parse_args()
private = opt.private_root.replace('\\', '/')
if not (private.startswith('/') or re.match(r'^[A-Za-z]:/', private)) or any(c in private for c in "'\r\n\x00"):
    raise SystemExit('Use a safe absolute private directory path.')
out = Path(opt.output).resolve()
if out == root or root in out.parents and not out.is_relative_to(root/'output'):
    raise SystemExit('Inside this repository, staging is allowed only under output/.')
if out.exists():
    raise SystemExit('Output already exists; use a new directory. Nothing overwritten.')
tracked = subprocess.check_output(['git','ls-files','-z'],cwd=root).decode().split('\0')
revision = subprocess.check_output(['git','rev-parse','HEAD'],cwd=root).decode().strip()
entry_count = 0
for name in tracked:
    if not name.startswith(('app/','public/')) or name == 'app/config/local.php':
        continue
    source = root/name
    target = out/'web'/name.removeprefix('public/') if name.startswith('public/') else out/'private'/name
    target.parent.mkdir(parents=True,exist_ok=True)
    if name.startswith('public/') and name.endswith('.php'):
        text = source.read_text(encoding='utf-8')
        text, n = re.subn(r"require __DIR__\.'/\.\./(?:\.\./)?app/bootstrap\.php';", "require '"+private+"/app/bootstrap.php';", text)
        if n != 1:
            raise SystemExit('Unexpected bootstrap entry point: '+name)
        target.write_text(text,encoding='utf-8')
        entry_count += 1
    else:
        shutil.copyfile(source,target)
(out/'private'/'storage').mkdir(exist_ok=True)
manifest = {'source_commit':revision,'private_root':private,'php_entry_points':entry_count,'contains_credentials':False,
            'web_upload':'Upload web/ contents to the approved public_html/clozee directory only.',
            'private_upload':'Upload private/ contents to private_root outside public_html.',
            'configuration':'Create app/config/local.php privately; never upload the local development config.',
            'database':'Import reviewed database/angsila-schema.sql separately after approval; no production demo seed.'}
(out/'STAGING-PLAN.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
print(json.dumps(manifest,indent=2))
