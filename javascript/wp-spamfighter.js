/**
 * Wrapper function to safely use $
 */
function wpsfWrapper($) {
    var wpsf = {

        /**
         * Main entry point
         */
        init: function () {
            //Timestamp protection
            if (window.wpsf_timestamp_enabled) {
                $('#commentform, #setupform, #registerform').append('<input type="hidden" name="wpsfTS1" id="wpsfTS1" value="1" />');
                $('#commentform, #setupform, #registerform').append('<input type="hidden" name="wpsfTS2" id="wpsfTS2" value="1" />');

                $('#wpsfTS1').val((new Date).getTime());
            }
            //Not a spammer protection
            if (window.wpsf_not_a_spammer_enabled) {
                var wpsf_checkbox = $("<input>").attr("type", "checkbox").attr("id", "wpsf_not_a_spammer").attr("name", "wpsf_not_a_spammer");
                var wpsf_label = $("<label />").text(window.not_a_spammer_label);
                $("#wpsf_p").append(wpsf_label);
                wpsf_label.append(wpsf_checkbox);
            }
            //No Captcha reCaptcha protection
            if (window.wpsf_recaptcha_enabled) {
                var wpsf_recaptcha = $("<div>").attr("class", "g-recaptcha").attr("data-sitekey", window.captcha_site_key);
                $("#wpsf_p").append(wpsf_recaptcha);
            }
            $('#commentform, #setupform, #registerform').submit(validateCommentForm);

        }
    }; // end wpsf

    $(document).ready(wpsf.init);

} // end wpsfWrapper()

wpsfWrapper(jQuery);

/**
 * Checks the user input and issues an error message if required.
 * Returns true if the checks were fine and false if an error message was displayed.
 *
 * @returns {boolean}
 */
function validateCommentForm() {
    //Timestamp protection
    if (window.wpsf_timestamp_enabled) {
        var wpsfTS1 = document.getElementById("wpsfTS1").value;
        var wpsfTS2 = (new Date).getTime();
        document.getElementById("wpsfTS2").value = wpsfTS2;
        var diff = wpsfTS2 - wpsfTS1;
        var remaining = Math.round((window.wpsf_threshold - diff) / 1000);
        if (diff <= window.wpsf_threshold) {
            alert(window.wpsf_message.format([Math.round(window.wpsf_threshold / 1000), remaining]));
            return false;
        }
    }
    //Not a spammer protection
    if (window.wpsf_not_a_spammer_enabled) {
        var wpsf_checkbox = document.getElementById("wpsf_not_a_spammer");
        if (wpsf_checkbox.checked != true) {
            alert(window.not_a_spammer_user_message);
            return false;
        }
    }
    //JavaScript protection
    if (window.wpsf_javascript_enabled) {
        jQuery("<input>").attr("type", "hidden").attr("name", "wpsf_javascript").attr("value", "WPSF_JAVASCRIPT_TOKEN").appendTo('#commentform, #setupform, #registerform');
    }

    return true;
}
