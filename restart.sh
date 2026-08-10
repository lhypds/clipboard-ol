#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

echo "==> Updating code"
git fetch --all --prune
CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
git pull --ff-only origin "$CURRENT_BRANCH"

echo "==> Running project setup"
if [[ -f "setup.sh" ]]; then
  bash "setup.sh"
fi

echo "==> Running build steps when project defines them"
if [[ -f "package.json" ]]; then
  if command -v npm >/dev/null 2>&1; then
    npm install
    if npm run | grep -q " build"; then
      npm run build
    fi
  else
    echo "npm is not installed; skipping Node.js build steps"
  fi
elif [[ -f "composer.json" ]]; then
  if command -v composer >/dev/null 2>&1; then
    composer install --no-interaction --prefer-dist
  else
    echo "composer is not installed; skipping PHP dependency install"
  fi
else
  echo "No build configuration found (package.json/composer.json)"
fi

echo "==> Restart completed"
