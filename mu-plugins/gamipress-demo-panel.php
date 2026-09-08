<?php
/**
 * Plugin Name: GamiPress Demo Panel
 * Description: Floating quick-win panel for the GamiPress demo — award Credits and badges without leaving the page.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── AJAX handler ─────────────────────────────────────────────────────────────

add_action( 'wp_ajax_gp_demo_award', 'gp_demo_panel_award' );

function gp_demo_panel_award() {
    check_ajax_referer( 'gp_demo_panel', 'nonce' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( 'Not logged in.' );
    }

    $type = sanitize_text_field( $_POST['award_type'] ?? '' );

    // ── Points ────────────────────────────────────────────────────────────────
    if ( $type === 'credits' ) {
        $amount = absint( $_POST['amount'] ?? 100 );
        gamipress_award_points_to_user( $user_id, $amount, 'credits' );

        $balance = gamipress_get_user_points( $user_id, 'credits' );
        wp_send_json_success( array(
            'message' => sprintf( '+%d Credits awarded! New balance: %d', $amount, $balance ),
        ) );
    }

    // ── Badge ─────────────────────────────────────────────────────────────────
    if ( strpos( $type, 'badge:' ) === 0 ) {
        $badge_id = absint( substr( $type, 6 ) );
        $badge    = get_post( $badge_id );

        if ( ! $badge || $badge->post_type !== 'badges' ) {
            wp_send_json_error( 'Badge not found.' );
        }

        gamipress_award_achievement_to_user( $badge_id, $user_id );
        wp_send_json_success( array(
            'message' => '🏅 Badge earned: ' . esc_html( $badge->post_title ),
        ) );
    }

    wp_send_json_error( 'Unknown award type.' );
}

// ── Front-end panel ───────────────────────────────────────────────────────────

add_action( 'wp_footer', 'gp_demo_panel_render' );

function gp_demo_panel_render() {
    if ( ! is_user_logged_in() ) return;
    if ( ! function_exists( 'gamipress_award_points_to_user' ) ) return;

    $nonce    = wp_create_nonce( 'gp_demo_panel' );
    $ajax_url = admin_url( 'admin-ajax.php' );
    $badges   = get_posts( array(
        'post_type'      => 'badges',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ) );
    ?>
    <style>
    #gp-demo-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        background: #2271b1;
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        font: 600 14px/1 -apple-system,sans-serif;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(0,0,0,.25);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background .2s;
    }
    #gp-demo-toggle:hover { background: #135e96; }

    #gp-demo-panel {
        position: fixed;
        bottom: 76px;
        right: 24px;
        z-index: 99998;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,.18);
        width: 300px;
        font: 14px/1.5 -apple-system,sans-serif;
        display: none;
        overflow: hidden;
    }
    #gp-demo-panel.open { display: block; }

    .gp-panel-header {
        background: #2271b1;
        color: #fff;
        padding: 14px 16px;
        font-weight: 700;
        font-size: 15px;
    }
    .gp-panel-header small {
        display: block;
        font-weight: 400;
        font-size: 12px;
        opacity: .8;
        margin-top: 2px;
    }
    .gp-panel-body { padding: 16px; }

    .gp-panel-body h4 {
        margin: 0 0 8px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #666;
    }
    .gp-panel-body h4:not(:first-child) { margin-top: 16px; }

    .gp-credits-row {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .gp-btn {
        flex: 1;
        border: 1px solid #2271b1;
        background: #fff;
        color: #2271b1;
        border-radius: 6px;
        padding: 8px 10px;
        font: 600 13px/1 -apple-system,sans-serif;
        cursor: pointer;
        transition: background .15s, color .15s;
        text-align: center;
    }
    .gp-btn:hover { background: #2271b1; color: #fff; }
    .gp-btn:disabled { opacity: .5; cursor: default; }

    .gp-badge-btn {
        display: block;
        width: 100%;
        margin-bottom: 6px;
        border: 1px solid #ddd;
        background: #fafafa;
        color: #333;
        border-radius: 6px;
        padding: 9px 12px;
        font: 13px/1 -apple-system,sans-serif;
        cursor: pointer;
        text-align: left;
        transition: background .15s, border-color .15s;
    }
    .gp-badge-btn:hover { background: #f0f6fc; border-color: #2271b1; }
    .gp-badge-btn:disabled { opacity: .5; cursor: default; }
    .gp-badge-btn span { font-size: 11px; color: #888; display: block; margin-top: 2px; }

    #gp-panel-toast {
        margin-top: 12px;
        padding: 9px 12px;
        border-radius: 6px;
        font-size: 13px;
        display: none;
    }
    #gp-panel-toast.success { background: #edfce8; color: #1a7f37; border: 1px solid #a8e6b0; }
    #gp-panel-toast.error   { background: #fce8e8; color: #cf222e; border: 1px solid #f5b8b8; }
    </style>

    <button id="gp-demo-toggle" aria-expanded="false">
        🎮 Demo Panel
    </button>

    <div id="gp-demo-panel" role="dialog" aria-label="GamiPress Demo Panel">
        <div class="gp-panel-header">
            🎮 Demo Quick-Wins
            <small>Award yourself points &amp; badges instantly</small>
        </div>
        <div class="gp-panel-body">

            <h4>💰 Credits</h4>
            <div class="gp-credits-row">
                <button class="gp-btn" data-award="credits" data-amount="10">+10</button>
                <button class="gp-btn" data-award="credits" data-amount="50">+50</button>
                <button class="gp-btn" data-award="credits" data-amount="100">+100</button>
                <button class="gp-btn" data-award="credits" data-amount="500">+500</button>
            </div>

            <?php if ( $badges ) : ?>
            <h4>🏅 Badges</h4>
            <?php
            $badge_descriptions = array(
                'badge-welcome'          => 'Log in once',
                'badge-conversationalist' => 'Leave 3 comments',
                'badge-regular-visitor'  => 'Visit 5 days',
                'badge-author'           => 'Publish a post',
                'badge-high-roller'      => 'Accumulate 100 Credits',
            );
            foreach ( $badges as $badge ) :
                $desc = $badge_descriptions[ $badge->post_name ] ?? '';
            ?>
            <button class="gp-badge-btn" data-award="badge:<?php echo esc_attr( $badge->ID ); ?>">
                <?php echo esc_html( $badge->post_title ); ?>
                <?php if ( $desc ) : ?><span><?php echo esc_html( $desc ); ?></span><?php endif; ?>
            </button>
            <?php endforeach; ?>
            <?php endif; ?>

            <div id="gp-panel-toast"></div>
        </div>
    </div>

    <script>
    (function () {
        var toggle   = document.getElementById('gp-demo-toggle');
        var panel    = document.getElementById('gp-demo-panel');
        var toast    = document.getElementById('gp-panel-toast');
        var ajaxUrl  = <?php echo json_encode( $ajax_url ); ?>;
        var nonce    = <?php echo json_encode( $nonce ); ?>;

        toggle.addEventListener('click', function () {
            var open = panel.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open);
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== toggle) {
                panel.classList.remove('open');
                toggle.setAttribute('aria-expanded', false);
            }
        });

        function showToast(msg, type) {
            toast.textContent = msg;
            toast.className   = type;
            toast.style.display = 'block';
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { toast.style.display = 'none'; }, 4000);
        }

        function award(btn, awardType, amount) {
            btn.disabled = true;
            var body = new URLSearchParams({
                action:     'gp_demo_award',
                nonce:      nonce,
                award_type: awardType,
            });
            if (amount) body.append('amount', amount);

            fetch(ajaxUrl, { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        showToast('✅ ' + data.data.message, 'success');
                    } else {
                        showToast('❌ ' + (data.data || 'Something went wrong.'), 'error');
                    }
                })
                .catch(function () {
                    showToast('❌ Request failed — is WordPress running?', 'error');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        }

        panel.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-award]');
            if (!btn) return;
            award(btn, btn.dataset.award, btn.dataset.amount || null);
        });
    }());
    </script>
    <?php
}
