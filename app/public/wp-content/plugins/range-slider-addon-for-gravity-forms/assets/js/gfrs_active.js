"use strict";

var $j = jQuery.noConflict();

$j(document).bind("gform_post_render", function (event, form_id) {
    var rangeData = window["gfrsData_" + form_id];

    if (!rangeData) {
        return;
    }

    var getFieldsData = rangeData.elements;

    $j.each(getFieldsData, function (index, data) {
        var value = jQuery.parseJSON(data);

        if( ! $j("#gfrs_rangeslider_"+ value['id']).hasClass('noUi-target') ) {

            var inputId = jQuery("#input_"+ form_id + "_" + value['id']);
            var start = [ inputId.val() ];
            var slider = document.getElementById(`gfrs_rangeslider_${value['id']}`);

            var options = {
                start: start,
                connect: 'lower',
                range: {
                    'min': parseInt(value['rangeValueMin']),
                    'max': parseInt(value['rangeValueMax']),
                },
                format: wNumb({
                    decimals: 0,
                    thousand: ',',
                    prefix: value['valuePrefixGField']
                }),
                tooltips: [ wNumb({
                    decimals: 0,
                    thousand: ',',
                    prefix: value['valuePrefixGField']
                })],
            }

            if( value['showValueGField'] != "" ) {
                options.tooltips = true;
            } else {
                options.tooltips = false;
            }

            if( value['rangeValueStep'] != "" ) {
                options.step = parseInt(value['rangeValueStep']);
            } else {
                options.step = 1;
            }

            noUiSlider.create(slider, options);

            slider.noUiSlider.on('update', function (values, handle) {
                var minValue = inputId.val();
                var value = values[handle];
                if( ! handle ) {
                    minValue = value;
                }

                jQuery(inputId).val(minValue).trigger("change");

            });
        }
    });
});
