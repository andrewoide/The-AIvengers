<?php
if (!defined('ABSPATH')) {
	exit;
}
GFForms::include_addon_framework();

class GFRangeSliderAddOn extends GFAddOn {

	protected $_version = GF_NU_RANGE_SLIDER_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gf-range-slider';
	protected $_path = 'range-slider-for-gravityforms/gf-range-slider.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Range Slider AddOn Gravity Forms';
	protected $_short_title = 'Range Slider Add-On';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFRangeSliderAddOn
	 */
	public static function get_instance() {
		if (self::$_instance == null) {
			self::$_instance = new GFRangeSliderAddOn();
		}

		return self::$_instance;
	}


	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

		add_filter('gform_tooltips', array($this, 'gfrs_add_tooltips'));
		add_action('gform_editor_js', array($this, 'gfrs_editor_script'));
		add_filter('gform_custom_merge_tags', array($this, 'gfrs_slider_calculation_merge_tags'), 10, 4);
		add_action('gform_editor_js_set_default_values', array($this, 'gfrs_rangeslider_set_defaults'));
		add_action('gform_enqueue_scripts', array($this, 'gfrs_frontend_enqueue_scripts'), 10, 2);
		if (GFRS_GF_MIN_2_5) {
			add_filter('gform_field_settings_tabs', array($this, 'gfrs_fields_settings_tab'), 10, 2);
			add_action('gform_field_settings_tab_content_nurange_tab', array($this, 'gfrs_fields_settings_tab_content'), 10, 2);
		} else {
			add_action('gform_field_advanced_settings', array($this, 'gfrs_advanced_settings'), 10, 2);
		}
	}


	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gfrs_nouislider',
				'src'     => $this->get_base_url() . '/../../assets/js/nouislider.min.js',
				'version' => $this->_version,
				'deps'    => array('jquery'),
				'enqueue'  => array(
					array('field_types' => array('nurslider')),
					array('admin_page' => array('form_editor')),
				)
			),
			array(
				'handle'  => 'gfrs_wnumb',
				'src'     => $this->get_base_url() . '/../../assets/js/wNumb.js',
				'version' => $this->_version,
				'deps'    => array('jquery'),
				'enqueue'  => array(
					array('field_types' => array('nurslider')),
					array('admin_page' => array('form_editor')),
				)
			),
			array(
				'handle'  => 'gfgpa_editor',
				'src'     => $this->get_base_url() . '/../../assets/js/noui_form_editor.js',
				'version' => $this->_version,
				'deps'    => array('jquery'),
				'enqueue'  => array(
					array('admin_page' => array('form_editor')),
				)
			)
		);

		return array_merge(parent::scripts(), $scripts);
	}

	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gfrs_nouislider',
				'src'     => $this->get_base_url() . '/../../assets/css/nouislider.css',
				'version' => $this->_version,
				'enqueue' => array(
					array('field_types' => array('nurslider')),
					array('admin_page' => array('form_editor'))
				)
			)
		);

		return array_merge(parent::styles(), $styles);
	}


	public function gfrs_fields_settings_tab($tabs, $form) {
		$tabs[] = array(
			// Define the unique ID for your tab.
			'id'             => 'nurange_tab',
			// Define the title to be displayed on the toggle button your tab.
			'title'          => 'Range Slider',
			// Define an array of classes to be added to the toggle button for your tab.
			'toggle_classes' => array('gfrs_toggle_1', 'gfrs_toggle_2'),
			// Define an array of classes to be added to the body of your tab.
			'body_classes'   => array('nuslider_toggle_class'),
		);

		return $tabs;
	}

	public function gfrs_fields_settings_tab_content($form) {
?>

		<li class="gravitycafe_range_slider field_setting">
			<p style="margin: 0 0 2px;"><?php esc_html_e("Range options", "range-slider-addon-for-gravity-forms"); ?></p>
			<ul class="range_data" style="display:flex; flex-wrap:wrap; gap:20px;">
				<li class="rangeValueMin field_setting">
					<input type="number" name="rangeValueMin" id="rangeValueMin" onChange="SetFieldProperty('rangeValueMin', this.value);">
					<label for="rangeValueMin">
						<?php esc_html_e("Min", "range-slider-addon-for-gravity-forms"); ?>
						<?php gform_tooltip("gfrs_min"); ?>
					</label>
				</li>
				<li class="rangeValueStep field_setting">
					<input type="number" name="rangeValueStep" id="rangeValueStep" onChange="SetFieldProperty('rangeValueStep', this.value);">
					<label for="rangeValueStep">
						<?php esc_html_e("Step", "range-slider-addon-for-gravity-forms"); ?>
						<?php gform_tooltip("gfrs_step"); ?>
					</label>
				</li>
				<li class="rangeValueMax field_setting">
					<input type="number" name="rangeValueMax" id="rangeValueMax" onChange="SetFieldProperty('rangeValueMax', this.value);">
					<label for="rangeValueMax">
						<?php esc_html_e("Max", "range-slider-addon-for-gravity-forms"); ?>
						<?php gform_tooltip("gfrs_max"); ?>
					</label>
				</li>
			</ul>
		</li>

		<li class="rangeDValueGField field_setting">
			<label for="field_admin_label" class="inline">
				<?php esc_html_e("Default Value", "range-slider-addon-for-gravity-forms"); ?>
				<?php gform_tooltip("defaultValueGField"); ?>
			</label>
			<input type="text" id="rangeDValueGField" name="rangeDValueGField" onChange="SetFieldProperty('rangeDValueGField', this.value);" />
		</li>
		<li class="showValueGField field_setting">
			<label for="field_admin_label" class="inline">
				<?php esc_html_e("Show Value", "range-slider-addon-for-gravity-forms"); ?>
				<?php gform_tooltip("showValueGField"); ?>
			</label>
			<select name="showValueGField" id="showValueGField" onChange="SetFieldProperty('showValueGField', this.value);">
				<option value="">Hidden</option>
				<option value="show">Show</option>
			</select>
		</li>
		<li class="valuePrefixGField field_setting">
			<label for="valuePrefixGField" class="inline">
				<?php esc_html_e("Prefix", "range-slider-addon-for-gravity-forms"); ?>
				<?php gform_tooltip("gfrs_prefix"); ?>
			</label>
			<input type="text" id="valuePrefixGField" name="valuePrefixGField" onChange="SetFieldProperty('valuePrefixGField', this.value);" />
		</li>
		<style>
			.range_data li {
				width: calc(33.33% - 15px);
				margin-bottom: 0;
			}

			.range_data li input {
				width: 100%;
				max-height: 2.25rem;
				min-height: 2.25rem;
			}

			.range_data li label {
				margin: 2px 0 0 0;
			}
		</style>
	<?php
	}

	public function gfrs_advanced_settings($position, $form_id) {
		if ($position == 550) {
			$this->gfrs_fields_settings_tab_content(GFAPI::get_form($form_id));
		}
	}

	function gfrs_editor_script() {
	?>

		<script type='text/javascript'>
			//adding setting to fields of type "date"

			fieldSettings.nurslider += ", .gravitycafe_range_slider";
			fieldSettings.nurslider += ", .rangeValueMin";
			fieldSettings.nurslider += ", .rangeValueStep";
			fieldSettings.nurslider += ", .rangeValueMax";
			fieldSettings.nurslider += ", .showValueGField";
			fieldSettings.nurslider += ", .valuePrefixGField";
			fieldSettings.nurslider += ", .rangeDValueGField";

			jQuery(document).bind("gform_load_field_settings", function(event, field, form) {
				jQuery("#rangeValueMin").val(field["rangeValueMin"]);
				jQuery("#rangeValueStep").val(field["rangeValueStep"]);
				jQuery("#rangeValueMax").val(field["rangeValueMax"]);
				jQuery("#showValueGField").val(field["showValueGField"]);
				jQuery("#valuePrefixGField").val(field["valuePrefixGField"]);
				jQuery("#rangeDValueGField").val(field["rangeDValueGField"]);
			});
		</script>

	<?php
	}

	function gfrs_rangeslider_set_defaults() {
	?>
		case "nurslider" :
		field.label = "Range Slider";
		field.rangeValueMin = 1;
		field.rangeValueMax = 100;
		field.rangeValueStep = 1;
		field.showValueGField = "show";
		field.valuePrefixGField = "$";
		field.defaultValueGField = "50";
		break;
<?php
	}

	function gfrs_slider_calculation_merge_tags($merge_tags, $form_id, $fields, $element_id) {

		// check the type of merge tag dropdown
		if ('field_calculation_formula' != $element_id) {
			return $merge_tags;
		}

		foreach ($fields as $field) {

			// check the field type as we only want to generate merge tags for list fields
			if ('nurslider' != $field->get_input_type()) {
				continue;
			}

			$merge_tags[] = array('label' => $field->label, 'tag' => '{' . $field->label . ':' . $field->id . '}');
		}

		return $merge_tags;
	}

	function gfrs_frontend_enqueue_scripts($form, $is_ajax) {

		$form_id = $form['id'];
		$fields_data = [];

		foreach ($form['fields'] as $field) {
			if ($field->type === "nurslider") {
				$form = (array) GFFormsModel::get_form_meta($field->formId);
				$fields_data[] = json_encode(GFFormsModel::get_field($form, $field->id));
			}
		}

		if (count($fields_data) === 0) {
			return;
		}

		wp_enqueue_script('gfrs_data', $this->get_base_url() . '/../../assets/js/gfrs_active.js', array('jquery'), $this->_version, true);
		wp_localize_script(
			'gfrs_data',
			'gfrsData_' . $form_id,
			array(
				'elements' =>  $fields_data
			)
		);
	}

	function gfrs_add_tooltips() {
		$tooltips['gfrs_min'] = esc_html__("Type minimum value in number.", "range-slider-addon-for-gravity-forms");
		$tooltips['showValueGField'] = esc_html__("You can hide and show range slider value.", "range-slider-addon-for-gravity-forms");
		$tooltips['gfrs_prefix'] = esc_html__("Add prefix for showing in tooltips before number.", "range-slider-addon-for-gravity-forms");
		$tooltips['gfrs_max'] = esc_html__("Type maximum value in number", "range-slider-addon-for-gravity-forms");
		$tooltips['gfrs_step'] = esc_html__("Type step in number", "range-slider-addon-for-gravity-forms");
		$tooltips['defaultValueGField'] = esc_html__("Type default value", "range-slider-addon-for-gravity-forms");

		return $tooltips;
	}
}
