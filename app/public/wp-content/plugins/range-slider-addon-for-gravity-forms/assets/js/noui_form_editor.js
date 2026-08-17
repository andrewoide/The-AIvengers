jQuery(document).ready(function ($) {
    var GFNU_Field_Editor = {
        init: function () {
            GFNU_Field_Editor.form_editor_init();
        },

        form_editor_init: function () {

            //fieldSettings.text += ', .gfgeo_dynamic_api_fields, .gfgpa_google_api_field_ids';

            jQuery(document).bind(
                "gform_load_field_settings",
                function (event, field, form) {


                    

                    

                    // var formFields;
                    // // get form fields
                    // if (typeof form.fields != "undefined") {
                    //     formFields = form.fields;
                    // } else {
                    //     formFields = [];
                    // }

                    // jQuery(jQuery("select#gfgpa_google_api_field_ids").find("option:gt(0)").remove());

                    // jQuery(formFields).each(function () {
                    //     if (this.type == "gfgpa_api") {
                    //         jQuery(
                    //             jQuery(
                    //                 "select#gfgpa_google_api_field_ids"
                    //             ).append(
                    //                 jQuery("<option>")
                    //                     .attr("value", this.id)
                    //                     .text("Google API ID - " + this.id)
                    //             )
                    //         );
                    //     }
                    // });

                    // jQuery("#gfgpa_google_api_field_ids").val(field['gfgpaApiID']);

                    // jQuery("#gfgeo-address-autocomplete-types").val(field["gfgeo_address_autocomplete_types"]);
                    // jQuery("#gfgeo_dynamic_api_fields_value").val(field["dynamicAPIField"]);
                    // jQuery("#gfgpa_lalitude_value").val(field["gfgpaLatitude"]);
                    // jQuery("#gfgpa_longitude_value").val(field["gfgpaLongitude"]);
                    // jQuery("#gfgpa_enable_ac_value").attr("checked", field["gfgpaEnableAC"] == true);
                }
            );
        },
    };

    GFNU_Field_Editor.init();
});
