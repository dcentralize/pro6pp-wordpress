// Avoid library conflicts.
var $ = jQuery.noConflict();

// Create closure to keep namespace clean and hide implementation.
(function($) {
    "use strict";
    var NL_SIXPP_REGEX = /^([0-9]{4,4})(\s)?([a-zA-Z]{2,2})$/;
    $.fn.applyAutocomplete = function(options) {
        var parent_obj = this;

        var form = new FieldManipulations(options);
        parent_obj.country = $(options.country);
        parent_obj.postcode = $(options.postcode);
        parent_obj.streetnumber = $(options.streetnumber);
        parent_obj.street = $(options.street);
        parent_obj.city = $(options.city);
        parent_obj.prefix = "#" + options.prefix;
        // parent_obj.municipality = $(options.municipality);
        parent_obj.province = $(options.province);

        parent_obj.country.change(function() {
            var country = $(parent_obj.country.selector + " :selected").val()
                    .trim();
            if ($.inArray(country, pro6pp.countries) !== -1) {
                // Change the ffield order.
                form.apply();
                parent_obj.postcode.bind("keyup", function() {
                    autocomplete(parent_obj);
                });
                parent_obj.streetnumber.bind("blur", function() {
                    autocomplete(parent_obj);
                });
                fieldsLock(parent_obj, 'apply');
            } else {
                parent_obj.postcode.unbind("keyup");
                parent_obj.streetnumber.unbind("blur");
                resetFields(parent_obj, 'all');
                fieldsLock(parent_obj, 'release');
                form.reset();
            }
        });
        parent_obj.country.trigger("change");
    };

    /**
     * Handles the lock and release of the form fields. Accepts a second
     * parameter to determine what actions to perform.
     *
     * @param {object}
     *            obj: The object holding the form fields.
     * @param {string}
     *            action: A string defining what action to perform. Supports
     *            "release" and "apply".
     */
    function fieldsLock(obj, action) {
        if (action === 'release') {
            obj.postcode.removeAttr('autocomplete');
            obj.postcode.removeAttr('maxlength');
            obj.street.removeAttr('readonly');
            // obj.municipality.removeAttr('readonly');
            obj.city.removeAttr('readonly');
            obj.province.removeAttr('readonly');
        } else if (action === 'apply') {
            obj.postcode.attr('autocomplete', 'off');
            obj.postcode.attr('maxlength', '7');
            obj.street.attr('readonly', 'readonly');
            // obj.municipality.attr('readonly', 'readonly');
            obj.city.attr('readonly', 'readonly');
            obj.province.attr('readonly', 'readonly');
            obj.spinner = $(obj.prefix + 'pro6pp_spinner');
            obj.message = $(obj.prefix + 'pro6pp_message');
            obj.spinner.hide();
            obj.message.hide();
        } else {
            return;
        }
    }

    /**
     * Cache object.
     */
    var pro6pp_cache = {};

    /**
     * Searches the response in the cache. If not found, it will make a request
     * to the service and save the response in the cache for later use. If the
     * response is an error it will show and error and release all fields. If
     * the response is successful, it will call the callback function.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     * @param {string}
     *            url: The url to request a response from.
     * @param {Object}
     *            params: The data to send along with the request.
     * @param {function}
     *            callback: The function to call on successfull response.
     */
    function pro6pp_cached_get(obj, url, params, callback) {
        var key = url + params.nl_sixpp + params.streetnumber;
        if (pro6pp_cache.hasOwnProperty(key)) {
            callback(obj, pro6pp_cache[key]);
        } else {
            $.ajax({
                crossDomain : true,
                type : 'GET',
                dataType : 'json',
                timeout : parseInt(pro6pp.timeout, 10) * 1000,
                url : pro6pp.url,
                data : params,
                success : function(data, textStatus, jqXHR) {
                    if (!data || textStatus !== "success") {
                        callback(obj, {
                            status : "error",
                            error : {
                                message : pro6pp.serviceDown
                            }
                        });
                        fieldsLock(obj, 'release');
                        return;
                    }
                    pro6pp_cache[key] = data;
                    callback(obj, data);
                }
            });
        }
    }

    /**
     * Empties the values of the autocompleted fields. If a second parameter is
     * passed, It will reset all fields controled by this script.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     * @param {undefined}
     *            all : Any value that can evaluate to true.
     */
    function resetFields(obj, all) {
        if (all) {
            obj.postcode.val('');
            obj.streetnumber.val('');
            if (typeof obj.message !== "undefined")
                obj.message.hide();
        }
        obj.street.val('');
        obj.city.val('');
        // obj.municipality.val('');
        obj.province.val('');
        if (typeof obj.spinner !== "undefined")
            obj.spinner.hide();
    }

    /**
     * Ensures if the given input was correct and requests the response.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     */
    function autocomplete(obj) {
        var postcode = obj.postcode.val();
        var streetnumber = obj.streetnumber.val();
        obj.message.hide();
        // Trigger on '5408xb' and on '5408 xb'
        if (!isValidPostcode(obj) || !isValidStreetNumber(obj)) {
            resetFields(obj);
        } else {
            obj.spinner.show();
            var params = {
                action : pro6pp.action,
                nl_sixpp : encodeURIComponent(postcode),
                streetnumber : encodeURIComponent(streetnumber)
            };
            var url = pro6pp.url;
            pro6pp_cached_get(obj, url, params, fillin);
        }
    }

    /**
     * Checks if the postcode value is valid. If not it displays an error.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     */
    function isValidPostcode(obj) {
        var postcode = obj.postcode.val();
        var noSpace = postcode.replace(/(\s|-)/gi, '');
        if (noSpace.length) {
            if (noSpace.length === 6) {
                if (NL_SIXPP_REGEX.test(postcode))
                    return true;
                else
                    obj.message.empty().append(
                            '<li><abbr title="only spaces are permitted">'
                                    + pro6pp.invalidPostcode + '</abbr></li>');
                obj.message.show();
            }
        }
        return false;
    }

    /**
     * Checks if the streetnumber is valid. If not, it displays the error.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     */
    function isValidStreetNumber(obj) {
        var streetnumber = obj.streetnumber.val();
        var regex = /^\d+(\s)?(-|\s)?(\s)?([a-z]{0,3})$/i;
        if (streetnumber.length >= 1) {
            if (regex.test(streetnumber))
                return true;
            else
                obj.message.empty().append(
                        '<li>' + pro6pp.invalidStreetnumber + '</li>');
            obj.message.show();
        }
        return false;
    }

    /**
     * The callback that handles the response.
     *
     * @param {object}
     *            obj: The object that holds the form fields.
     * @param {object}
     *            json: A JSON object containing the service response.
     */
    function fillin(obj, json) {
        obj.spinner.hide();
        if (json.status === 'ok') {
            if (json.results.length >= 1) {
                obj.street.val(json.results[0].street);
                obj.city.val(json.results[0].city);
                // obj.municipality.val(json.results[0].municipality);
                // Re-select province field.
                obj.province = $(obj.province.selector);
                obj.province.val(json.results[0].province);
                obj.message.hide();
            }
        } else {
            var translated_message = json.error.message;
            if (json.error.message === 'nl_sixpp not found') {
                translated_message = 'Onbekende postcode';
            } else if (json.error.message === 'invalid postcode format') {
                translated_message = 'Ongeldig postcode formaat';
            } else if (json.error.message === 'auth_key has expired') {
                translated_message = json.error.message;
                fieldsLock(obj, 'release');
            } else if (json.error.message === "An error occured, "
                    + "please contact the site's administrator.") {
                translated_message = json.error.message;
                fieldsLock(obj, 'release');
            }

            resetFields(obj);
            obj.message.empty().append('<li>' + translated_message + '</li>');
            obj.message.show();
        }
    }
})($);

/**
 * Wire the plugin when the page loads.
 */
jQuery(document).ready(function() {
    var $ = jQuery.noConflict();

    if (typeof billing_fields != "undefined") {
        $(billing_fields.scope).applyAutocomplete(billing_fields);
    }
    if (typeof shipping_fields != "undefined") {
        $(shipping_fields.scope).applyAutocomplete(shipping_fields);
    }
});
