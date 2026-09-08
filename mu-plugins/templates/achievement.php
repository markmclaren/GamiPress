<?php
/**
 * Custom GamiPress Achievement Template with Font Awesome Icons & Vibrant Styling
 */

global $gamipress_template_args;
$a = $gamipress_template_args;

$achievement_id = get_the_ID();
$user_id        = isset( $a['user_id'] ) ? absint( $a['user_id'] ) : get_current_user_id();
$earned         = gamipress_has_user_earned_achievement( $achievement_id, $user_id );
$post           = get_post( $achievement_id );
$slug           = $post ? $post->post_name : '';
$title          = get_the_title( $achievement_id );

// Map badges to Font Awesome 6 icons and vibrant gradients
$badge_config = array(
    'badge-welcome' => array(
        'icon'     => 'fa-rocket',
        'gradient' => 'linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%)',
        'shadow'   => 'rgba(255, 65, 108, 0.35)',
    ),
    'badge-conversationalist' => array(
        'icon'     => 'fa-comments',
        'gradient' => 'linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)',
        'shadow'   => 'rgba(0, 198, 255, 0.35)',
    ),
    'badge-regular-visitor' => array(
        'icon'     => 'fa-fire',
        'gradient' => 'linear-gradient(135deg, #f857a6 0%, #ff5858 100%)',
        'shadow'   => 'rgba(248, 87, 166, 0.35)',
    ),
    'badge-author' => array(
        'icon'     => 'fa-pen-nib',
        'gradient' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'shadow'   => 'rgba(17, 153, 142, 0.35)',
    ),
    'badge-high-roller' => array(
        'icon'     => 'fa-crown',
        'gradient' => 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)',
        'shadow'   => 'rgba(247, 151, 30, 0.35)',
    ),
);

$config = $badge_config[ $slug ] ?? array(
    'icon'     => 'fa-award',
    'gradient' => 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)',
    'shadow'   => 'rgba(142, 45, 226, 0.35)',
);

$classes = array(
    'gamipress-achievement',
    'gp-badge-card',
    $earned ? 'user-has-earned badge-unlocked' : 'user-has-not-earned badge-locked',
);
?>

<div id="gamipress-achievement-<?php echo esc_attr( $achievement_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
    
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
            <?php if ( $earned ) : ?>
                <span class="badge bg-success text-white rounded-pill px-3 py-1 shadow-sm">
                    <i class="fa-solid fa-circle-check me-1"></i> Earned
                </span>
            <?php else : ?>
                <span class="badge bg-secondary text-light rounded-pill px-3 py-1">
                    <i class="fa-solid fa-lock me-1"></i> Locked
                </span>
            <?php endif; ?>
        </div>

        <!-- Badge Title -->
        <h4 class="gp-badge-title fw-bold text-dark mt-2 mb-1" style="font-size: 1.15rem;"><?php echo esc_html( $title ); ?></h4>

        <!-- Badge Description / Excerpt -->
        <?php
        $excerpt = has_excerpt( $achievement_id ) ? gamipress_get_post_field( 'post_excerpt', $achievement_id ) : gamipress_get_post_field( 'post_content', $achievement_id );
        if ( ! empty( $excerpt ) ) :
        ?>
            <div class="gp-badge-excerpt text-muted small mb-3" style="line-height: 1.4;">
                <?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?>
            </div>
        <?php endif; ?>

        <!-- Requirements / Steps -->
        <?php if ( $a['steps'] === 'yes' && $steps = gamipress_get_achievement_steps( $achievement_id ) ) : ?>
            <div class="gp-badge-steps mt-auto pt-2 border-top">
                <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">How to Earn</span>
                <?php echo gamipress_get_required_achievements_for_achievement_list_markup( $steps, $achievement_id, $user_id, $a ); ?>
            </div>
        <?php endif; ?>

        <!-- Earned by Points requirement if applicable -->
        <?php
        $earned_by = get_post_meta( $achievement_id, '_gamipress_earned_by', true );
        if ( $earned_by === 'points' ) :
            $pts_req  = get_post_meta( $achievement_id, '_gamipress_points_required', true );
            $pts_type = get_post_meta( $achievement_id, '_gamipress_points_type_required', true );
        ?>
            <div class="gp-badge-steps mt-auto pt-2 border-top">
                <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">How to Earn</span>
                <div class="gp-pts-req-pill">
                    <i class="fa-solid fa-coins me-1"></i> Earn <?php echo esc_html( $pts_req ); ?> <?php echo esc_html( ucfirst( $pts_type ) ); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
