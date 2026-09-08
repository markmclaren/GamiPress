#!/bin/sh
# cheat.sh — Quick-win helper for the GamiPress demo
#
# Usage (demo must be running — ./start.sh):
#   ./cheat.sh credits          — award 100 Credits to the demo user
#   ./cheat.sh badge            — award the "Welcome Aboard" badge
#   ./cheat.sh badge <slug>     — award a specific badge by slug
#   ./cheat.sh rank <slug>      — promote demo user to a specific rank
#   ./cheat.sh list badges      — list all badge slugs
#   ./cheat.sh list ranks       — list all rank slugs
set -e

WP="docker compose run --rm -T --entrypoint wp setup --path=/var/www/html --allow-root"
DEMO_USER=demo

# Verify the stack is up
if ! docker inspect gamipress_wp >/dev/null 2>&1; then
  echo "ERROR: gamipress_wp container is not running. Start it first with: ./start.sh up"
  exit 1
fi

# Resolve demo user ID (create if missing)
DEMO_ID=$($WP user get "$DEMO_USER" --field=ID 2>/dev/null || true)
if [ -z "$DEMO_ID" ]; then
  $WP user create "$DEMO_USER" demo@example.com --role=subscriber --user_pass=demo123 >/dev/null 2>&1 || true
  DEMO_ID=$($WP user get "$DEMO_USER" --field=ID 2>/dev/null || true)
fi

CMD="${1:-help}"
ARG="${2:-}"

case "$CMD" in

  credits)
    AMOUNT="${ARG:-100}"
    $WP eval "
      \$user_id = ${DEMO_ID}; \$amount = ${AMOUNT};
      gamipress_award_points_to_user(\$user_id, \$amount, 'credits');
      \$pt_id = gamipress_get_points_type_id('credits');
      gamipress_insert_user_earning(\$user_id, array(
        'title'       => sprintf('+%d Credits', \$amount),
        'post_id'     => \$pt_id ? \$pt_id : 0,
        'post_type'   => 'points-type',
        'points'      => \$amount,
        'points_type' => 'credits',
        'date'        => date('Y-m-d H:i:s', current_time('timestamp')),
      ));
    " >/dev/null
    echo "✅  Awarded ${AMOUNT} Credits to '$DEMO_USER' (user ID ${DEMO_ID})."
    echo "    View balance → http://localhost:8080/my-points/"
    ;;

  gems)
    AMOUNT="${ARG:-10}"
    $WP eval "
      \$user_id = ${DEMO_ID}; \$amount = ${AMOUNT};
      gamipress_award_points_to_user(\$user_id, \$amount, 'gems');
      \$pt_id = gamipress_get_points_type_id('gems');
      gamipress_insert_user_earning(\$user_id, array(
        'title'       => sprintf('+%d Gems', \$amount),
        'post_id'     => \$pt_id ? \$pt_id : 0,
        'post_type'   => 'points-type',
        'points'      => \$amount,
        'points_type' => 'gems',
        'date'        => date('Y-m-d H:i:s', current_time('timestamp')),
      ));
    " >/dev/null
    echo "✅  Awarded ${AMOUNT} Gems to '$DEMO_USER' (user ID ${DEMO_ID})."
    ;;

  coins)
    AMOUNT="${ARG:-100}"
    $WP eval "
      \$user_id = ${DEMO_ID}; \$amount = ${AMOUNT};
      gamipress_award_points_to_user(\$user_id, \$amount, 'coins');
      \$pt_id = gamipress_get_points_type_id('coins');
      gamipress_insert_user_earning(\$user_id, array(
        'title'       => sprintf('+%d Coins', \$amount),
        'post_id'     => \$pt_id ? \$pt_id : 0,
        'post_type'   => 'points-type',
        'points'      => \$amount,
        'points_type' => 'coins',
        'date'        => date('Y-m-d H:i:s', current_time('timestamp')),
      ));
    " >/dev/null
    echo "✅  Awarded ${AMOUNT} Coins to '$DEMO_USER' (user ID ${DEMO_ID})."
    ;;

  badge)
    SLUG="${ARG:-badge-welcome}"
    BADGE_ID=$($WP post list --post_type=badges --name="$SLUG" --field=ID --format=ids 2>/dev/null | head -1)
    if [ -z "$BADGE_ID" ]; then
      echo "ERROR: Badge '$SLUG' not found. Run './cheat.sh list badges' to see available slugs."
      exit 1
    fi
    BADGE_TITLE=$($WP post get "$BADGE_ID" --field=post_title 2>/dev/null)
    $WP eval "gamipress_award_achievement_to_user(${BADGE_ID}, ${DEMO_ID});" >/dev/null
    echo "✅  Awarded badge '${BADGE_TITLE}' to '$DEMO_USER'."
    echo "    View badges → http://localhost:8080/achievements/"
    ;;

  rank)
    SLUG="${ARG:-rank-explorer}"
    RANK_ID=$($WP post list --post_type=levels --name="$SLUG" --field=ID --format=ids 2>/dev/null | head -1)
    if [ -z "$RANK_ID" ]; then
      echo "ERROR: Rank '$SLUG' not found. Run './cheat.sh list ranks' to see available slugs."
      exit 1
    fi
    RANK_TITLE=$($WP post get "$RANK_ID" --field=post_title 2>/dev/null)
    $WP eval "gamipress_award_rank_to_user(${RANK_ID}, ${DEMO_ID});" >/dev/null
    echo "✅  Promoted '$DEMO_USER' to rank '${RANK_TITLE}'."
    echo "    View rank → http://localhost:8080/ranks/"
    ;;

  reset)
    $WP eval "
      \$user_id = ${DEMO_ID}; global \$wpdb;
      \$wpdb->query(\$wpdb->prepare(\"DELETE FROM {\$wpdb->usermeta} WHERE user_id = %d AND (meta_key LIKE '_gamipress_%%' OR meta_key LIKE 'gamipress_%%')\", \$user_id));
      \$earning_ids = \$wpdb->get_col(\$wpdb->prepare(\"SELECT user_earning_id FROM {\$wpdb->prefix}gamipress_user_earnings WHERE user_id = %d\", \$user_id));
      if (!empty(\$earning_ids)) {
        \$ids_str = implode(',', array_map('absint', \$earning_ids));
        \$wpdb->query(\"DELETE FROM {\$wpdb->prefix}gamipress_user_earnings_meta WHERE user_earning_id IN (\$ids_str)\");
        \$wpdb->query(\$wpdb->prepare(\"DELETE FROM {\$wpdb->prefix}gamipress_user_earnings WHERE user_id = %d\", \$user_id));
      }
      \$log_ids = \$wpdb->get_col(\$wpdb->prepare(\"SELECT log_id FROM {\$wpdb->prefix}gamipress_logs WHERE user_id = %d\", \$user_id));
      if (!empty(\$log_ids)) {
        \$ids_str = implode(',', array_map('absint', \$log_ids));
        \$wpdb->query(\"DELETE FROM {\$wpdb->prefix}gamipress_logs_meta WHERE log_id IN (\$ids_str)\");
        \$wpdb->query(\$wpdb->prepare(\"DELETE FROM {\$wpdb->prefix}gamipress_logs WHERE user_id = %d\", \$user_id));
      }
      if (function_exists('gamipress_get_lowest_priority_rank_id')) {
        \$lowest = gamipress_get_lowest_priority_rank_id('levels');
        if (\$lowest) gamipress_update_user_rank(\$user_id, \$lowest);
      }
    " >/dev/null
    echo "🔄  Reset all data for '$DEMO_USER' (0 Credits, locked badges, starting rank, cleared logs)."
    ;;

  list)
    case "$ARG" in
      badges)
        echo "Available badges (use slug with './cheat.sh badge <slug>'):"
        $WP post list --post_type=badges --fields=post_name,post_title --format=table 2>/dev/null
        ;;
      ranks)
        echo "Available ranks (use slug with './cheat.sh rank <slug>'):"
        $WP post list --post_type=levels --fields=post_name,post_title --format=table 2>/dev/null
        ;;
      *)
        echo "Usage: ./cheat.sh list [badges|ranks]"
        ;;
    esac
    ;;

  help|*)
    echo ""
    echo "GamiPress Demo — Quick-win cheat sheet"
    echo "======================================="
    echo ""
    echo "  ./cheat.sh credits [amount]   Award Credits to the demo user (default: 100)"
    echo "  ./cheat.sh badge [slug]       Award a badge        (default: badge-welcome)"
    echo "  ./cheat.sh rank [slug]        Promote to a rank    (default: rank-explorer)"
    echo "  ./cheat.sh list badges        List all badge slugs"
    echo "  ./cheat.sh list ranks         List all rank slugs"
    echo ""
    echo "Badge slugs:   badge-welcome  badge-conversationalist  badge-regular-visitor"
    echo "               badge-author   badge-high-roller"
    echo ""
    echo "Rank slugs:    rank-newcomer  rank-explorer  rank-contributor  rank-champion"
    echo ""
    echo "Examples:"
    echo "  ./cheat.sh credits 500                 # jump straight to Champion territory"
    echo "  ./cheat.sh badge badge-high-roller     # award High Roller badge"
    echo "  ./cheat.sh rank rank-champion          # promote to Champion"
    echo ""
    ;;
esac
