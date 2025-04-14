/**
 * Admin JavaScript for the plugin.
 */
;(($) => {
  $(document).ready(() => {
    // Initialize any JavaScript functionality here

    // Dismiss notices
    $(document).on("click", ".notice-dismiss", function () {
      $(this).closest(".notice").fadeOut()
    })

    // Confirm bulk actions
    /*$("#doaction, #doaction2").on("click", function () {
      var action = $(this).prev("select").val()

      if (action === "delete") {
        if (typeof lhg_activity_plugin !== "undefined") {
          return confirm(lhg_activity_plugin.confirm_delete)
        } else {
          return confirm("Are you sure you want to delete these items?")
        }
      }
    })*/
  })
})(jQuery)

