#!/bin/sh
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "Tearing down GamiPress demo (volumes + orphans)..."
docker compose down -v --remove-orphans

echo "Done. All containers, networks, and volumes removed."
