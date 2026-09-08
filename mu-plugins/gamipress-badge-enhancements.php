<?php
/**
 * Plugin Name: GamiPress Badge & Visual Enhancements
 * Description: Integrates Bootstrap 5, Font Awesome 6, and custom greyed-out / brightly coloured badge styling for GamiPress.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Ensure GamiPress Version Options & Custom Tables Are Set ───────────────
add_action( 'init', function() {
    if ( defined( 'GAMIPRESS_VER' ) ) {
        if ( get_option( 'gamipress_version' ) !== GAMIPRESS_VER ) {
            update_option( 'gamipress_version', GAMIPRESS_VER );
            update_option( 'gamipress_db_version', GAMIPRESS_VER );
        }
    }
    if ( function_exists( 'ct_setup_table' ) ) {
        foreach ( array( 'gamipress_user_earnings', 'gamipress_user_earnings_meta', 'gamipress_logs', 'gamipress_logs_meta' ) as $t_name ) {
            $table = ct_setup_table( $t_name );
            if ( $table && isset( $table->db ) ) {
                $table->db->maybe_upgrade();
            }
        }
    }
}, 1 );

// ── Register Custom Template Location for GamiPress ──────────────────────────
add_filter( 'gamipress_template_paths', function( $file_paths ) {
    array_unshift( $file_paths, WPMU_PLUGIN_DIR . '/templates/' );
    return $file_paths;
} );

// ── Enqueue Bootstrap 5 & Font Awesome 6 ──────────────────────────────────────
add_action( 'wp_enqueue_scripts', function() {
    // Bootstrap 5 CSS
    wp_enqueue_style(
        'bootstrap-5',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        array(),
        '5.3.3'
    );

    // Font Awesome 6
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    // Bootstrap 5 Bundle JS
    wp_enqueue_script(
        'bootstrap-5-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.3',
        true
    );
} );

// ── Inject Custom CSS for Badges & Layout ────────────────────────────────────
add_action( 'wp_head', function() {
    ?>
    <style id="gp-demo-badge-styles">
    /* ── Grid Layout for Achievements, Ranks & Points Types ──────────────── */
    .gamipress-achievements-container,
    .gamipress-ranks-container,
    .gamipress-user-points {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
        gap: 1.5rem !important;
        margin-top: 1.5rem !important;
        margin-bottom: 2rem !important;
    }

    /* ── Points Types Card & Icon Styling ───────────────────────────── */
    .gamipress-user-points .gamipress-points {
        float: none !important;
        width: 100% !important;
        margin: 0 !important;
        background: #ffffff !important;
        border-radius: 16px !important;
        padding: 1.75rem 1.25rem !important;
        text-align: center !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }

    .gamipress-user-points .gamipress-points:hover {
        transform: translateY(-5px) scale(1.02) !important;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.1) !important;
    }

    .gamipress-user-points-image {
        width: 76px !important;
        height: 76px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 1.25rem auto !important;
        position: relative !important;
        transition: transform 0.3s ease !important;
    }

    .gamipress-user-points .gamipress-points:hover .gamipress-user-points-image {
        transform: scale(1.08) rotate(4deg) !important;
    }

    /* Specific Points Type Icons & Gradients */
    .gamipress-user-points-coins-image:empty {
        background: linear-gradient(135deg, #ffb347, #ffcc33) !important;
        box-shadow: 0 6px 16px rgba(255, 179, 71, 0.4) !important;
    }
    .gamipress-user-points-coins-image:empty::before {
        content: "\f51e";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 34px;
        color: #ffffff;
        filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.25));
    }

    .gamipress-user-points-credits-image:empty {
        background: linear-gradient(135deg, #f09819, #edde5d) !important;
        box-shadow: 0 6px 16px rgba(240, 152, 25, 0.4) !important;
    }
    .gamipress-user-points-credits-image:empty::before {
        content: "\f555";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 34px;
        color: #ffffff;
        filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.25));
    }

    .gamipress-user-points-gems-image:empty {
        background: linear-gradient(135deg, #e100ff, #7f00ff) !important;
        box-shadow: 0 6px 16px rgba(225, 0, 255, 0.4) !important;
    }
    .gamipress-user-points-gems-image:empty::before {
        content: "\f3a5";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 34px;
        color: #ffffff;
        filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.25));
    }

    .gamipress-user-points-image:empty:not(.gamipress-user-points-coins-image):not(.gamipress-user-points-credits-image):not(.gamipress-user-points-gems-image) {
        background: linear-gradient(135deg, #4facfe, #00f2fe) !important;
        box-shadow: 0 6px 16px rgba(79, 172, 254, 0.4) !important;
    }
    .gamipress-user-points-image:empty:not(.gamipress-user-points-coins-image):not(.gamipress-user-points-credits-image):not(.gamipress-user-points-gems-image)::before {
        content: "\f005";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 34px;
        color: #ffffff;
        filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.25));
    }

    /* Typography for Amounts & Labels */
    .gamipress-user-points-amount {
        font-size: 2.25rem !important;
        font-weight: 800 !important;
        color: #212529 !important;
        display: block !important;
        line-height: 1.1 !important;
        letter-spacing: -0.5px !important;
    }

    .gamipress-user-points-label {
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        color: #6c757d !important;
        display: block !important;
        margin-top: 0.35rem !important;
    }

    /* Clear default float/inline GamiPress layout styles */
    .gamipress-achievement.gp-badge-card,
    .gamipress-rank.gp-rank-card {
        float: none !important;
        width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    /* ── Active / Current User Rank Highlight ───────────────────────────── */
    .gp-rank-card.current-user-rank,
    .gp-rank-card.active-rank-card {
        border: 2px solid #0d6efd !important;
        box-shadow: 0 0 18px rgba(13, 110, 253, 0.25) !important;
    }

    /* ── Base Card Design ─────────────────────────────────────────────────── */
    .gp-badge-card {
        background: #ffffff;
        border-radius: 16px !important;
        padding: 1.5rem 1.25rem !important;
        text-align: center;
        position: relative;
        transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .gp-badge-card-inner {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* ── Icon Circle ────────────────────────────────────────────────────── */
    .gp-badge-icon-wrapper {
        width: 84px;
        height: 84px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: transform 0.3s ease;
    }

    .gp-badge-card:hover .gp-badge-icon-wrapper {
        transform: scale(1.08) rotate(4deg);
    }

    .gp-badge-fa-icon {
        font-size: 38px;
        color: #ffffff;
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.25));
    }

    /* ── GREYED OUT / UNLEARNED / LOCKED BADGES & RANKS ──────────────────── */
    .gp-badge-card.badge-locked,
    .gamipress-achievement.user-has-not-earned,
    .gamipress-rank.user-has-not-earned {
        filter: grayscale(100%);
        opacity: 0.65;
        background: #f8f9fa !important;
        border: 1px dashed #ced4da !important;
        box-shadow: none !important;
    }

    .gp-badge-card.badge-locked:hover,
    .gamipress-achievement.user-has-not-earned:hover,
    .gamipress-rank.user-has-not-earned:hover {
        filter: grayscale(70%);
        opacity: 0.85;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .badge-locked .gp-badge-fa-icon {
        color: #6c757d !important;
        filter: none !important;
    }

    .gp-badge-lock-overlay {
        position: absolute;
        bottom: -2px;
        right: -2px;
        background: #495057;
        color: #ffffff;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    /* ── BRIGHTLY COLOURED / UNLOCKED BADGES & RANKS ─────────────────────── */
    .gp-badge-card.badge-unlocked,
    .gamipress-achievement.user-has-earned,
    .gamipress-rank.user-has-earned {
        filter: none !important;
        opacity: 1 !important;
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.06) !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08) !important;
    }

    .gp-badge-card.badge-unlocked:hover,
    .gamipress-achievement.user-has-earned:hover,
    .gamipress-rank.user-has-earned:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.14) !important;
    }

    /* ── Requirements & Step Lists ────────────────────────────────────────── */
    .gp-badge-steps ul.gamipress-required-achievements,
    .gp-badge-steps ul.gamipress-rank-requirements {
        list-style: none !important;
        padding: 0 !important;
        margin: 0.4rem 0 0 0 !important;
    }

    .gp-badge-steps ul.gamipress-required-achievements li,
    .gp-badge-steps ul.gamipress-rank-requirements li {
        font-size: 0.82rem;
        padding: 5px 10px;
        border-radius: 8px;
        background: #f1f3f5;
        margin-bottom: 4px;
        color: #495057;
        display: inline-block;
        width: 100%;
        box-sizing: border-box;
    }

    .gp-badge-steps ul.gamipress-required-achievements li.user-has-earned,
    .gp-badge-steps ul.gamipress-rank-requirements li.user-has-earned {
        background: #e6fcf5 !important;
        color: #0ca678 !important;
        font-weight: 600;
        border: 1px solid #96f2d7;
    }

    .gp-pts-req-pill {
        font-size: 0.82rem;
        padding: 5px 10px;
        border-radius: 8px;
        background: #fff9db;
        color: #f59f00;
        font-weight: 600;
        border: 1px solid #ffe066;
        display: inline-block;
        width: 100%;
    }

    /* Hide standard GamiPress toggle switch and redundant headings if steps are rendered cleanly */
    .gp-badge-card .gamipress-open-close-switch,
    .gp-badge-card .gamipress-rank-requirements-heading,
    .gamipress-rank-type-title {
        display: none !important;
    }
    .gp-badge-card .gamipress-extras-window {
        display: block !important;
    }

    /* Modern Theme & Typography Overrides */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f8f9fa;
        color: #212529;
    }
    </style>
    <?php
}, 99 );

// ── Earnings History Thumbnail Fallback (Font Awesome Icons) ───────────────
add_filter( 'gamipress_earnings_render_column', function( $column_output, $column_name, $user_earning, $template_args ) {
    if ( $column_name !== 'thumbnail' ) {
        return $column_output;
    }

    if ( ! empty( trim( $column_output ) ) ) {
        return $column_output;
    }

    $post_id = $user_earning->post_id;
    $post    = get_post( $post_id );
    $slug    = $post ? $post->post_name : '';
    $title   = $user_earning->title ? $user_earning->title : ( $post ? $post->post_title : '' );

    $icon_map = array(
        'badge-welcome'          => array( 'icon' => 'fa-rocket',          'bg' => 'linear-gradient(135deg, #ff416c, #ff4b2b)' ),
        'badge-conversationalist' => array( 'icon' => 'fa-comments',        'bg' => 'linear-gradient(135deg, #00c6ff, #0072ff)' ),
        'badge-regular-visitor'  => array( 'icon' => 'fa-fire',            'bg' => 'linear-gradient(135deg, #f857a6, #ff5858)' ),
        'badge-author'           => array( 'icon' => 'fa-pen-nib',         'bg' => 'linear-gradient(135deg, #11998e, #38ef7d)' ),
        'badge-high-roller'      => array( 'icon' => 'fa-crown',           'bg' => 'linear-gradient(135deg, #f7971e, #ffd200)' ),
        'rank-newcomer'          => array( 'icon' => 'fa-seedling',        'bg' => 'linear-gradient(135deg, #42e695, #3bb2b8)' ),
        'rank-explorer'          => array( 'icon' => 'fa-compass',         'bg' => 'linear-gradient(135deg, #5b86e5, #36d1dc)' ),
        'rank-contributor'       => array( 'icon' => 'fa-award',           'bg' => 'linear-gradient(135deg, #ff8c00, #e52e71)' ),
        'rank-champion'          => array( 'icon' => 'fa-trophy',          'bg' => 'linear-gradient(135deg, #f7971e, #ffd200)' ),
        'credits'                => array( 'icon' => 'fa-coins',           'bg' => 'linear-gradient(135deg, #f09819, #edde5d)' ),
        'gems'                   => array( 'icon' => 'fa-gem',             'bg' => 'linear-gradient(135deg, #e100ff, #7f00ff)' ),
        'coins'                  => array( 'icon' => 'fa-coins',           'bg' => 'linear-gradient(135deg, #ffb347, #ffcc33)' ),
    );

    $cfg = $icon_map[ $slug ] ?? null;

    if ( ! $cfg ) {
        if ( strpos( strtolower( $title ), 'gem' ) !== false ) {
            $cfg = array( 'icon' => 'fa-gem', 'bg' => 'linear-gradient(135deg, #e100ff, #7f00ff)' );
        } elseif ( strpos( strtolower( $title ), 'coin' ) !== false ) {
            $cfg = array( 'icon' => 'fa-coins', 'bg' => 'linear-gradient(135deg, #ffb347, #ffcc33)' );
        } elseif ( in_array( $user_earning->post_type, array( 'credits', 'points-type', 'points-award' ) ) || strpos( strtolower( $title ), 'credit' ) !== false ) {
            $cfg = array( 'icon' => 'fa-coins', 'bg' => 'linear-gradient(135deg, #f09819, #edde5d)' );
        } elseif ( in_array( $user_earning->post_type, gamipress_get_achievement_types_slugs() ) ) {
            $cfg = array( 'icon' => 'fa-award', 'bg' => 'linear-gradient(135deg, #8e2de2, #4a00e0)' );
        } elseif ( in_array( $user_earning->post_type, gamipress_get_rank_types_slugs() ) ) {
            $cfg = array( 'icon' => 'fa-layer-group', 'bg' => 'linear-gradient(135deg, #11998e, #38ef7d)' );
        } else {
            $cfg = array( 'icon' => 'fa-star', 'bg' => 'linear-gradient(135deg, #667eea, #764ba2)' );
        }
    }

    return sprintf(
        '<div class="gp-earnings-thumb" style="width:40px;height:40px;border-radius:50%%;background:%s;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:18px;box-shadow:0 3px 8px rgba(0,0,0,0.15);"><i class="fa-solid %s"></i></div>',
        esc_attr( $cfg['bg'] ),
        esc_attr( $cfg['icon'] )
    );
}, 20, 4 );

// ── Automatic Rank Update on Points Gain ──────────────────────────────────
function gp_check_and_update_user_rank( $user_id ) {
    if ( ! $user_id || ! function_exists( 'gamipress_get_user_points' ) ) {
        return;
    }

    $ranks = gamipress_get_ranks( array(
        'post_type'      => gamipress_get_rank_types_slugs(),
        'orderby'        => 'meta_value_num',
        'meta_key'       => '_gamipress_points_to_unlock',
        'order'          => 'DESC',
        'posts_per_page' => -1,
    ) );

    if ( empty( $ranks ) ) {
        return;
    }

    foreach ( $ranks as $rank ) {
        $threshold = (int) get_post_meta( $rank->ID, '_gamipress_points_to_unlock', true );
        $pts_type  = get_post_meta( $rank->ID, '_gamipress_points_type_to_unlock', true );
        if ( empty( $pts_type ) ) {
            $pts_type = 'credits';
        }

        $user_pts = (int) gamipress_get_user_points( $user_id, $pts_type );

        if ( $user_pts >= $threshold ) {
            $rank_type       = get_post_type( $rank->ID );
            $current_rank_id = (int) gamipress_get_user_rank_id( $user_id, $rank_type );
            if ( $current_rank_id !== (int) $rank->ID ) {
                gamipress_update_user_rank( $user_id, $rank->ID, $current_rank_id );
            }
            break;
        }
    }
}

add_action( 'gamipress_award_points_to_user', function( $user_id, $amount, $points_type ) {
    if ( $points_type === 'credits' ) {
        gp_check_and_update_user_rank( $user_id );
    }
}, 10, 3 );

add_action( 'gamipress_update_user_points', function( $user_id, $new_points, $old_points, $points_type ) {
    gp_check_and_update_user_rank( $user_id );
}, 10, 4 );

add_action( 'wp', function() {
    if ( is_user_logged_in() ) {
        gp_check_and_update_user_rank( get_current_user_id() );
    }
} );

// ── Exclude 'GamiPress Demo' Landing Page & Add Log In / Log Out to Menus ──
add_filter( 'wp_page_menu_args', function( $args ) {
    $demo_page = get_page_by_path( 'gamipress-demo' );
    if ( $demo_page ) {
        $exclude = ! empty( $args['exclude'] ) ? $args['exclude'] : '';
        $args['exclude'] = trim( $exclude . ',' . $demo_page->ID, ',' );
    }
    return $args;
} );

add_filter( 'wp_get_nav_menu_items', function( $items, $menu, $args ) {
    if ( empty( $items ) || ! is_array( $items ) ) {
        return $items;
    }
    $demo_page = get_page_by_path( 'gamipress-demo' );
    if ( ! $demo_page ) {
        return $items;
    }

    return array_values( array_filter( $items, function( $item ) use ( $demo_page ) {
        return (int) $item->object_id !== (int) $demo_page->ID && $item->post_name !== 'gamipress-demo';
    } ) );
}, 10, 3 );

add_filter( 'wp_page_menu', function( $menu ) {
    if ( is_user_logged_in() ) {
        $logout_url  = esc_url( wp_logout_url( home_url() ) );
        $logout_item = sprintf(
            '<li class="page_item menu-item"><a href="%s" class="text-danger fw-semibold" style="color:#dc3545!important;"><i class="fa-solid fa-right-from-bracket me-1"></i> Log out</a></li>',
            $logout_url
        );
        $menu = str_replace( '</ul>', $logout_item . '</ul>', $menu );
    } else {
        $login_url  = esc_url( wp_login_url( home_url() ) );
        $login_item = sprintf(
            '<li class="page_item menu-item"><a href="%s" class="text-primary fw-semibold"><i class="fa-solid fa-right-to-bracket me-1"></i> Log in</a></li>',
            $login_url
        );
        $menu = str_replace( '</ul>', $login_item . '</ul>', $menu );
    }
    return $menu;
} );

// ── Gutenberg Block Theme Navigation Filter (Twenty Twenty-Two FSE) ─────────
add_filter( 'render_block', function( $block_content, $block ) {
    if ( ! empty( $block['blockName'] ) && ( $block['blockName'] === 'core/page-list' || $block['blockName'] === 'core/navigation' ) ) {
        // Strip GamiPress Demo page item from Block Theme navigation
        $pattern1 = '/<li[^>]*menu-item-home[^>]*>.*?<\/li>/is';
        $block_content = preg_replace( $pattern1, '', $block_content );
        $pattern2 = '/<li[^>]*>.*?href=[\'"][^\'"]*gamipress-demo[^\'"]*[\'"][^>]*>.*?<\/li>/is';
        $block_content = preg_replace( $pattern2, '', $block_content );

        // Append Log out or Log in item if not already present
        if ( strpos( $block_content, 'Log out' ) === false && strpos( $block_content, 'Log in' ) === false ) {
            if ( is_user_logged_in() ) {
                $logout_url  = esc_url( wp_logout_url( home_url() ) );
                $logout_item = sprintf(
                    '<li class="wp-block-pages-list__item wp-block-navigation-item open-on-hover-click"><a class="wp-block-pages-list__item__link wp-block-navigation-item__content text-danger fw-semibold" style="color:#dc3545!important;" href="%s"><i class="fa-solid fa-right-from-bracket me-1"></i> Log out</a></li>',
                    $logout_url
                );
                $block_content = preg_replace( '/<\/ul>/', $logout_item . '</ul>', $block_content, 1 );
            } else {
                $login_url  = esc_url( wp_login_url( home_url() ) );
                $login_item = sprintf(
                    '<li class="wp-block-pages-list__item wp-block-navigation-item open-on-hover-click"><a class="wp-block-pages-list__item__link wp-block-navigation-item__content text-primary fw-semibold" href="%s"><i class="fa-solid fa-right-to-bracket me-1"></i> Log in</a></li>',
                    $login_url
                );
                $block_content = preg_replace( '/<\/ul>/', $login_item . '</ul>', $block_content, 1 );
            }
        }
    }
    return $block_content;
}, 10, 2 );


// ── Dynamic Login Alert vs Logged In Status Banner Replacement ─────────────
add_filter( 'the_content', function( $content ) {
    if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    if ( is_user_logged_in() ) {
        $user       = wp_get_current_user();
        $user_name  = $user ? $user->display_name : 'User';
        $logout_url = esc_url( wp_logout_url( get_permalink() ) );

        $logged_in_bar = sprintf(
            '<div class="alert alert-success border-0 shadow-sm d-flex align-items-center justify-content-between py-2 px-3 mb-4 rounded-3">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-user text-success fs-4 me-2"></i>
                    <span>Logged in as <strong class="text-dark">%s</strong></span>
                </div>
                <a href="%s" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Log out
                </a>
            </div>',
            esc_html( $user_name ),
            $logout_url
        );

        $patterns = array(
            '/<div[^>]*class=[\'"][^\'"]*alert-(?:warning|primary|info)[^\'"]*[\'"][^>]*>.*?Log in.*?<\/div>/is',
            '/<p[^>]*style=[\'"][^\'"]*background:#fff8e1[^\'"]*[\'"][^>]*>.*?Log in.*?<\/p>/is',
        );

        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $content ) ) {
                $content = preg_replace( $pattern, $logged_in_bar, $content, 1 );
                break;
            }
        }
    }

    return $content;
}, 20 );

// ── Robust Login Trigger & Cache Invalidation ──────────────────────────────
add_action( 'init', function() {
    if ( function_exists( 'gamipress_get_triggers_listeners_count' ) ) {
        $tc = gamipress_get_triggers_listeners_count();
        if ( empty( $tc ) ) {
            gamipress_delete_cache( 'gamipress_triggers_listeners_count' );
            if ( function_exists( 'wp_cache_flush' ) ) {
                wp_cache_flush();
            }
        }
    }
}, 5 );

add_action( 'wp_login', function( $user_login, $user = null ) {
    if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
        $user = get_user_by( 'login', $user_login );
        if ( ! $user ) {
            $user = get_user_by( 'email', $user_login );
        }
    }
    if ( $user && function_exists( 'gamipress_trigger_event' ) ) {
        gamipress_trigger_event( array(
            'event'   => 'gamipress_login',
            'user_id' => $user->ID,
        ) );
    }
}, 20, 2 );




