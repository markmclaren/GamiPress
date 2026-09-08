#!/bin/sh
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "Starting GamiPress demo stack..."
docker compose up -d db wordpress

echo "Waiting for WordPress to become healthy..."
docker compose up setup

echo ""
echo "Stack is up. Visit http://localhost:8080"
