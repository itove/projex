#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p tmp-viewer-build
export PATH="${HOME}/.nvm/versions/node/v20.20.2/bin:${HOME}/.nvm/versions/node/v18.20.8/bin:${PATH}"
{
  echo "node=$(command -v node)"
  node -v
  npm -v
  echo "=== install ==="
  npm install --registry https://registry.npmmirror.com
  echo "=== build ==="
  npm run build:viewer
  echo "=== done ==="
  cp -f public/build/file-viewer/.vite/manifest.json tmp-viewer-build/manifest.json
} > tmp-viewer-build/build.log 2>&1
echo 0 > tmp-viewer-build/build.exit
node -e 'const m=require("./tmp-viewer-build/manifest.json"); const e=m["frontend/file-viewer/main.js"]; console.log("main="+ (e&&e.file));' > tmp-viewer-build/SUMMARY.txt
