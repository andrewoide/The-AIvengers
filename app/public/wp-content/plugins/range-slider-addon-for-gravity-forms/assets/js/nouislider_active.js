"use strict";

jQuery(document).bind("gform_load_field_settings", function(event, field, form){

    var slider = document.getElementById('gfrs_rangeslider_3'),
        inputNumber = document.getElementById('input_3');

    var start = [inputNumber.value.replace( /[^\d.]/g, '' )];

    if( ! jQuery("#gfrs_rangeslider_3").hasClass('noUi-target') ) {

        noUiSlider.create(slider, {
            start: start,
            connect: true,
            tooltips: true,
            range: {
                'min': parseInt(field["nuSliderMinGField"]),
                'max': parseInt(field["nuSliderMaxGField"])
            }
        });

    }

    var minValue = inputNumber.value;

    slider.noUiSlider.on('update', function (values, handle) {
        
        var value = values[handle];

        if( ! handle ) {
            minValue = value;
        }

        inputNumber.value = minValue;
        // var eventClick = new Event('change');
        // inputNumber.dispatchEvent(eventClick);

    });

});
