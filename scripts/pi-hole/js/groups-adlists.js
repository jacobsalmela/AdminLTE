/* Pi-hole: A black hole for Internet advertisements
 *  (c) 2017 Pi-hole, LLC (https://pi-hole.net)
 *  Network-wide ad blocking via your own hardware.
 *
 *  This file is copyright under the latest version of the EUPL.
 *  Please see LICENSE file for your rights under this license. */

/* global utils:false */

var table;
var groups = [];
var token = $("#token").html();

function get_groups() {
  $.post(
    "scripts/pi-hole/php/groups.php",
    { action: "get_groups", token: token },
    function(data) {
      groups = data.data;
      initTable();
    },
    "json"
  );
}

$(document).ready(function() {
  $("#btnAdd").on("click", addAdlist);

  get_groups();
});

function initTable() {
  table = $("#adlistsTable").DataTable({
    ajax: {
      url: "scripts/pi-hole/php/groups.php",
      data: { action: "get_adlists", token: token },
      type: "POST"
    },
    order: [[0, "asc"]],
    columns: [
      { data: "id", visible: false },
      { data: "address" },
      { data: "enabled", searchable: false },
      { data: "comment" },
      { data: "groups", searchable: false },
      { data: null, width: "80px", orderable: false }
    ],
    drawCallback: function() {
      $('button[id^="deleteAdlist_"]').on("click", deleteAdlist);
      $('body > [id^="container_"]').each(function () { $(this).remove(); });
    },
    rowCallback: function(row, data) {
      $(row).attr("data-id", data.id);
      var tooltip =
        "Added: " +
        utils.datetime(data.date_added) +
        "\nLast modified: " +
        utils.datetime(data.date_modified) +
        "\nDatabase ID: " +
        data.id;
      $("td:eq(0)", row).html(
        '<code id="address_' + data.id + '" title="' + tooltip + '">' + data.address + "</code>"
      );

      var disabled = data.enabled === 0;
      $("td:eq(1)", row).html(
        '<input type="checkbox" id="status_' + data.id + '"' + (disabled ? "" : " checked") + ">"
      );
      var statusEl = $("#status_" + data.id, row);
      statusEl.bootstrapToggle({
        on: "Enabled",
        off: "Disabled",
        size: "small",
        onstyle: "success",
        width: "80px"
      });
      statusEl.on("change", editAdlist);

      $("td:eq(2)", row).html('<input id="comment_' + data.id + '" class="form-control">');
      var commentEl = $("#comment_" + data.id, row);
      commentEl.val(data.comment);
      commentEl.on("change", editAdlist);

      $("td:eq(3)", row).empty();
      $("td:eq(3)", row).append(
        '<div id="selectHome_' +
          data.id +
          '">' +
          '<select id="multiselect_' +
          data.id +
          '" multiple="multiple"></select></div>'
      );
      var selectEl = $("#multiselect_" + data.id, row);
      // Add all known groups
      for (var i = 0; i < groups.length; i++) {
        var extra = "";
        if (!groups[i].enabled) {
          extra = " (disabled)";
        }

        selectEl.append(
          $("<option />")
            .val(groups[i].id)
            .text(groups[i].name + extra)
        );
      }

      // Select assigned groups
      selectEl.val(data.groups);
      // Initialize multiselect
      selectEl.multiselect({
        includeSelectAllOption: true,
        buttonContainer: '<div id="container_' + data.id + '" class="btn-group"/>',
        maxHeight: 200,
        onDropdownShown: function() {
          var el = $("#container_" + data.id);
          var top = el[0].getBoundingClientRect().top;
          var bottom = $(window).height() - top - el.height();
          if (bottom < 200) {
            el.addClass("dropup");
          }

          if (bottom > 200) {
            el.removeClass("dropup");
          }

          var offset = el.offset();
          $("body").append(el);
          el.css("position", "absolute");
          el.css("top", offset.top + "px");
          el.css("left", offset.left + "px");
        },
        onDropdownHide: function() {
          var el = $("#container_" + data.id);
          var home = $("#selectHome_" + data.id);
          home.append(el);
          el.removeAttr("style");
        }
      });
      selectEl.on("change", editAdlist);

      var button =
        '<button class="btn btn-danger btn-xs" type="button" id="deleteAdlist_' +
        data.id +
        '">' +
        '<span class="glyphicon glyphicon-trash"></span>' +
        "</button>";
      $("td:eq(4)", row).html(button);
    },
    dom:
      "<'row'<'col-sm-4'l><'col-sm-8'f>>" +
      "<'row'<'col-sm-12'<'table-responsive'tr>>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"]
    ],
    stateSave: true,
    stateSaveCallback: function(settings, data) {
      // Store current state in client's local storage area
      localStorage.setItem("groups-adlists-table", JSON.stringify(data));
    },
    stateLoadCallback: function() {
      // Receive previous state from client's local storage area
      var data = localStorage.getItem("groups-adlists-table");
      // Return if not available
      if (data === null) {
        return null;
      }

      data = JSON.parse(data);
      // Always start on the first page to show most recent queries
      data.start = 0;
      // Always start with empty search field
      data.search.search = "";
      // Reset visibility of ID column
      data.columns[0].visible = false;
      // Apply loaded state to table
      return data;
    }
  });

  table.on("order.dt", function() {
    var order = table.order();
    if (order[0][0] !== 0 || order[0][1] !== "asc") {
      $("#resetButton").show();
    } else {
      $("#resetButton").hide();
    }
  });
  $("#resetButton").on("click", function() {
    table.order([[0, "asc"]]).draw();
    $("#resetButton").hide();
  });
}

function addAdlist() {
  var address = $("#new_address").val();
  var comment = $("#new_comment").val();

  utils.disableAll();
  utils.showAlert("info", "", "Adding adlist...", address);

  if (address.length === 0) {
    utils.showAlert("warning", "", "Warning", "Please specify an adlist address");
    return;
  }

  $.ajax({
    url: "scripts/pi-hole/php/groups.php",
    method: "post",
    dataType: "json",
    data: {
      action: "add_adlist",
      address: address,
      comment: comment,
      token: token
    },
    success: function(response) {
      utils.enableAll();
      if (response.success) {
        utils.showAlert(
          "success",
          "glyphicon glyphicon-plus",
          "Successfully added adlist",
          address
        );
        $("#new_address").val("");
        $("#new_comment").val("");
        table.ajax.reload();
      } else {
        utils.showAlert("error", "", "Error while adding new adlist: ", response.message);
      }
    },
    error: function(jqXHR, exception) {
      utils.enableAll();
      utils.showAlert("error", "", "Error while adding new adlist: ", jqXHR.responseText);
      console.log(exception);
    }
  });
}

function editAdlist() {
  var elem = $(this).attr("id");
  var tr = $(this).closest("tr");
  var id = tr.attr("data-id");
  var status = tr.find("#status_" + id).is(":checked") ? 1 : 0;
  var comment = tr.find("#comment_" + id).val();
  var groups = tr.find("#multiselect_" + id).val();
  var address = tr.find("#address_" + id).text();

  var done = "edited";
  var not_done = "editing";
  switch (elem) {
    case "status_" + id:
      if (status === 0) {
        done = "disabled";
        not_done = "disabling";
      } else if (status === 1) {
        done = "enabled";
        not_done = "enabling";
      }

      break;
    case "comment_" + id:
      done = "edited comment of";
      not_done = "editing comment of";
      break;
    case "multiselect_" + id:
      done = "edited groups of";
      not_done = "editing groups of";
      break;
    default:
      alert("bad element or invalid data-id!");
      return;
  }

  utils.disableAll();
  utils.showAlert("info", "", "Editing adlist...", address);

  $.ajax({
    url: "scripts/pi-hole/php/groups.php",
    method: "post",
    dataType: "json",
    data: {
      action: "edit_adlist",
      id: id,
      comment: comment,
      status: status,
      groups: groups,
      token: token
    },
    success: function(response) {
      utils.enableAll();
      if (response.success) {
        utils.showAlert(
          "success",
          "glyphicon glyphicon-pencil",
          "Successfully " + done + " adlist ",
          address
        );
      } else {
        utils.showAlert(
          "error",
          "",
          "Error while " + not_done + " adlist with ID " + id,
          Number(response.message)
        );
      }
    },
    error: function(jqXHR, exception) {
      utils.enableAll();
      utils.showAlert(
        "error",
        "",
        "Error while " + not_done + " adlist with ID " + id,
        jqXHR.responseText
      );
      console.log(exception);
    }
  });
}

function deleteAdlist() {
  var tr = $(this).closest("tr");
  var id = tr.attr("data-id");
  var address = tr.find("#address_" + id).text();

  utils.disableAll();
  utils.showAlert("info", "", "Deleting adlist...", address);
  $.ajax({
    url: "scripts/pi-hole/php/groups.php",
    method: "post",
    dataType: "json",
    data: { action: "delete_adlist", id: id, token: token },
    success: function(response) {
      utils.enableAll();
      if (response.success) {
        utils.showAlert(
          "success",
          "glyphicon glyphicon-trash",
          "Successfully deleted adlist ",
          address
        );
        table
          .row(tr)
          .remove()
          .draw(false);
      } else {
        utils.showAlert("error", "", "Error while deleting adlist with ID " + id, response.message);
      }
    },
    error: function(jqXHR, exception) {
      utils.enableAll();
      utils.showAlert("error", "", "Error while deleting adlist with ID " + id, jqXHR.responseText);
      console.log(exception);
    }
  });
}
