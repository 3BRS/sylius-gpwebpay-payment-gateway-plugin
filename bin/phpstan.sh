#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# project root
cd "$(dirname "$DIR")"

set -x
tests/Application/bin/console --env=test cache:warmup --no-optional-warmers
vendor/bin/phpstan analyse
