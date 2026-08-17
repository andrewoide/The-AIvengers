<?php
if (!defined('ABSPATH')) {
    exit;
}

$features = [
    [
        'feature'   => __('Number slider', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Single handle', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Calculation', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Tooltip', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Prefix', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Default value', 'range-slider-addon-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Dual handle', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Text slider', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Pips', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Separator', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('RTL support', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Connect with number field', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Show value under slider', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Filter hook', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Custom design', 'range-slider-addon-for-gravity-forms'),
        'pro'       => true
    ]
];

?>
<div id="pro" class="pro_introduction tab_item">
    <div class="content_heading">
        <h2><?php esc_html_e('Unlock the full power of Range Slider For Gravity Forms', 'range-slider-addon-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('The amazing PRO features will make your Range Slider even more efficient.', 'range-slider-addon-for-gravity-forms'); ?></p>
    </div>

    <div class="content_heading free_vs_pro">
        <h2>
            <span><?php esc_html_e('Free', 'range-slider-addon-for-gravity-forms'); ?></span>
            <?php esc_html_e('vs', 'range-slider-addon-for-gravity-forms'); ?>
            <span><?php esc_html_e('Pro', 'range-slider-addon-for-gravity-forms'); ?></span>
        </h2>
    </div>

    <div class="features_list">
        <div class="list_header">
            <div class="feature_title"><?php esc_html_e('Feature List', 'range-slider-addon-for-gravity-forms'); ?></div>
            <div class="feature_free"><?php esc_html_e('Free', 'range-slider-addon-for-gravity-forms'); ?></div>
            <div class="feature_pro"><?php esc_html_e('Pro', 'range-slider-addon-for-gravity-forms'); ?></div>
        </div>
        <?php foreach ($features as $feature) : ?>
            <div class="feature">
                <div class="feature_title"><?php echo esc_html($feature['feature']); ?></div>
                <div class="feature_free">
                    <?php if ($feature['pro']) : ?>
                        <i class="dashicons dashicons-no-alt"></i>
                    <?php else : ?>
                        <i class="dashicons dashicons-saved"></i>
                    <?php endif; ?>
                </div>
                <div class="feature_pro">
                    <i class="dashicons dashicons-saved"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (rsfgf_fs()->is_not_paying()) : ?>
        <div class="pro-cta background_pro">
            <div class="cta-content">
                <h2><?php esc_html_e('Don\'t waste time, get the PRO version now!', 'range-slider-addon-for-gravity-forms'); ?></h2>
                <p><?php esc_html_e('Upgrade to the PRO version of the plugin and unlock all the amazing Range Slider features for
                your website.', 'range-slider-addon-for-gravity-forms'); ?></p>
            </div>
            <div class="cta-btn">
                <a href="<?php echo esc_url(rsfgf_fs()->get_upgrade_url()); ?>" class="pcafe_btn"><?php esc_html_e('Upgrade Now', 'range-slider-addon-for-gravity-forms'); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <div class="pro-cta background_free">
        <div class="cta-content">
            <h2><?php esc_html_e('Want to try live demo, before purchase?', 'range-slider-addon-for-gravity-forms'); ?></h2>
            <p><?php esc_html_e('Try our instant ready-made demo with form submission! If you use an active email address, you\'ll also receive a notification.', 'range-slider-addon-for-gravity-forms'); ?></p>
        </div>
        <div class="cta-btn">
            <a href="https://pluginscafe.com/demo/range-slider-for-gravity-forms/" target="_blank" class="pcafe_btn"><?php esc_html_e('Try Live Demo', 'range-slider-addon-for-gravity-forms'); ?></a>
        </div>
    </div>
</div>