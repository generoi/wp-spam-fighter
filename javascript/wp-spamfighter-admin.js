/**
 * Wrapper function to safely use $
 */
function wpsfAdminWrapper($) {
    var wpsfAdmin = {

        /**
         * Main entry point
         */
        init: function () {
        }
    }; // end wpsfAdmin

    $(document).ready(wpsfAdmin.init);

} // end wpsfAdminWrapper()

wpsfAdminWrapper(jQuery);
