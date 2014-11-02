/**
 * Collection class. Reorders form fields in HTML DOM.
 *
 * @param {object}
 *            The field selectors.
 * @todo Give the class a more descriptive name.
 * @uses jQuery.
 * @uses The pro6pp object, generated from PHP.
 */
function FieldManipulations(fields) {
    "use strict";
    // Avoid conflict between libraries/scripts.
    var $ = jQuery.noConflict();
    // Get the elements by selector. Accepts XPaths also.
    var company = $(fields.company + '_field');
    var country = $(fields.country + '_field');
    var postcode = $(fields.postcode + '_field');
    var streetNr = $(fields.streetnumber + '_field');
    var street = $(fields.street);
    var province = $(fields.province + '_field');
    var prefix = fields.prefix;
    // Use of css selector
    var streetNrId = $(fields.streetnumber);
    var url = fields.pluginUrl;

    /**
     * Rearrange the order by moving the fields in the DOM.
     */
    var reArrange = function() {
        company.after(country);
        country.after(postcode);
        postcode.after(streetNr);
    };

    /**
     * Manipulate the style attributes
     */
    var changeStyle = function() {
        // Postcode field.
        postcode.removeClass("form-row-wide");
        postcode.removeClass("form-row-last");
        postcode.addClass("form-row-first");
        // Streetnumber field.
        streetNr.removeClass("form-row-wide");
        streetNr.addClass("form-row-last");
        streetNr.addClass("validate-required");
    };

    /**
     * Inserts an ajax loader image and a message box (for error messages) to
     * the form.
     */
    var addExtraFields = function() {
        var spinner = $("<img>", {
            id : prefix + "pro6pp_spinner",
            src : pro6pp.spinnerSrc,
            class : "img ",
            style : "display:none"
        });

        /*
         * Add the spinner underneath the postcode field, while keeping the
         * same parent div.
         */
        postcode.append(spinner);

        var message = $("<ul>", {
            id : prefix + "pro6pp_message",
            class : "woocommerce-error",
            style : "margin-top: 10px; display:none",
        });

        /**
         * <pre>
         * Error message box is placed above the postcode and streetnumber
         * fields (under the country field).
         * Otherwise the box will be either &quot;stack&quot; on top of the
         * postcode field, or the streetnumber field will not be aligned
         * properly.
         * </pre>
         */
        postcode.before(message);
    };

    /**
     * Insert a label element in the DOM. The label is meant for a required
     * field, thus, inserts also a tooltip for that label and removes the
     * placeholder attribute of the labeled field.
     */
    var addStreetNrLabel = function() {
        // Get the street number id without the selector's identifier.
        var id = streetNrId.selector.substring(1);
        // Use a translated text for the required tooltip, derived from a
        // woocommerce class. Default to English otherwise.
        var required = typeof woocommerce_params.i18n_required_text !== "undefined" ? woocommerce_params.i18n_required_text
                : 'required';
        var tip = '<abbr title="' + required + '" '
                + 'class="required"> * </abbr>';
        // Add a label before streetnumber input if none exists.
        if (!$('label[for="' + id + '"]').length)
            streetNr.prepend('<label class="" for="' + id + '">'
                    + pro6pp.streetnumber + tip + '</label>');
        // Add the "required" indicator if none.
        else if (!$('label[for="' + id + '"]>abbr').length) {
            $('label[for="' + id + '"]').append(tip);
        }
        // Remove unwanted attributes
        streetNrId.removeAttr('placeholder');
    };

    /**
     * Change the province fields input field to a text-box. This is done to
     * ensure that even if the user has provided woocomerce with provinces, the
     * pro6pp plugin will override them.
     */
    var addProvinceTextField = function() {
        // Target the raw DOM attribute.
        province[0].innerHTML = '<label for="' + prefix
                + 'state" class="">Province</label>'
                + '<input type="text" class="input-text" name="' + prefix
                + 'state" id="' + prefix
                + 'state" value="" autocomplete="off" '
                + 'placeholder="State / County">';
        // Apply styling rules.
        province.removeAttr('style');
        province.removeClass('form-row-first');
        province.addClass('form-row-wide');
    };

    /**
     * Public function to initialise the reordering.
     */
    this.apply = function() {
        reArrange();
        changeStyle();
        addStreetNrLabel();
        addProvinceTextField();
        addExtraFields();
    };

    /**
     * Public function to revert the order to the (almost) initial state.
     */
    this.reset = function() {
        // Reset the streetnumber field on country change.
        street.after(streetNr);
        streetNr.removeClass("form-row-last");
        streetNr.add("form-row-wide");
        // Postcode field is reseted by the wooCommerce script.
        postcode.removeClass("form-row-last");
        postcode.removeClass("form-row-first");
        postcode.addClass("form-row-wide");
    };
}
