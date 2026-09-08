# GamiPress: Docs vs GitHub Repo (v2.4.0) — Gap Analysis

> **Repo version:** 2.4.0 · Tested up to WP 6.0 · No `Requires PHP` header  
> **Docs site:** gamipress.com/docs (last modified 2026-07-31)

---

## ✅ Shortcodes — Fully Matched

The docs and the repo shortcode files are in exact 1-to-1 agreement:

| Shortcode | Doc | Repo file |
|---|---|---|
| `gamipress_achievement` | ✅ | `gamipress_achievement.php` |
| `gamipress_achievements` | ✅ | `gamipress_achievements.php` |
| `gamipress_earnings` | ✅ | `gamipress_earnings.php` |
| `gamipress_email_settings` | ✅ | `gamipress_email_settings.php` |
| `gamipress_inline_achievement` | ✅ | `gamipress_inline_achievement.php` |
| `gamipress_inline_last_achievements_earned` | ✅ | `gamipress_inline_last_achievements_earned.php` |
| `gamipress_inline_rank` | ✅ | `gamipress_inline_rank.php` |
| `gamipress_inline_user_rank` | ✅ | `gamipress_inline_user_rank.php` |
| `gamipress_last_achievements_earned` | ✅ | `gamipress_last_achievements_earned.php` |
| `gamipress_logs` | ✅ | `gamipress_logs.php` |
| `gamipress_points` | ✅ | `gamipress_points.php` |
| `gamipress_points_types` | ✅ | `gamipress_points_types.php` |
| `gamipress_rank` | ✅ | `gamipress_rank.php` |
| `gamipress_ranks` | ✅ | `gamipress_ranks.php` |
| `gamipress_site_points` | ✅ | `gamipress_site_points.php` |
| `gamipress_user_points` | ✅ | `gamipress_user_points.php` |
| `gamipress_user_rank` | ✅ | `gamipress_user_rank.php` |

> [!NOTE]
> Blocks are **auto-generated from shortcodes** at runtime (`includes/blocks.php` line 101:
> `gamipress_` → `gamipress/`, underscores → hyphens). So every shortcode automatically
> becomes a Gutenberg block — this is why the blocks docs also list 17 matching entries.

---

## ❌ Features Documented But NOT in the GitHub Repo

### 1. `gamipress/points` block (docs label: "Points")
The docs list a **Points block** (`/docs/blocks/points/`) but there is no corresponding
`gamipress_points` block in the auto-generated list (it maps from `gamipress_points` shortcode,
which _is_ present). This is actually present — see note above.

### 2. Pro / Premium Add-on Features
The docs site extensively references features that **require paid add-ons** not present in the repo:

| Feature | Add-on needed |
|---|---|
| Leaderboards (`[gamipress_points_leaderboard]`) | GamiPress – Leaderboards |
| User-specific achievement lists (`[gamipress_user_achievements]`) | Not core — was incorrectly assumed |
| Notifications / Achievement Popups | GamiPress – Notifications |
| Reports & Analytics dashboard | GamiPress – Reports |
| Progress bars | GamiPress – Progress |
| Shareable links to achievements | GamiPress – Social Share |
| Badges open-badge export (Badgr / Credly) | GamiPress – Badgr / Credly add-ons |
| PDF certificates | GamiPress – Certificates |
| BuddyPress / BuddyBoss integration | GamiPress – BuddyPress integration |
| WooCommerce integration | GamiPress – WooCommerce integration |
| bbPress integration | GamiPress – bbPress integration |
| LearnDash / LifterLMS / TutorLMS integration | Separate integration add-ons |
| MemberPress / Restrict Content Pro integration | Separate integration add-ons |

> [!IMPORTANT]
> The `readme.md` in the repo mentions Badgr, Credly, social sharing, etc. as features —
> these are actually **links to paid add-ons**, not built into this repo.

### 3. REST API — Partial implementation only
The docs say "Full support to WordPress REST API". What's actually in the repo:

| Endpoint | Status |
|---|---|
| `wp/v2/{points-type}` `wp/v2/{achievement-type}` `wp/v2/{rank-type}` | ✅ via `class-wp-rest-gamipress-posts-controller.php` (extends WP core REST controller) |
| `gamipress/v1/logs` | ✅ `includes/api/logs.php` |
| `gamipress/v1/user-earnings` | ✅ `includes/api/user-earnings.php` |
| Custom gamipress namespace for points, users, achievements | ❌ Not present |
| Dedicated docs page for REST API | ❌ Not on the docs site either |

The REST API is functional but **minimal** — two custom endpoints. The docs don't explicitly document the API endpoints at all (no `/docs/rest-api/` page exists).

### 4. Version & Compatibility Gap
| Attribute | Repo (v2.4.0) | Docs / WordPress.org (current) |
|---|---|---|
| Plugin version | **2.4.0** | Latest release is significantly newer |
| Tested up to WP | **6.0** (released May 2022) | WP is now at 6.7+ |
| `Requires PHP` | **Not declared** | Best practice to declare (≥7.4 recommended) |

> [!WARNING]
> The codebase is **several major WP versions behind**. Running it on modern WP may surface
> deprecation notices (e.g., `_register_controls`, block API changes, `wp_localize_script`
> patterns have evolved). The healthcheck in the Docker Compose uses `wordpress:6.0` to
> deliberately match the tested-up-to version.

### 5. Admin Tools — Present in Repo, No Dedicated Docs Page

These tools exist in `includes/admin/tools/` but have no individual docs article:

| Tool file | What it does |
|---|---|
| `bulk-awards.php` | Bulk award achievements/points to users |
| `bulk-revokes.php` | Bulk revoke achievements/points |
| `recount-activity.php` | Recount triggers for existing users |
| `reset-data.php` | Full data reset |
| `logs-clean-up.php` | Delete old log entries |
| `import-export-achievements.php` | CSV import/export of achievements |
| `import-export-earnings.php` | CSV import/export of user earnings |
| `import-export-points.php` | CSV import/export of points balances |
| `import-export-ranks.php` | CSV import/export of ranks |
| `import-export-settings.php` | Settings import/export |
| `system-info.php` | System debug info |

The docs only covers three high-level tool categories (General, Import/Export, System).
**None of the individual tool pages link to code-level documentation**.

### 6. Settings Pages — Present but Underdocumented
`includes/admin/settings/` contains:
- `general.php`, `logs.php`, `email.php`, `social.php`, `style.php`, `network.php`

The docs has a `/docs/settings/` section but it **does not cover** `social.php` (social sharing
settings built into core) or `style.php` (custom CSS/color settings) as separate pages.

### 7. Multisite / Network Mode
`includes/network.php` (10 KB) implements data centralisation for WordPress Multisite.
The docs mentions this as a feature bullet in the readme, but there is **no dedicated
`/docs/network/` documentation page**.

---

## Summary

| Category | Docs ↔ Repo |
|---|---|
| Shortcodes (17) | ✅ Perfect match |
| Gutenberg Blocks (17, auto-generated) | ✅ Perfect match |
| REST API | ⚠️ Minimal (2 custom endpoints), underdocumented |
| Admin Tools (11 tools) | ⚠️ Exist in code, docs only cover categories not individual tools |
| Settings pages | ⚠️ `social.php` and `style.php` not individually documented |
| Multisite/Network | ⚠️ Implemented in code, no dedicated docs page |
| Pro add-on features | ❌ Documented on site, **not in this repo** |
| Plugin version currency | ❌ v2.4.0 is old; tested against WP 6.0 only |
