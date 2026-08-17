<?php
if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('GFForms'))
    die();

class GF_Field_NURSlider extends GF_Field {

    public $type = 'nurslider';


    public function get_form_editor_field_title() {
        return (esc_attr__('Range Slider', 'range-slider-addon-for-gravity-forms'));
    }

    public function get_form_editor_field_settings() {
        return array(
            'conditional_logic_field_setting',
            'prepopulate_field_setting',
            'error_message_setting',
            'label_setting',
            'admin_label_setting',
            'rules_setting',
            'duplicate_setting',
            'description_setting',
            'css_class_setting',
        );
    }

    public function is_conditional_logic_supported() {
        return true;
    }

    public function get_field_input($form, $value = '', $entry = null) {

        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();
        $is_admin        = $is_entry_detail || $is_form_editor;

        $form_id  = $form['id'];
        $id       = intval($this->id);
        $field_id = $is_admin || $form_id == 0  ? "input_$id" : 'input_' . $form_id . "_$id";

        $size          = $this->size;
        $disabled_text = $is_form_editor ? "disabled='disabled'" : '';
        $class_suffix  = $is_entry_detail ? '_admin' : '';
        $class         = $this->type . ' ' . $size . $class_suffix;
        $invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

        $min        = isset($this->rangeValueMin) && '' != $this->rangeValueMin ? $this->rangeValueMin : 1;
        $max        = isset($this->rangeValueMax) && '' != $this->rangeValueMax ? $this->rangeValueMax : 100;
        $prefix     = isset($this->valuePrefixGField) && '' != $this->valuePrefixGField ? $this->valuePrefixGField : "$";
        $showValue  = isset($this->showValueGField) && '' != $this->showValueGField ? $this->showValueGField : "";
        $step       = isset($this->rangeValueStep) && '' != $this->rangeValueStep ? $this->rangeValueStep : 1;

        if ('' == $value) {
            $value = isset($this->rangeDValueGField) && '' != $this->rangeDValueGField ? $this->rangeDValueGField : ($min + $max) / 2;
        }

        $input = '<div class="ginput_container ginput_container_nurslider">';

        $input .= '<div id="gfrs_rangeslider_' . $id . '"></div>';

        $input .= '<input type="hidden" name="input_' . $id . '" id="' . $field_id . '" value="' . esc_attr($value) . '" class="' . $class . '" ' . $invalid_attribute . ' />';

        $input .= '</div>';

        $script = $this->gfrs_admin_script($id, $min, $max, $prefix, $showValue, $step);

        if (is_admin()) $input .= "<script>$script</script>";

        return $input;
    }

    public function gfrs_admin_script($id, $min, $max, $prefix, $showValue, $step) {

        $script = "
            function gfrs_range_slider_init_$id(){
                var slider = document.getElementById('gfrs_rangeslider_$id'),
                inputNumber = document.getElementById('input_$id'),
                start = [inputNumber.value.replace( /[^\d.]/g, '' )];

                var options = {
                    start: start,
                    connect: 'lower',
                    tooltips: false,
                    range: {
                        'min': $min,
                        'max': $max
                    },
                    format: wNumb({
                        decimals: 0,
                        thousand: ',',
                        prefix: '$prefix'
                    })
                };

                if( $step != '' ) {
                    options.step = $step;
                } else {
                    options.step = 1;
                }

                noUiSlider.create(slider, options )
            }
            document.onreadystatechange = gfrs_range_slider_init_$id();
        ";

        return $script;
    }


    public function get_value_merge_tag($value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br) {
        return GFCommon::format_number($value, $this->numberFormat);
    }

    public function get_value_submission($field_values, $get_from_post_global_var = true) {

        $value = $this->get_input_value_submission('input_' . $this->id, $this->inputName, $field_values, $get_from_post_global_var);

        if ($this->numberFormat == 'currency') {
            require_once(GFCommon::get_base_path() . '/currency.php');
            $currency = new RGCurrency(GFCommon::get_currency());
            $value    = $currency->to_number($value);
        } else if ($this->numberFormat == 'decimal_comma') {
            $value = GFCommon::clean_number($value, 'decimal_comma');
        } else if ($this->numberFormat == 'decimal_dot') {
            $value = GFCommon::clean_number($value, 'decimal_dot');
        }

        return $value;
    }


    public function validate($value, $form) {

        $cleaned_value = GFCommon::clean_number($value, 'decimal_dot');

        if ($this->isRequired && rgblank($cleaned_value)) {
            // If it's required and empty, fail validation.
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('This field is required.', 'range-slider-addon-for-gravity-forms') : $this->errorMessage;
        } else if (! rgblank($value) && !is_numeric($cleaned_value)) {
            // If it's not a valid number, fail validation.
            $this->failed_validation  = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please enter a valid number.', 'range-slider-addon-for-gravity-forms') : $this->errorMessage;
        }
    }

    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {
        return $value;
    }


    public function is_value_submission_array() {
        return false;
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {

        return GFCommon::format_number($value, $this->numberFormat);
    }

    public function get_value_entry_list($value, $entry, $field_id, $columns, $form) {

        return GFCommon::format_number($value, $this->numberFormat);
    }
}


GF_Fields::register(new GF_Field_NURSlider());
