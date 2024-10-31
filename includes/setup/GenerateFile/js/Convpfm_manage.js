jQuery(function ($) {
  //jQuery(document).ready(function($) {
  var project_hash = null;
  var project_status = null;
  var get_value = null;
  var tab_value = null;

  
  url = new URL(window.location.href);
  if (url.searchParams.get("page")) {
    get_value = url.searchParams.get("page");
  }
  if (url.searchParams.get("tab")) {
    tab_value = url.searchParams.get("tab");
  }

  if (get_value == "convpfm-manage-file") {
    jQuery(function ($) {
      var nonce = $("#_wpnonce").val();

      jQuery
        .ajax({
          method: "POST",
          url: ajaxurl,
          dataType: "json",
          data: {
            action: "convpfm_check_processing",
            security: nonce,
          },
        })
        .done(function (data) {
          if (data.processing == "true") {
            myInterval = setInterval(convpfm_check_perc, 10000);
          } else {
            console.log("No refresh interval is needed, all feeds are ready");
          }
        })
        .fail(function (data) {
          console.log("Failed AJAX Call :( /// Return Data: " + data);
        });
    });
  }

  $("td[id=manage_inline]").find("div").parents("tr").hide();
  $(".checkbox-field").on("change", function (index, obj) {
    if (get_value == "convpfm-manage-file") {
      var nonce = $("#_wpnonce").val();
      project_hash = $(this).val();
      project_status = $(this).prop("checked");

      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_project_status",
          security: nonce,
          project_hash: project_hash,
          active: project_status,
        },
      });

      $("table tbody")
        .find('input[name="manage_record"]')
        .each(function () {
          var hash = this.value;
          if (hash == project_hash) {
            if (project_status == false) {
              $(this).parents("tr").addClass("strikethrough");
            } else {
              $(this).parents("tr").removeClass("strikethrough");
            }
          }
        });
    } else {
      // Do nothing, waste of resources
    }
  });

  $("td[id=manage_inline]").find("div").parents("tr").hide();
  $(".checkbox-field").on("change", function (index, obj) {
    var nonce = $("#_wpnonce").val();
    if (get_value == "class-manage-feed") {
      project_hash = $(this).val();
      project_status = $(this).prop("checked");

      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_project_status",
          security: nonce,
          project_hash: project_hash,
          active: project_status,
        },
      });

      $("table tbody")
        .find('input[name="manage_record"]')
        .each(function () {
          var hash = this.value;
          if (hash == project_hash) {
            if (project_status == false) {
              $(this).parents("tr").addClass("strikethrough");
            } else {
              $(this).parents("tr").removeClass("strikethrough");
            }
          }
        });
    } else {
      // Do nothing, waste of resources
    }
  });

  // Check if user would like to use mother image for variations
  $("#add_mother_image").on("change", function () {
    // on change of state
    var nonce = $("#_wpnonce").val();

    if (this.checked) {
      // Checkbox is on
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_add_mother_image",
          security: nonce,
          status: "on",
        },
      });
    } else {
      // Checkbox is off
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_add_mother_image",
          security: nonce,
          status: "off",
        },
      });
    }
  });

  // Check if user would like to add all country shipping costs
  $("#add_all_shipping").on("change", function () {
    // on change of state
    var nonce = $("#_wpnonce").val();
    if (this.checked) {
      // Checkbox is on
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_add_all_shipping",
          security: nonce,
          status: "on",
        },
      });
    } else {
      // Checkbox is off
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_add_all_shipping",
          security: nonce,
          status: "off",
        },
      });
    }
  });

  // Check if user would like the plugin to respect free shipping class
  $("#free_shipping").on("change", function () {
    // on change of state
    var nonce = $("#_wpnonce").val();
    if (this.checked) {
      // Checkbox is on
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_free_shipping",
          security: nonce,
          status: "on",
        },
      });
    } else {
      // Checkbox is off
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_free_shipping",
          security: nonce,
          status: "off",
        },
      });
    }
  });

  // Check if user would like the plugin to respect free shipping class
  $("#local_pickup_shipping").on("change", function () {
    // on change of state
    var nonce = $("#_wpnonce").val();
    if (this.checked) {
      // Checkbox is on
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_local_pickup_shipping",
          security: nonce,
          status: "on",
        },
      });
    } else {
      // Checkbox is off
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_local_pickup_shipping",
          security: nonce,
          status: "off",
        },
      });
    }
  });

  // Check if user would like the plugin to remove the free shipping class
  $("#remove_free_shipping").on("change", function () {
    // on change of state
    var nonce = $("#_wpnonce").val();
    if (this.checked) {
      // Checkbox is on
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_remove_free_shipping",
          security: nonce,
          status: "on",
        },
      });
    } else {
      // Checkbox is off
      jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          action: "convpfm_remove_free_shipping",
          security: nonce,
          status: "off",
        },
      });
    }
  });

  $(".actions").on("click", "span", function () {
    var id = $(this).attr("id");
    var idsplit = id.split("_");
    var project_hash = idsplit[1];
    var action = idsplit[0];
    var nonce = $("#_wpnonce").val();

    if (action == "gear") {
      $("tr")
        .not(":first")
        .click(function (event) {
          var $target = $(event.target);
          $target
            .closest("tr")
            .next()
            .find("div")
            .parents("tr")
            .toggle();
        });
    }

    if (action == "convpfm-copy") {
      var popup_dialog = confirm("Are you sure you want to copy this feed?");
      if (popup_dialog == true) {
        jQuery
          .ajax({
            method: "POST",
            url: ajaxurl,
            data: {
              action: "convpfm_project_copy",
              security: nonce,
              project_hash: project_hash,
            },
          })

          .done(function (data) {
            data = JSON.parse(data);
            $("#convpfm_main_table").append(
              '<tr class><td>&nbsp;</td><td colspan="5"><span>The plugin is creating a new product feed now: <b><i>"' +
                data.projectname +
                '"</i></b>. Please refresh your browser to manage the copied product feed project.</span></span></td></tr>'
            );
          });
      }
    }

    if (action == "convpfm-trash") {
      var popup_dialog = confirm("Are you sure you want to delete this feed?");
      if (popup_dialog == true) {
        jQuery.ajax({
          method: "POST",
          url: ajaxurl,
          data: {
            action: "convpfm_project_delete",
            security: nonce,
            project_hash: project_hash,
          },
        });

        $("table tbody")
          .find('input[name="manage_record"]')
          .each(function () {
            var hash = this.value;
            if (hash == project_hash) {
              $(this).parents("tr").remove();
            }
          });
      }
    }

    if (action == "convpfm-cancel") {
      var popup_dialog = confirm(
        "Are you sure you want to cancel processing the feed?"
      );
      if (popup_dialog == true) {
        jQuery.ajax({
          method: "POST",
          url: ajaxurl,
          data: {
            action: "convpfm_project_cancel",
            security: nonce,
            project_hash: project_hash,
          },
        });

        // Replace status of project to stop processing
        $("table tbody")
          .find('input[name="manage_record"]')
          .each(function () {
            var hash = this.value;
            if (hash == project_hash) {
              $(".woo-product-feed-pro-blink_" + hash).text(function () {
                $(this).addClass("woo-product-feed-pro-blink_me");
                return $(this).text().replace("ready", "stop processing");
              });
            }
          });
      }
    }

    if (action == "convpfm-refresh") {
      var popup_dialog = confirm(
        "Are you sure you want to refresh the product feed?"
      );
      if (popup_dialog == true) {
        jQuery.ajax({
          method: "POST",
          url: ajaxurl,
          data: {
            action: "convpfm_project_refresh",
            security: nonce,
            project_hash: project_hash,
          },
        });

        // Replace status of project to processing
        $("table tbody")
          .find('input[name="manage_record"]')
          .each(function () {
            var hash = this.value;
            if (hash == project_hash) {
              $(".woo-product-feed-pro-blink_off_" + hash).text(function () {
                $(this).addClass("woo-product-feed-pro-blink_me");
                var status = $(
                  ".woo-product-feed-pro-blink_off_" + hash
                ).text();
                myInterval = setInterval(convpfm_check_perc, 5000);
                if (status == "ready") {
                  return $(this).text().replace("ready", "processing (0%)");
                } else if (status == "stopped") {
                  return $(this).text().replace("stopped", "processing (0%)");
                } else if (status == "not run yet") {
                  return $(this)
                    .text()
                    .replace("not run yet", "processing (0%)");
                } else {
                  // it should not be coming here at all
                  return $(this).text().replace("ready", "processing (0%)");
                }
              });
            }
          });
      }
    }
  });

  function convpfm_check_perc() {
    // Check if we need to UP the processing percentage
    var nonce = $("#_wpnonce").val();

    $("table tbody")
      .find('input[name="manage_record"]')
      .each(function () {
        var hash = this.value;
        jQuery.ajax({
          method: "POST",
          url: ajaxurl,
          data: {
            action: "convpfm_project_processing_status",
            security: nonce,
            project_hash: hash,
          },
          success: function (data) {
            data = JSON.parse(data);

            if (data.proc_perc < 100) {
              if (data.running != "stopped") {
                $("#convpfm_proc_" + hash).addClass(
                  "woo-product-feed-pro-blink_me"
                );
                return $("#convpfm_proc_" + hash).text(
                  "processing (" + data.proc_perc + "%)"
                );
              }
            } else if (data.proc_perc == 100) {
              //	clearInterval(myInterval);
              $("#convpfm_proc_" + hash).removeClass(
                "woo-product-feed-pro-blink_me"
              );
              return $("#convpfm_proc_" + hash).text("ready");
            } else if (data.proc_perc == 999) {
              // Do not do anything
            } else {
              //	clearInterval(myInterval);
            }
          },
        });

        // Check if we can kill the refresh interval
        // Kill interval when all feeds are done processing
        jQuery
          .ajax({
            method: "POST",
            url: ajaxurl,
            data: {
              action: "convpfm_check_processing",
              security: nonce,
            },
          })
          .done(function (data) {
            data = JSON.parse(data);
            if (data.processing == "false") {
              clearInterval(myInterval);
              console.log("Kill interval, all feeds are ready");
            }
          })
          .fail(function (data) {
            console.log("Failed AJAX Call :( /// Return Data: " + data);
          });
      });
  }

});
