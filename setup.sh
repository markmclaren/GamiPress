#!/bin/sh
set -e

WP="wp --path=/var/www/html --allow-root"

until [ -f /var/www/html/wp-config.php ]; do printf '.'; sleep 2; done
printf '\n'

$WP core install \
  --url="${WP_SITE_URL:-http://localhost:8080}" \
  --title="${WP_SITE_TITLE:-GamiPress Demo}" \
  --admin_user="${WP_ADMIN_USER:-admin}" \
  --admin_password="${WP_ADMIN_PASSWORD:-admin123}" \
  --admin_email="${WP_ADMIN_EMAIL:-admin@example.com}" \
  --skip-email >/dev/null 2>&1 || true

$WP plugin activate gamipress >/dev/null 2>&1
$WP option update gamipress_version "2.4.0" >/dev/null 2>&1
$WP option update gamipress_db_version "2.4.0" >/dev/null 2>&1
$WP eval "gamipress_register_custom_tables(); foreach(['gamipress_user_earnings', 'gamipress_user_earnings_meta', 'gamipress_logs', 'gamipress_logs_meta'] as \$n) { \$t = ct_setup_table(\$n); if(\$t && isset(\$t->db)) \$t->db->maybe_upgrade(); }" >/dev/null 2>&1 || true

# Remove sample page if present
SAMPLE_PAGE_ID=$($WP post list --post_type=page --name=sample-page --field=ID --format=ids 2>/dev/null | head -1)
if [ -n "$SAMPLE_PAGE_ID" ]; then
  $WP post delete "$SAMPLE_PAGE_ID" --force >/dev/null 2>&1 || true
fi

# ── Helper: set post meta ────────────────────────────────────────────────────
set_meta() {
  POST_ID="$1"; KEY="$2"; VALUE="$3"
  $WP post meta update "$POST_ID" "$KEY" "$VALUE" >/dev/null 2>&1
}

# ── Helper: get the ID of an existing post by type+slug ──────────────────────
get_id() {
  $WP post list --post_type="$1" --name="$2" --field=ID --format=ids 2>/dev/null | head -1
}

# ── Helper: create a post only if it doesn't already exist ───────────────────
create_post() {
  TYPE="$1"; TITLE="$2"; SLUG="$3"; PARENT="${4:-0}"
  EXISTING=$(get_id "$TYPE" "$SLUG")
  if [ -n "$EXISTING" ]; then
    echo "$EXISTING"
  else
    $WP post create \
      --post_type="$TYPE" --post_title="$TITLE" \
      --post_status=publish --post_name="$SLUG" \
      --post_parent="$PARENT" --porcelain 2>/dev/null
  fi
}

# ══════════════════════════════════════════════════════════════════════════════
# POINTS TYPES: Credits, Gems, Coins
# ══════════════════════════════════════════════════════════════════════════════
PT_ID=$(create_post points-type "Credits" credits)
PT_GEMS=$(create_post points-type "Gems" gems)
PT_COINS=$(create_post points-type "Coins" coins)

# Points award rules for Credits
AWARD_ID=$(create_post points-award "Login Award" credits-login "$PT_ID")
set_meta "$AWARD_ID" _gamipress_trigger_type  gamipress_login
set_meta "$AWARD_ID" _gamipress_points        5
set_meta "$AWARD_ID" _gamipress_points_type   credits
set_meta "$AWARD_ID" _gamipress_maximum_earnings 0
set_meta "$AWARD_ID" _gamipress_count         1

AWARD_ID=$(create_post points-award "Comment Award" credits-comment "$PT_ID")
set_meta "$AWARD_ID" _gamipress_trigger_type  gamipress_new_comment
set_meta "$AWARD_ID" _gamipress_points        10
set_meta "$AWARD_ID" _gamipress_points_type   credits
set_meta "$AWARD_ID" _gamipress_maximum_earnings 0
set_meta "$AWARD_ID" _gamipress_count         1

AWARD_ID=$(create_post points-award "Daily Visit Award" credits-daily-visit "$PT_ID")
set_meta "$AWARD_ID" _gamipress_trigger_type  gamipress_site_visit
set_meta "$AWARD_ID" _gamipress_points        20
set_meta "$AWARD_ID" _gamipress_points_type   credits
set_meta "$AWARD_ID" _gamipress_maximum_earnings 0
set_meta "$AWARD_ID" _gamipress_count         1

AWARD_ID=$(create_post points-award "Publish Post Award" credits-publish-post "$PT_ID")
set_meta "$AWARD_ID" _gamipress_trigger_type  gamipress_publish_post
set_meta "$AWARD_ID" _gamipress_points        50
set_meta "$AWARD_ID" _gamipress_points_type   credits
set_meta "$AWARD_ID" _gamipress_maximum_earnings 0
set_meta "$AWARD_ID" _gamipress_count         1

# Points award rule for Gems (1 Gem per post published)
AWARD_GEM=$(create_post points-award "Publish Post Gem Award" gems-publish-post "$PT_GEMS")
set_meta "$AWARD_GEM" _gamipress_trigger_type  gamipress_publish_post
set_meta "$AWARD_GEM" _gamipress_points        1
set_meta "$AWARD_GEM" _gamipress_points_type   gems
set_meta "$AWARD_GEM" _gamipress_maximum_earnings 0
set_meta "$AWARD_GEM" _gamipress_count         1

# Points award rule for Coins (100 Coins on login)
AWARD_COIN=$(create_post points-award "Login Coins Award" coins-login "$PT_COINS")
set_meta "$AWARD_COIN" _gamipress_trigger_type  gamipress_login
set_meta "$AWARD_COIN" _gamipress_points        100
set_meta "$AWARD_COIN" _gamipress_points_type   coins
set_meta "$AWARD_COIN" _gamipress_maximum_earnings 0
set_meta "$AWARD_COIN" _gamipress_count         1

# ══════════════════════════════════════════════════════════════════════════════
# ACHIEVEMENT TYPE: Badges
# Each badge is a post of type "badges"; requirements are "step" child posts.
# ══════════════════════════════════════════════════════════════════════════════
$WP post create --post_type=achievement-type --post_title="Badges" \
  --post_status=publish --post_name=badges --porcelain >/dev/null 2>&1 || true

# Badge 1 — Welcome Aboard (log in once)
B1=$(create_post badges "Welcome Aboard" badge-welcome)
set_meta "$B1" _gamipress_earned_by triggers
STEP=$(create_post step "Log in once" badge-welcome-step1 "$B1")
set_meta "$STEP" _gamipress_trigger_type gamipress_login
set_meta "$STEP" _gamipress_count        1

# Badge 2 — Conversationalist (leave 3 comments)
B2=$(create_post badges "Conversationalist" badge-conversationalist)
set_meta "$B2" _gamipress_earned_by triggers
STEP=$(create_post step "Leave 3 comments" badge-conv-step1 "$B2")
set_meta "$STEP" _gamipress_trigger_type gamipress_new_comment
set_meta "$STEP" _gamipress_count        3

# Badge 3 — Regular Visitor (visit the site 5 days)
B3=$(create_post badges "Regular Visitor" badge-regular-visitor)
set_meta "$B3" _gamipress_earned_by triggers
STEP=$(create_post step "Visit 5 days" badge-visit-step1 "$B3")
set_meta "$STEP" _gamipress_trigger_type gamipress_site_visit
set_meta "$STEP" _gamipress_count        5

# Badge 4 — Author (publish your first post)
B4=$(create_post badges "Author" badge-author)
set_meta "$B4" _gamipress_earned_by triggers
STEP=$(create_post step "Publish a post" badge-author-step1 "$B4")
set_meta "$STEP" _gamipress_trigger_type gamipress_publish_post
set_meta "$STEP" _gamipress_count        1

# Badge 5 — High Roller (earn 100 Credits)
B5=$(create_post badges "High Roller" badge-high-roller)
set_meta "$B5" _gamipress_earned_by points
set_meta "$B5" _gamipress_points_required    100
set_meta "$B5" _gamipress_points_type_required credits

# ══════════════════════════════════════════════════════════════════════════════
# RANK TYPE: Levels
# Each rank is a post of type "levels"; requirements are "rank-requirement" posts.
# ══════════════════════════════════════════════════════════════════════════════
$WP post create --post_type=rank-type --post_title="Levels" \
  --post_status=publish --post_name=levels --porcelain >/dev/null 2>&1 || true

# Level 1 — Newcomer (no requirements — starting rank)
R1=$(create_post levels "Newcomer" rank-newcomer)
set_meta "$R1" _gamipress_points_to_unlock  0
set_meta "$R1" _gamipress_points_type_to_unlock credits

# Level 2 — Explorer (earn 50 Credits)
R2=$(create_post levels "Explorer" rank-explorer)
set_meta "$R2" _gamipress_points_to_unlock  50
set_meta "$R2" _gamipress_points_type_to_unlock credits
REQ=$(create_post rank-requirement "Earn 50 Credits" rank-explorer-req1 "$R2")
set_meta "$REQ" _gamipress_trigger_type  gamipress_login
set_meta "$REQ" _gamipress_count         1

# Level 3 — Contributor (earn 200 Credits)
R3=$(create_post levels "Contributor" rank-contributor)
set_meta "$R3" _gamipress_points_to_unlock  200
set_meta "$R3" _gamipress_points_type_to_unlock credits
REQ=$(create_post rank-requirement "Earn 200 Credits" rank-contrib-req1 "$R3")
set_meta "$REQ" _gamipress_trigger_type  gamipress_new_comment
set_meta "$REQ" _gamipress_count         5

# Level 4 — Champion (earn 500 Credits)
R4=$(create_post levels "Champion" rank-champion)
set_meta "$R4" _gamipress_points_to_unlock  500
set_meta "$R4" _gamipress_points_type_to_unlock credits
REQ=$(create_post rank-requirement "Earn 500 Credits" rank-champ-req1 "$R4")
set_meta "$REQ" _gamipress_trigger_type  gamipress_site_visit
set_meta "$REQ" _gamipress_count         10

# ── Demo subscriber ───────────────────────────────────────────────────────────
$WP user create demo demo@example.com --role=subscriber --user_pass=demo123 >/dev/null 2>&1 || true

# ── Helper: create a page if it doesn't already exist ───────────────────────
create_page() {
  SLUG="$1"; TITLE="$2"; CONTENT="$3"
  if $WP post list --post_type=page --name="$SLUG" --field=ID --format=ids 2>/dev/null | grep -q '[0-9]'; then
    true  # already exists, skip silently
  else
    $WP post create \
      --post_type=page \
      --post_title="$TITLE" \
      --post_content="$CONTENT" \
      --post_status=publish \
      --post_name="$SLUG" \
      --porcelain >/dev/null 2>&1
  fi
}


# 1 — Landing / overview
create_page "gamipress-demo" "GamiPress Demo" \
"<div class='container my-3'>
<div class='p-4 mb-4 bg-white rounded-3 shadow-sm border'>
  <h2 class='fw-bold text-dark mb-3'><i class='fa-solid fa-trophy text-warning me-2'></i>Welcome to the GamiPress Demo</h2>
  <p class='lead text-secondary'>GamiPress is a WordPress plugin that lets you reward users with <strong>points</strong>, <strong>achievements (badges)</strong>, and <strong>ranks</strong> for interacting with your site.</p>
  <div class='row g-3 my-3'>
    <div class='col-md-4'>
      <div class='card h-100 border-0 bg-light shadow-sm'>
        <div class='card-body text-center p-3'>
          <i class='fa-solid fa-coins text-warning fs-1 mb-2'></i>
          <h5 class='card-title fw-bold'>Credits</h5>
          <p class='card-text small text-muted'>Points currency awarded automatically for site activities like logins and comments.</p>
        </div>
      </div>
    </div>
    <div class='col-md-4'>
      <div class='card h-100 border-0 bg-light shadow-sm'>
        <div class='card-body text-center p-3'>
          <i class='fa-solid fa-award text-primary fs-1 mb-2'></i>
          <h5 class='card-title fw-bold'>Badges</h5>
          <p class='card-text small text-muted'>Achievements unlocked by completing defined milestone steps.</p>
        </div>
      </div>
    </div>
    <div class='col-md-4'>
      <div class='card h-100 border-0 bg-light shadow-sm'>
        <div class='card-body text-center p-3'>
          <i class='fa-solid fa-layer-group text-success fs-1 mb-2'></i>
          <h5 class='card-title fw-bold'>Levels</h5>
          <p class='card-text small text-muted'>Rank ladder users climb as they earn credits and complete requirements.</p>
        </div>
      </div>
    </div>
  </div>
  <div class='alert alert-primary d-flex align-items-center mb-3' role='alert'>
    <i class='fa-solid fa-right-to-bracket fs-4 me-3'></i>
    <div>
      <strong>Ready to start?</strong> Log in as the demo user to track balances, unlock badges, and test triggers.<br/>
      <a href='/wp-login.php?redirect_to=%2F' class='btn btn-primary btn-sm mt-2'><i class='fa-solid fa-lock me-1'></i> Log in to Demo</a>
      <span class='ms-2 small text-muted'>Username: <code>demo</code> | Password: <code>demo123</code></span>
    </div>
  </div>
  <div class='d-flex flex-wrap gap-2 pt-2'>
    <a href='/my-points/' class='btn btn-outline-secondary btn-sm'><i class='fa-solid fa-coins me-1'></i> My Points</a>
    <a href='/achievements/' class='btn btn-outline-primary btn-sm'><i class='fa-solid fa-award me-1'></i> Achievements</a>
    <a href='/ranks/' class='btn btn-outline-success btn-sm'><i class='fa-solid fa-layer-group me-1'></i> Ranks</a>
    <a href='/activity-log/' class='btn btn-outline-info btn-sm'><i class='fa-solid fa-list-check me-1'></i> Activity Log</a>
    <a href='/earnings-history/' class='btn btn-outline-dark btn-sm'><i class='fa-solid fa-clock-rotate-left me-1'></i> Earnings History</a>
  </div>
</div>
</div>"

# 2 — Points
create_page "my-points" "My Points" \
"<div class='container my-3'>
<h2 class='fw-bold mb-3'><i class='fa-solid fa-wallet text-warning me-2'></i>Your Points Balance</h2>
<div class='alert alert-warning shadow-sm border-0 d-flex align-items-center mb-4'>
  <i class='fa-solid fa-lock me-2 fs-5'></i>
  <div>
    <strong>You need to be logged in to see your balance.</strong>
    <a href='/wp-login.php?redirect_to=%2Fmy-points%2F' class='alert-link ms-2'>Log in as demo / demo123</a>
  </div>
</div>
<p>GamiPress supports multiple <strong>points types</strong> (Credits, Gems, Coins). The balance below is rendered dynamically by <code>[gamipress_points]</code>:</p>
[gamipress_points]
<div class='card shadow-sm border-0 my-4'>
  <div class='card-header bg-white border-bottom fw-semibold py-3'>
    <i class='fa-solid fa-bolt text-warning me-2'></i>How Credits are Earned
  </div>
  <div class='card-body p-0'>
    <div class='table-responsive'>
      <table class='table table-striped table-hover align-middle mb-0'>
        <thead class='table-light'>
          <tr><th>Action Trigger</th><th>Credits Awarded</th></tr>
        </thead>
        <tbody>
          <tr><td><i class='fa-solid fa-right-to-bracket text-primary me-2'></i>Log in</td><td><span class='badge bg-success'>+5 Credits</span></td></tr>
          <tr><td><i class='fa-solid fa-comment text-info me-2'></i>Leave a comment</td><td><span class='badge bg-success'>+10 Credits</span></td></tr>
          <tr><td><i class='fa-solid fa-calendar-day text-warning me-2'></i>Daily site visit</td><td><span class='badge bg-success'>+20 Credits</span></td></tr>
          <tr><td><i class='fa-solid fa-newspaper text-danger me-2'></i>Publish a post</td><td><span class='badge bg-success'>+50 Credits</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>"

# 3 — Achievements
create_page "achievements" "Achievements" \
"<div class='container my-3'>
<div class='alert alert-warning shadow-sm border-0 d-flex align-items-center mb-4'>
  <i class='fa-solid fa-lock me-2 fs-5'></i>
  <div>
    <strong>Log in to see your personally earned badges.</strong>
    <a href='/wp-login.php?redirect_to=%2Fachievements%2F' class='alert-link ms-2'>Log in as demo / demo123</a>
  </div>
</div>

<div class='mb-4'>
  <h2 class='fw-bold mb-2'><i class='fa-solid fa-award text-primary me-2'></i>Badges You Can Earn</h2>
  <p class='text-secondary mb-0'>
    Unearned badges appear <strong>greyed out with a padlock</strong>. Unlocked badges light up in <strong>bright, vivid colors</strong>!
  </p>
</div>

[gamipress_achievements type=\"badges\" columns=\"3\" limit=\"12\"]

<div class='my-5 pt-4 border-top'>
  <h3 class='fw-bold mb-3'><i class='fa-solid fa-circle-check text-success me-2'></i>Your Earned Badges</h3>
  <p class='text-secondary'>Badges you have unlocked appear below in full color.</p>
  [gamipress_achievements type=\"badges\" columns=\"3\" current_user=\"yes\"]
</div>
</div>"

# 4 — Ranks
create_page "ranks" "Ranks" \
"<div class='container my-3'>
<div class='alert alert-warning shadow-sm border-0 d-flex align-items-center mb-4'>
  <i class='fa-solid fa-lock me-2 fs-5'></i>
  <div>
    <strong>Log in to track your personal rank level and progress.</strong>
    <a href='/wp-login.php?redirect_to=%2Franks%2F' class='alert-link ms-2'>Log in as demo / demo123</a>
  </div>
</div>

<div class='mb-4'>
  <h2 class='fw-bold mb-2'><i class='fa-solid fa-layer-group text-success me-2'></i>Rank Progression Ladder</h2>
  <p class='text-secondary mb-0'>
    <strong>Ranks</strong> represent your level of achievement as you earn Credits. Higher rank tiers unlock automatically as your total Credits grow!
  </p>
</div>

[gamipress_ranks type=\"levels\" columns=\"4\" limit=\"20\"]

<div class='card shadow-sm border-0 my-5'>
  <div class='card-header bg-white border-bottom fw-semibold py-3'>
    <i class='fa-solid fa-circle-info text-primary me-2'></i>How Ranks Work
  </div>
  <div class='card-body p-4'>
    <p class='mb-2'>Users automatically advance through rank tiers based on their accumulated <strong>Credits</strong> balance:</p>
    <ul class='mb-0'>
      <li><strong>Newcomer:</strong> Starting rank (0 Credits)</li>
      <li><strong>Explorer:</strong> Unlocks at 50 Credits</li>
      <li><strong>Contributor:</strong> Unlocks at 200 Credits</li>
      <li><strong>Champion:</strong> Top rank — unlocks at 500 Credits</li>
    </ul>
  </div>
</div>
</div>"

# 5 — Activity Log
create_page "activity-log" "Activity Log" \
"<h2>Community Activity</h2>
<p>The <strong>Activity Log</strong> is a real-time feed of every GamiPress event on the site — points awarded or deducted, badges earned, and ranks reached — for all users.</p>
<p>This is useful for community transparency (users can see each other's progress) and for debugging your gamification setup during development (you can confirm that triggers are firing correctly).</p>
[gamipress_logs limit=\"25\"]
<p><em>Logs update automatically as users interact with the site. Trigger a new entry by leaving a comment, then refresh this page.</em></p>
<h3>Log visibility</h3>
<p>GamiPress supports both <strong>public</strong> and <strong>private</strong> log entries. Private entries are only visible to the user they belong to and to admins. This is configured per award rule in the GamiPress admin.</p>"

# 6. Earnings History
create_page "earnings-history" "Earnings History" \
"<h2>Your Earnings History</h2>
<p style='background:#fff8e1;border-left:4px solid #f0b429;padding:.75em 1em;margin-bottom:1em;'>
  &#128274; <strong>Log in to see your personal earnings history.</strong>
  <a href='/wp-login.php?redirect_to=%2Fearnings-history%2F'>Log in as demo / demo123</a>
</p>
<p>Unlike the community Activity Log, <strong>Earnings History</strong> is scoped to the currently logged-in user. It shows every point award, badge unlock, and rank promotion <em>you</em> have received, in chronological order.</p>
<p>This is a great place to put a personalised profile or dashboard page so users can review their own progress over time.</p>
[gamipress_earnings limit=\"25\"]
<p><em>Log in as <code>demo / demo123</code> and interact with the site to populate your history.</em></p>
<h3>About the shortcode</h3>
<p>The list above is rendered by the <code>[gamipress_earnings]</code> shortcode. You can filter it by points type, achievement type, or date range — see <strong>GamiPress → Help / Support</strong> in the admin for the full list of parameters.</p>"


# Set demo landing page as WordPress front page
DEMO_PAGE_ID=$($WP post list --post_type=page --name=gamipress-demo \
  --field=ID --format=ids 2>/dev/null | head -1)
if [ -n "$DEMO_PAGE_ID" ]; then
  $WP option update show_on_front page >/dev/null 2>&1
  $WP option update page_on_front "$DEMO_PAGE_ID" >/dev/null 2>&1
fi

# ── Create Navigation Menu (Primary Nav excluding GamiPress Demo) ──────────
MENU_ID=$($WP menu list --format=ids 2>/dev/null | head -1)
if [ -z "$MENU_ID" ]; then
  MENU_ID=$($WP menu create "Header Menu" --porcelain 2>/dev/null || true)
fi

if [ -n "$MENU_ID" ]; then
  for SLUG in my-points achievements ranks activity-log earnings-history; do
    PAGE_ID=$(get_id page "$SLUG")
    if [ -n "$PAGE_ID" ]; then
      $WP menu item add-post "$MENU_ID" "$PAGE_ID" >/dev/null 2>&1 || true
    fi
  done
fi

# Clear stale GamiPress trigger listener cache so login & activity triggers fire immediately
$WP eval "gamipress_delete_cache('gamipress_triggers_listeners_count'); if(function_exists('wp_cache_flush')) wp_cache_flush();" >/dev/null 2>&1 || true

echo ""
echo "=============================================="
echo "  GamiPress Demo is ready!"
echo "  Front page:     http://localhost:8080"
echo "  My Points:      http://localhost:8080/my-points/"
echo "  Achievements:   http://localhost:8080/achievements/"
echo "  Ranks:          http://localhost:8080/ranks/"
echo "  Activity Log:   http://localhost:8080/activity-log/"
echo "  Earnings:       http://localhost:8080/earnings-history/"
echo "  Admin:          http://localhost:8080/wp-admin"
echo "  Admin login:    ${WP_ADMIN_USER:-admin} / ${WP_ADMIN_PASSWORD:-admin123}"
echo "  Demo user:      demo / demo123"
echo "=============================================="
