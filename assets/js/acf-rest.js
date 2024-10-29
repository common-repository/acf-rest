(function($){

  // General selector for any acf input with the name "endpoint"
	var acfEndpointInputSelector = 'input[id ^= "acf_fields"][id $= "endpoint"]';
  var errorUrlDiv = '<div class="js_remove-dynamic-elements error invalid-url">Please enter a valid url</div>';
  var errorJsonDiv = '<div class="js_remove-dynamic-elements error invalid-json">There is no JSON file at the destination url or the file does not contain valid JSON</div>';

  $('.inside').on('keyup', acfEndpointInputSelector, function(event) {
    var fieldObject = $(event.target).closest('.acf-field-object');
    var fieldObjectId = fieldObject.data('id');

    var endpointInputSelector = '#acf_fields-' + fieldObjectId + '-endpoint';
    var selectDefaultSelector = '#acf_fields-' + fieldObjectId + '-default';

    fieldObject.find('#js_check-url-div-' + fieldObjectId) .remove();
    fieldObject.find('.js_remove-dynamic-elements').remove();
    var url = $.trim($(this).val());

    if (url !== '') {
      var checkAndRetrieveButtonDiv = '<div id="js_check-url-div-' + fieldObjectId + '" class="btn-container"> \
																			   <a id="js_check-url-' + fieldObjectId + '" class="js_btn-check-' + fieldObjectId + ' btn tmarg10">Check Url</a> \
																			 </div>';

      $(endpointInputSelector).after(checkAndRetrieveButtonDiv);

      $('.inside').on('click', '#js_check-url-' + fieldObjectId, function(event) {
      	event.preventDefault();
        fieldObject.find('.js_remove-dynamic-elements').remove();

        try {
          checkUrlAndPopulateSelect(url, selectDefaultSelector, endpointInputSelector, fieldObjectId);
        } catch (err) {
          $(endpointInputSelector).after(errorUrlDiv);
				}
			});
		} else {
      $('js_btn-check-' + fieldObjectId).remove();
    }
	});

  function checkUrlAndPopulateSelect(url, selectDefaultSelector, endpointInputSelector) {
    if (url !== '' && !isUrlValid(url)) {
      $(this).after(errorUrlDiv);
    } else {
      $.getJSON(url, function (data) {
        populateSelect(data.entry, selectDefaultSelector);

      }).fail(function () {
        $(endpointInputSelector).after(errorJsonDiv);
      });
    }
  }

  function populateSelect(data, selectDefaultSelector) {
    $(selectDefaultSelector + ' option').remove();
    $.each(data, function (index, value) {

      $(selectDefaultSelector).append($('<option>', {
        value: value['value'],
        text: value['identifier']
      }));
    });
  }

  function isUrlValid(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
  }

})(jQuery);