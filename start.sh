#!/bin/sh
# start.sh — Full GamiPress demo lifecycle helper
#
# Usage:
#   ./start.sh          — fresh start (down -v, then up)
#   ./start.sh up       — bring up only (skip tear-down)
#   ./start.sh down     — tear down only
#   ./start.sh restart  — stop (keep volumes), then up
#   ./start.sh logs     — tail all container logs
#   ./start.sh status   — show container status
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# ── Helpers ──────────────────────────────────────────────────────────────────

check_env() {
  if [ ! -f .env ]; then
    if [ -f .env.example ]; then
      echo "No .env found — copying from .env.example"
      cp .env.example .env
    else
      echo "ERROR: .env file not found. Create one before starting." >&2
      exit 1
    fi
  fi
}

do_down() {
  echo "==> Tearing down (volumes + orphans)..."
  docker compose down -v --remove-orphans
}

do_stop() {
  echo "==> Stopping containers (volumes preserved)..."
  docker compose stop
}

do_up() {
  echo "==> Starting db and wordpress..."
  docker compose up -d db wordpress

  echo "==> Running setup (watching logs)..."
  docker compose up setup

  echo ""
  echo "=============================================="
  echo "  GamiPress Demo is live!"
  echo "  http://localhost:8080"
  echo "  http://localhost:8080/wp-admin"
  echo "=============================================="
}

do_logs() {
  docker compose logs -f --tail=50
}

do_status() {
  docker compose ps
}

# ── Command dispatch ─────────────────────────────────────────────────────────

CMD="${1:-fresh}"

case "$CMD" in
  fresh | "")
    check_env
    do_down
    do_up
    ;;
  up)
    check_env
    do_up
    ;;
  down)
    do_down
    ;;
  restart)
    check_env
    do_stop
    do_up
    ;;
  logs)
    do_logs
    ;;
  status)
    do_status
    ;;
  *)
    echo "Usage: $0 [fresh|up|down|restart|logs|status]"
    echo ""
    echo "  fresh    — tear down (incl. volumes) then start fresh  (default)"
    echo "  up       — start stack without wiping data"
    echo "  down     — stop and remove containers + volumes"
    echo "  restart  — stop (keep data) then bring back up"
    echo "  logs     — tail all container logs"
    echo "  status   — show container status"
    exit 1
    ;;
esac
