<?php
if (!defined('ABSPATH')) {
    exit;
}

class GF_Range_Slider_Menu {
    public function __construct() {
        add_filter('admin_footer_text', [$this, 'admin_footer'], 1, 2);
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('admin_notices', [$this, 'review_request']);
        add_action('admin_notices', [$this, 'upgrade_notice']);
        add_action('admin_notices', [$this, 'offer_admin_notice']);
        add_action('wp_ajax_gfrs_offer_notice_dismiss', [$this, 'gfrs_offer_notice_dismiss']);
        add_action('wp_ajax_rs_review_dismiss', [$this, 'rs_review_dismiss']);
        add_action('wp_ajax_upgrade_notice_dismiss', [$this, 'upgrade_notice_dismiss']);
    }

    public function admin_scripts() {
        $current_screen = get_current_screen();
        if (strpos($current_screen->base, 'range-slider-for-gravity-forms') === false) {
            return;
        }

        wp_enqueue_style('gfrs_admin_style', GF_NU_RANGE_SLIDER_URL . 'assets/css/gfrs_admin.css', array(), GF_NU_RANGE_SLIDER_ADDON_VERSION);
        wp_enqueue_script('gfrs_admin_js', GF_NU_RANGE_SLIDER_URL . 'assets/js/gfrs_admin.js', array('jquery'), GF_NU_RANGE_SLIDER_ADDON_VERSION, true);
    }

    public function add_menu() {
        add_submenu_page(
            'options-general.php',
            'Range Slider for Gravity Forms',
            'GF Range Slider',
            'administrator',
            'range-slider-for-gravity-forms',
            [$this, 'range_slider_page']
        );
    }

    public function range_slider_page() {
        echo '<div class="pcafe_wrapper">';
        include_once __DIR__ . '/dashboard.php';

        echo '<div id="pcafe_tab_box" class="pcafe_container">';
        include_once __DIR__ . '/introduction.php';
        include_once __DIR__ . '/usage.php';
        include_once __DIR__ . '/help.php';
        include_once __DIR__ . '/pro.php';
        include_once __DIR__ . '/other-plugins.php';
        echo '</div>';
        echo '</div>';
    }

    public function admin_footer($text) {
        global $current_screen;

        if (! empty($current_screen->id) && strpos($current_screen->id, 'range-slider-for-gravity-forms') !== false) {
            $url  = 'https://wordpress.org/support/plugin/range-slider-addon-for-gravity-forms/reviews/?#new-post';
            $text = sprintf(
                wp_kses(
                    /* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
                    __('Thank you for using %1$s. Please rate us <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to boost our motivation.', 'range-slider-addon-for-gravity-forms'),
                    array(
                        'a' => array(
                            'href'   => array(),
                            'target' => array(),
                            'rel'    => array(),
                        ),
                    )
                ),
                '<strong>Range Slider Addon For Gravity Forms</strong>',
                $url,
                $url
            );
        }

        return $text;
    }

    public function review_request() {
        if (! is_super_admin()) {
            return;
        }

        $time = time();
        $load = false;

        $review = get_option('gfrs_review_status');

        if (! $review) {
            $review_time = strtotime("+15 days", time());
            update_option('gfrs_review_status', $review_time);
        } else {
            if (! empty($review) && $time > $review) {
                $load = true;
            }
        }
        if (! $load) {
            return;
        }

        $this->review();
    }

    public function review() {
        $nonce = wp_create_nonce('gfrs_review_nonce');
        $current_user = wp_get_current_user();
?>
        <div class="notice notice-info is-dismissible gfrs_review_notice" data-nonce="<?php echo esc_attr($nonce); ?>">
            <p><?php
                echo wp_kses_post(
                    sprintf(
                        /* translators: 1: user display name 2: plugin name */
                        __(
                            'Hey %1$s 👋, I noticed you are using <strong>%2$s</strong> for a few days - that\'s Awesome!  
            If you feel <strong>%2$s</strong> is helping your business to grow in any way, could you please do us a BIG favor and give it a 5-star rating on WordPress to boost our motivation?',
                            'range-slider-addon-for-gravity-forms'
                        ),
                        esc_html($current_user->display_name),
                        'Range Slider Addon For Gravity Forms'
                    )
                );
                ?>
            </p>
            <ul style="margin-bottom: 5px">
                <li style="display: inline-block">
                    <a style="padding: 5px 5px 5px 0; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/range-slider-addon-for-gravity-forms/reviews/?#new-post') ?>">
                        <span class="dashicons dashicons-external"></span><?php esc_html_e(' Ok, you deserve it!', 'range-slider-addon-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="rs_already_done" data-status="already">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('I already did', 'range-slider-addon-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="rs_later" data-status="rs_later">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Maybe Later', 'range-slider-addon-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://pluginscafe.com/support/') ?>">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e('I need help', 'range-slider-addon-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="rs_never" data-status="rs_never">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Never show again', 'range-slider-addon-for-gravity-forms') ?>
                    </a>
                </li>
            </ul>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.rs_already_done, .rs_later, .rs_never, .rs_notice_dismiss', function(event) {
                    event.preventDefault();
                    var $this = $(this);
                    var status = $this.attr('data-status');
                    var nonce = $this.parent().parent().parent().attr('data-nonce');

                    var data = {
                        action: 'rs_review_dismiss',
                        status: status,
                        nonce: nonce
                    };

                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: data,
                        success: function(data) {
                            $('.gfrs_review_notice').remove();
                        },
                        error: function(data) {}
                    });
                });
            });
        </script>
        <?php
    }

    public function rs_review_dismiss() {
        check_ajax_referer('gfrs_review_nonce', 'nonce');

        $status = '';
        if (isset($_POST['status'])) {
            $status = sanitize_text_field(wp_unslash($_POST['status']));
        }

        if ($status == 'already' || $status == 'rs_never') {
            $next_try     = strtotime("+30 days", time());
            update_option('gfrs_review_status', $next_try);
        } else if ($status == 'rs_later') {
            $next_try     = strtotime("+10 days", time());
            update_option('gfrs_review_status', $next_try);
        }
        wp_die();
    }

    public function upgrade_notice() {

        $show = false;
        if (rsfgf_fs()->is_not_paying()) {
            $show = true;
        }

        if (isset($_GET['show_notices'])) {
            delete_transient('gfrs_upgrade_notice');
            $show = true;
        }

        if (! $this->is_active_gravityforms()) { ?>
            <div id="gfrs_notice-error" class="gfrs_notice-error notice notice-error">
                <div class="notice-container" style="padding:10px">
                    <span> <?php esc_html_e("Range Slider AddOn needs to active gravity forms.", "range-slider-addon-for-gravity-forms"); ?></span>
                </div>
            </div>
            <?php
        } else {
            if ($show && false == get_transient('gfrs_upgrade_notice') && current_user_can('install_plugins')) {
            ?>

                <div id="gfrs_upgrade_notice" class="gfrs_upgrade_notice notice is-dismissible">
                    <div class="notice_container">
                        <div class="notice_wrap">
                            <div class="rda_img">
                                <img width="100px" src="<?php echo esc_url(GF_NU_RANGE_SLIDER_URL . '/assets/images/range-slider.svg'); ?>" class="gfrs_logo">
                            </div>
                            <div class="notice-content">
                                <div class="notice-heading">
                                    <?php esc_html_e("Hi there, Thanks for using Range Slider Addon for Gravity Forms", "range-slider-addon-for-gravity-forms"); ?>
                                </div>
                                <?php esc_html_e("Did you know our PRO version includes the ability to use text slider, double handle and more features? Check it out!", "range-slider-addon-for-gravity-forms"); ?>
                                <div class="gfrs_review-notice-container">
                                    <a href="https://pluginscafe.com/demo/range-slider-for-gravity-forms/" class="gfrs_notice-close gfrs_review-notice button-primary" target="_blank">
                                        <?php esc_html_e("See The Demo", "range-slider-addon-for-gravity-forms"); ?>
                                    </a>
                                    <span class="dashicons dashicons-smiley"></span>
                                    <a href="#" class="gfrs_notice-close notice-dis gfrs_review-notice">
                                        <?php esc_html_e("Dismiss", "range-slider-addon-for-gravity-forms"); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="gfrs_upgrade_btn">
                            <a href="<?php echo esc_url(rsfgf_fs()->get_upgrade_url()); ?>" target="_blank">
                                <?php esc_html_e('Upgrade Now!', 'range-slider-addon-for-gravity-forms'); ?>
                            </a>
                        </div>
                    </div>
                    <style>
                        .notice_container {
                            display: flex;
                            align-items: center;
                            padding: 10px 0;
                            gap: 15px;
                            justify-content: space-between;
                        }

                        img.gfrs_logo {
                            max-width: 90px;
                        }

                        .notice-heading {
                            font-size: 16px;
                            font-weight: 500;
                            margin-bottom: 5px;
                        }

                        .gfrs_review-notice-container {
                            margin-top: 11px;
                            display: flex;
                            align-items: center;
                        }

                        .gfrs_notice-close {
                            padding-left: 5px;
                        }

                        span.dashicons.dashicons-smiley {
                            padding-left: 15px;
                        }

                        .notice_wrap {
                            display: flex;
                            align-items: center;
                            gap: 15px;
                        }

                        .gfrs_upgrade_btn a {
                            text-decoration: none;
                            font-size: 15px;
                            background: #7BBD02;
                            color: #fff;
                            display: inline-block;
                            padding: 10px 20px;
                            border-radius: 3px;
                            transition: 0.3s;
                        }

                        .gfrs_upgrade_btn a:hover {
                            background: #69a103;
                        }
                    </style>
                    <script>
                        var $ = jQuery;
                        var admin_url_rda = '<?php echo esc_url(admin_url("admin-ajax.php")); ?>';
                        jQuery(document).on("click", '#gfrs_upgrade_notice .notice-dis', function() {
                            $(this).parents('#gfrs_upgrade_notice').find('.notice-dismiss').click();
                        });
                        jQuery(document).on("click", '#gfrs_upgrade_notice .notice-dismiss', function() {

                            var notice_id = $(this).parents('#gfrs_upgrade_notice').attr('id') || '';

                            jQuery.ajax({
                                url: admin_url_rda,
                                type: 'POST',
                                data: {
                                    action: 'upgrade_notice_dismiss',
                                    notice_id: notice_id,
                                },
                            });
                        });
                    </script>
                </div>

            <?php
            }
        }
    }

    public function upgrade_notice_dismiss() {
        $notice_id = isset($_POST['notice_id']) ? sanitize_key($_POST['notice_id']) : '';
        $repeat_notice_after = 60 * 60 * 24 * 7;
        if (!empty($notice_id)) {
            if (!empty($repeat_notice_after)) {
                set_transient($notice_id, true, $repeat_notice_after);
                wp_send_json_success();
            }
        }
    }

    public function is_active_gravityforms() {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return false;
        }
        return true;
    }

    public function gfrs_offer_notice_dismiss() {
        check_ajax_referer('gfrs_offer_dismiss_nonce', 'nonce');
        set_transient('gfrs_offer_notice_arrived', true, 3 * DAY_IN_SECONDS);
        wp_send_json_success();
    }

    public function offer_admin_notice() {
        $nonce = wp_create_nonce('gfrs_offer_dismiss_nonce');
        $ajax_url = admin_url('admin-ajax.php');

        $api_offer_notice = 'gfrs_offer_notice';
        $notice_array = get_transient($api_offer_notice);
        $is_offer_checked = get_transient('gfrs_offer_notice_arrived');

        $allowed_tags = [
            'strong' => ['style' => []],
            'code' => [],
            'a'      => [
                'href'   => [],
                'title'  => [],
                'target' => [],
                'rel'    => [],
            ],
            'span'   => ['style' => []],
        ];


        if ($notice_array === false) {
            // Fetch from remote only if cache expired
            $endpoint  = 'https://api.pluginscafe.com/wp-json/pcafe/v1/offers?id=3';
            $response  = wp_remote_get($endpoint, array('timeout' => 10));

            if (!is_wp_error($response) && $response['response']['code'] === 200) {
                $notice_array = json_decode($response['body'], true);

                // Save in cache for 3 hours (change as needed)
                set_transient($api_offer_notice, $notice_array, 3 * HOUR_IN_SECONDS);
            }
        }

        if (!empty($notice_array) && isset($notice_array['notice']) && $notice_array['live'] === true && $is_offer_checked === false) {
            $notice_type = $notice_array['notice']['notice_type'] ? $notice_array['notice']['notice_type'] : 'info';
            $notice_class = "notice-{$notice_type}";
            ?>
            <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible gfrs_offer_notice" data-ajax-url="<?php echo esc_url($ajax_url); ?>"
                data-nonce="<?php echo esc_attr($nonce); ?>">
                <div class="gfrs_notice_container" style="display: flex;align-items:center;padding:10px 0;justify-content:space-between;gap:15px;">
                    <div class="gfrs_notice_content" style="display: flex;align-items:center;gap:15px;">
                        <?php if ($notice_array['notice']['image']) : ?>
                            <div class="gfrs_notice_img">
                                <img width="90px" src="<?php echo esc_url($notice_array['notice']['image']); ?>" />
                            </div>
                        <?php endif; ?>
                        <div class="gfrs_notice_text">
                            <h3 style="margin:0 0 6px;"><?php echo esc_html($notice_array['notice']['title']); ?></h3>
                            <p><?php echo wp_kses($notice_array['notice']['content'], $allowed_tags); ?></p>
                            <div class="gfrs_notice_buttons" style="display: flex; gap:15px;align-items:center;">
                                <?php if ($notice_array['notice']['show_demo_url'] === true) : ?>
                                    <a href="https://pluginscafe.com/plugin/range-slider-for-gravity-forms-pro/" class="button-primary" target="__blank"><?php esc_html_e('Check Demo', 'range-slider-addon-for-gravity-forms'); ?></a>
                                <?php endif; ?>
                                <a href="#" class="gfrs_dismis_api__notice">
                                    <?php esc_html_e('Dismiss', 'range-slider-addon-for-gravity-forms'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php if ($notice_array['notice']['upgrade_btn'] === true) : ?>
                        <div class="gfrs_upgrade_btn">
                            <a href="<?php echo esc_url(rsfgf_fs()->get_upgrade_url()); ?>" style="text-decoration: none;font-size: 15px;background: #7BBD02;color: #fff;display: inline-block;padding: 10px 20px;border-radius: 3px;">
                                <?php echo esc_html($notice_array['notice']['upgrade_btn_text']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.gfrs_dismis_api__notice, .gfrs_offer_notice .notice-dismiss', function(event) {
                        event.preventDefault();
                        const $notice = jQuery(this).closest('.gfrs_offer_notice');
                        const ajaxUrl = $notice.data('ajax-url');
                        const nonce = $notice.data('nonce');

                        $.ajax({
                            url: ajaxUrl,
                            type: 'post',
                            data: {
                                action: 'gfrs_offer_notice_dismiss',
                                nonce: nonce
                            },
                            success: function(response) {
                                $('.gfrs_offer_notice').remove();
                            },
                            error: function(data) {}
                        });
                    });
                });
            </script>
<?php

        }
    }
}

new GF_Range_Slider_Menu();
