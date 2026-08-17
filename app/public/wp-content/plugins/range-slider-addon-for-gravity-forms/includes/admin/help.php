<?php
if (!defined('ABSPATH')) {
    exit;
}

$faqs = [
    [
        'question' => __('How many sliders can be added to a form?', 'range-slider-addon-for-gravity-forms'),
        'answer' => __('You can add multiple slider fields in a single form, but the same form cannot be used twice on the same page. For example, if you have added form ID 1 on the contact page, you cannot use the same form ID 1 again on the same page.', 'range-slider-addon-for-gravity-forms'),
    ],
    [
        'question' => __('Does this range slider plugin support calculations in the number and product fields of Gravity Forms?', 'range-slider-addon-for-gravity-forms'),
        'answer' => __('Yes, it does support calculations. Currently, you won\'t find the merge tag in the calculation merge tag option, so you\'ll need to enter the merge tag manually. For example, if you\'re using the range slider in Field ID 5, in the free version, you\'ll need to use {slider:5}, and in the pro version, use {slider:5.1} for a single slider and {slider:5.2} for a double slider.', 'range-slider-addon-for-gravity-forms'),
    ],
    [
        'question' => __('Does the range slider support conditional logic?', 'range-slider-addon-for-gravity-forms'),
        'answer' => __('Yes, it does support.', 'range-slider-addon-for-gravity-forms'),
    ]
];

?>


<div id="help" class="help_introduction tab_item">
    <div class="content_heading">
        <h2><?php esc_html_e('Frequently Asked Questions', 'range-slider-addon-for-gravity-forms'); ?></h2>
    </div>

    <section class="section_faq">
        <?php foreach ($faqs as $key => $faq) : ?>
            <div class="faq_item">
                <input type="checkbox" name="accordion-1" id="faq<?php echo esc_attr($key); ?>">
                <label for="faq<?php echo esc_attr($key); ?>" class="faq__header">
                    <?php echo esc_html($faq['question']); ?>
                    <i class="dashicons dashicons-arrow-down-alt2"></i>
                </label>
                <div class="faq__body">
                    <p><?php echo esc_html($faq['answer']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="content_heading">
        <h2><?php esc_html_e('Need Help?', 'range-slider-addon-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('If you have any questions or need help, please feel free to contact us.', 'range-slider-addon-for-gravity-forms'); ?></p>
    </div>

    <div class="help_docs">
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GF_NU_RANGE_SLIDER_URL . '/assets/images/docs.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Documentation', 'range-slider-addon-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('Check out our detailed online documentation and video tutorials to find out more about what you can do.', 'range-slider-addon-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://pluginscafe.com/docs/range-slider-for-gravity-forms/" class="pcafe_btn"><?php esc_html_e('Documentation', 'range-slider-addon-for-gravity-forms'); ?></a>
            </div>
        </section>
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GF_NU_RANGE_SLIDER_URL . '/assets/images/service247.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Support', 'range-slider-addon-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('We have dedicated support team to provide you fast, friendly & top-notch customer support.', 'range-slider-addon-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://wordpress.org/support/plugin/range-slider-addon-for-gravity-forms/" class="pcafe_btn"><?php esc_html_e('Get Support', 'range-slider-addon-for-gravity-forms'); ?></a>
            </div>
        </section>
    </div>
</div>