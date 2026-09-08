<?php
/**
 * Custom GamiPress Rank Template with Font Awesome Icons & Vibrant Styling
 */

global $gamipress_template_args;
$a = $gamipress_template_args;

$rank_id = get_the_ID();
$user_id = isset( $a['user_id'] ) ? absint( $a['user_id'] ) : get_current_user_id();

// Determine rank status
$current_rank_id = gamipress_get_user_rank_id( $user_id, 'levels' );
$is_current      = ( (int) $current_rank_id === (int) $rank_id );

$user_credits = (int) gamipress_get_user_points( $user_id, 'credits' );
$req_pts      = (int) get_post_meta( $rank_id, '_gamipress_points_to_unlock', true );

// Rank is earned if lowest priority (Newcomer), or explicit earned, or credits >= threshold
$earned = gamipress_is_lowest_priority_rank( $rank_id ) || gamipress_has_user_earned_rank( $rank_id, $user_id ) || ( $req_pts > 0 && $user_credits >= $req_pts );

$post  = get_post( $rank_id );
$slug  = $post ? $post->post_name : '';
$title = get_the_title( $rank_id );

// Map ranks to Font Awesome 6 icons and vibrant gradients
$rank_config = array(
    'rank-newcomer' => array(
        'icon'     => 'fa-seedling',
        'gradient' => 'linear-gradient(135deg, #42e695 0%, #3bb2b8 100%)',
        'shadow'   => 'rgba(66, 230, 149, 0.35)',
    ),
    'rank-explorer' => array(
        'icon'     => 'fa-compass',
        'gradient' => 'linear-gradient(135deg, #5b86e5 0%, #36d1dc 100%)',
        'shadow'   => 'rgba(91, 134, 229, 0.35)',
    ),
    'rank-contributor' => array(
        'icon'     => 'fa-award',
        'gradient' => 'linear-gradient(135deg, #ff8c00 0%, #e52e71 100%)',
        'shadow'   => 'rgba(255, 140, 0, 0.35)',
    ),
    'rank-champion' => array(
        'icon'     => 'fa-trophy',
        'gradient' => 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)',
        'shadow'   => 'rgba(247, 151, 30, 0.35)',
    ),
);

$config = $rank_config[ $slug ] ?? array(
    'icon'     => 'fa-layer-group',
    'gradient' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    'shadow'   => 'rgba(17, 153, 142, 0.35)',
);

$classes = array(
    'gamipress-rank',
    'gp-badge-card',
    'gp-rank-card',
    $earned ? 'user-has-earned badge-unlocked' : 'user-has-not-earned badge-locked',
    $is_current ? 'current-user-rank active-rank-card' : '',
);
?>

<div id="gamipress-rank-<?php echo esc_attr( $rank_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
    
    <div class="gp-badge-card-inner">
        
        <!-- Icon Wrapper -->
        <div class="gp-badge-icon-wrapper" style="<?php echo $earned ? 'background:' . $config['gradient'] . ';box-shadow:0 10px 22px ' . $config['shadow'] . ';' : 'background:#e9ecef;border:2px dashed #adb5bd;'; ?>">
            <i class="fa-solid <?php echo esc_attr( $config['icon'] ); ?> gp-badge-fa-icon"></i>
            <?php if ( ! $earned ) : ?>
                <div class="gp-badge-lock-overlay">
                    <i class="fa-solid fa-lock"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status Badge -->
        <div class="gp-badge-status-pill mb-2">
            <?php if ( $is_current ) : ?>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 shadow-sm">
                    <i class="fa-solid fa-star me-1"></i> Current Rank
                </span>
            <?php elseif ( $earned ) : ?>
                <span class="badge bg-success text-white rounded-pill px-3 py-1 shadow-sm">
                    <i class="fa-solid fa-circle-check me-1"></i> Unlocked
                </span>
            <?php else : ?>
                <span class="badge bg-secondary text-light rounded-pill px-3 py-1">
                    <i class="fa-solid fa-lock me-1"></i> Locked
                </span>
            <?php endif; ?>
        </div>

        <!-- Rank Title -->
        <h4 class="gp-badge-title fw-bold text-dark mt-2 mb-1" style="font-size: 1.15rem;"><?php echo esc_html( $title ); ?></h4>

        <!-- Threshold indicator -->
        <div class="mb-2">
            <span class="badge bg-light text-dark border px-2 py-1 small">
                <i class="fa-solid fa-coins text-warning me-1"></i> <?php echo esc_html( $req_pts ); ?> Credits Required
            </span>
        </div>

        <!-- Rank Description / Excerpt -->
        <?php
        $excerpt = has_excerpt( $rank_id ) ? gamipress_get_post_field( 'post_excerpt', $rank_id ) : gamipress_get_post_field( 'post_content', $rank_id );
        if ( ! empty( $excerpt ) ) :
        ?>
            <div class="gp-badge-excerpt text-muted small mb-3" style="line-height: 1.4;">
                <?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?>
            </div>
        <?php endif; ?>

        <!-- Requirements -->
        <?php if ( $a['requirements'] === 'yes' && $requirements = gamipress_get_rank_requirements( $rank_id ) ) : ?>
            <div class="gp-badge-steps mt-auto pt-2 border-top">
                <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Requirements</span>
                <?php echo gamipress_get_rank_requirements_list_markup( $requirements, $rank_id, $user_id, $a ); ?>
            </div>
        <?php else : ?>
            <div class="gp-badge-steps mt-auto pt-2 border-top">
                <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Requirements</span>
                <div class="gp-pts-req-pill">
                    <i class="fa-solid fa-coins me-1"></i> Earn <?php echo esc_html( $req_pts ); ?> Credits
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
