

$(function () {
  "use strict";


  /* scrollar */

  new PerfectScrollbar(".notify-list")

  new PerfectScrollbar(".search-content")

  // new PerfectScrollbar(".mega-menu-widgets")



  /* toggle button */

  $(".btn-toggle").click(function () {
    $("body").hasClass("toggled") ? ($("body").removeClass("toggled"), $(".sidebar-wrapper").unbind("hover")) : ($("body").addClass("toggled"), $(".sidebar-wrapper").hover(function () {
      $("body").addClass("sidebar-hovered")
    }, function () {
      $("body").removeClass("sidebar-hovered")
    }))
  })




  /* menu */

  $(function () {
    //$('#sidenav').metisMenu();
  });

  $(".sidebar-close").on("click", function () {
    $("body").removeClass("toggled")
  })



  /* dark mode button */

  $(".dark-mode i").click(function () {
    $(this).text(function (i, v) {
      return v === 'dark_mode' ? 'light_mode' : 'dark_mode'
    })
  });


  $(".dark-mode").click(function () {
    $("html").attr("data-bs-theme", function (i, v) {
      return v === 'dark' ? 'light' : 'dark';
    })
  })


  /* sticky header */

  $(document).ready(function () {
    $(window).on("scroll", function () {
      if ($(this).scrollTop() > 60) {
        $('.top-header .navbar').addClass('sticky-header');
      } else {
        $('.top-header .navbar').removeClass('sticky-header');
      }
    });
  });


  /* email */

  $(".email-toggle-btn").on("click", function() {
    $(".email-wrapper").toggleClass("email-toggled")
  }), $(".email-toggle-btn-mobile").on("click", function() {
    $(".email-wrapper").removeClass("email-toggled")
  }), $(".compose-mail-btn").on("click", function() {
    $(".compose-mail-popup").show()
  }), $(".compose-mail-close").on("click", function() {
    $(".compose-mail-popup").hide()
  }), 


  /* chat */

  $(".chat-toggle-btn").on("click", function() {
    $(".chat-wrapper").toggleClass("chat-toggled")
  }), $(".chat-toggle-btn-mobile").on("click", function() {
    $(".chat-wrapper").removeClass("chat-toggled")
  }),



  /* switcher */

  $("#BlueTheme").on("click", function () {
    $("html").attr("data-bs-theme", "blue-theme")
  }),

  $("#LightTheme").on("click", function () {
    $("html").attr("data-bs-theme", "light")
  }),

    $("#DarkTheme").on("click", function () {
      $("html").attr("data-bs-theme", "dark")
    }),

    $("#SemiDarkTheme").on("click", function () {
      $("html").attr("data-bs-theme", "semi-dark")
    }),

    $("#BoderedTheme").on("click", function () {
      $("html").attr("data-bs-theme", "bodered-theme")
    })



  /* search control */

  $(".search-control").click(function () {
    $(".search-popup").addClass("d-block");
    $(".search-close").addClass("d-block");
  });


  $(".search-close").click(function () {
    $(".search-popup").removeClass("d-block");
    $(".search-close").removeClass("d-block");
  });


  $(".mobile-search-btn").click(function () {
    $(".search-popup").addClass("d-block");
  });


  $(".mobile-search-close").click(function () {
    $(".search-popup").removeClass("d-block");
  });




  /* menu active */
/*
  $(function () {
    for (var e = window.location, o = $(".metismenu li a").filter(function () {
      return this.href == e
    }).addClass("").parent().addClass("mm-active"); o.is("li");) o = o.parent("").addClass("mm-show").parent("").addClass("mm-active")
  });
*/

  /* make event if input datalist is chosen */
  $("input[list='articleList']").on("input", function () {
    var selectedOption = $("#articleList option[value='" + $(this).val() + "']");
    if (selectedOption.length > 0 && selectedOption.data("id") !== undefined && selectedOption.data("id") !== 0) {
      $("#Form_addArticleForm_Title").val(selectedOption.data("title"));
      $("#Form_addArticleForm_SourceArticleID").val(selectedOption.data("id"));
      $("#Form_addArticleForm_Unit").val(selectedOption.data("unit"));
      $("#Form_addArticleForm_SellPriceNetto").val(selectedOption.data("nettoprice").toFixed(2));

    } else {
      $("#Form_addArticleForm_SourceArticleID").val(0);
      $("#Form_addArticleForm_Unit").val(0);
      $("#Form_addArticleForm_SellPriceNetto").val(0);
    }
    // trigger final price update
    $("#Form_addArticleForm_Quantity").trigger("input");
  });

  // update final price if quantity or sell price changes
  $("#Form_addArticleForm_Quantity, #Form_addArticleForm_SellPriceNetto, #Form_addArticleForm_Discount").on("input", function () {
    var quantity = parseFloat($("#Form_addArticleForm_Quantity").val().replace(",", "."));
    var sellPrice = parseFloat($("#Form_addArticleForm_SellPriceNetto").val().replace(",", "."));
    var discount = parseFloat($("#Form_addArticleForm_Discount").val().replace(",", "."));
    var tax = parseFloat($("#Form_addArticleForm_TaxRate").val().replace(",", "."));
    if (isNaN(quantity)) quantity = 0;
    if (isNaN(sellPrice)) sellPrice = 0;
    if (isNaN(discount)) discount = 0;
    if (isNaN(tax)) tax = 0;
    var finalPrice = quantity * sellPrice * (1 - discount / 100) * (1 + tax / 100);
    $("#Form_addArticleForm_FinalPrice").val(finalPrice.toFixed(2));
  });



  // same for edit form
  $("input[list='articleList']").on("input", function () {
    var selectedOption = $("#articleList option[value='" + $(this).val() + "']");
    if (selectedOption.length > 0 && selectedOption.data("id") !== undefined && selectedOption.data("id") !== 0) {
      $("#Form_editArticleForm_Title").val(selectedOption.data("title"));
      $("#Form_editArticleForm_SourceArticleID").val(selectedOption.data("id"));
      $("#Form_editArticleForm_Unit").val(selectedOption.data("unit"));
      $("#Form_editArticleForm_SellPriceNetto").val(selectedOption.data("nettoprice").toFixed(2));

    } else {
      $("#Form_editArticleForm_SourceArticleID").val(0);
      $("#Form_editArticleForm_Unit").val(0);
      $("#Form_editArticleForm_SellPriceNetto").val(0);
    }
    // trigger final price update
    $("#Form_editArticleForm_Quantity").trigger("input");
  });

  // update final price if quantity or sell price changes
  $("#Form_editArticleForm_Quantity, #Form_editArticleForm_SellPriceNetto, #Form_editArticleForm_Discount").on("input", function () {
    var quantity = parseFloat($("#Form_editArticleForm_Quantity").val().replace(",", "."));
    var sellPrice = parseFloat($("#Form_editArticleForm_SellPriceNetto").val().replace(",", "."));
    var discount = parseFloat($("#Form_editArticleForm_Discount").val().replace(",", "."));
    var tax = parseFloat($("#Form_editArticleForm_TaxRate").val().replace(",", "."));
    if (isNaN(quantity)) quantity = 0;
    if (isNaN(sellPrice)) sellPrice = 0;
    if (isNaN(discount)) discount = 0;
    if (isNaN(tax)) tax = 0;
    var finalPrice = quantity * sellPrice * (1 - discount / 100) * (1 + tax / 100);
    $("#Form_editArticleForm_FinalPrice").val(finalPrice.toFixed(2));
  });

  // confirm dialog for delete - german
  $(".yousure").click(function (e) {
    if (!confirm("Sind Sie sicher?")) {
      e.preventDefault();
      return false;
    }
    return true;
  });

  // add signature fields
  $(".signature-field").each(function() {
    var signaturePartDiv = $(this).find(".signaturePart");
    signaturePartDiv.jSignature({'height': 150, 'width': 300,color:"#000000",lineWidth:1});
    signaturePartDiv.bind('change', function(e) {
      //var datapair = signaturePartDiv.jSignature("getData", "base30");
      var datapair = signaturePartDiv.jSignature("getData", "svgbase64");
      var datapair = "data:" + datapair[0] + "," + datapair[1];
      $(this).closest(".signature-field").find("textarea").val(datapair);
      
      //var signatureImage = $(this).closest(".signature-field").find("img[id$='_signatureImage']");
      //signatureImage.attr("src", datapair);
      //signatureImage.show();
    });

    // clear button
    $(this).find(".clearSignature").click(function() {
      signaturePartDiv.jSignature("reset");
      $(this).closest(".signature-field").find("textarea").val("");
      //var signatureImage = $(this).closest(".signature-field").find("img[id$='_signatureImage']");
      //signatureImage.attr("src", "");
      //signatureImage.hide();
    });
  });

  if ($('#calendarMembers').length) {
    setTimeout(function() {
      var calendarEl = document.getElementById('calendarMembers');
      var visibleRange = {};
      if (calendarEl.dataset.start !== undefined && calendarEl.dataset.end !== undefined) {
        visibleRange = {
          start: calendarEl.dataset.start,
          end: calendarEl.dataset.end
        };
      }
      var events = [];
      $('#calendarMembers marker').each(function() {
        events.push({
          title: $(this).data('name'),
          start: $(this).data('start'),
          end: $(this).data('end'),
          color: $(this).data('color'),
          url: $(this).data('url'),
          extendedProps: {
            address: $(this).data('address'),
            popover: $(this).data('popover'),
          }
        });
      });
      
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek',
        locale: 'de',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: false
        },
        height: 600,
        weekends: false,
        eventDisplay: 'block',
        eventClick: function(event) {
            if (event.event.url) {
              event.jsEvent.preventDefault();
              window.open(event.event.url, "_blank");
            }
        },
        visibleRange: visibleRange,
        events: events
      });
      calendar.render();
    }, 250);
  } 
  // end calendar


  // datatables
  if ($('.dataTableMe').length) {
    $('.dataTableMe').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/de-DE.json"
      },
      responsive: true,
      //dom: 'Bfrtip',
      //buttons: [ 'excel', 'pdf', 'print']
    });
    //table.buttons().container().appendTo( '#offerListTable_paginate' );
  }

  // end datatables


  // select2 for dropdowns
  if ($('.select2me').length) {
    $('select.select2me').select2({
      theme: 'bootstrap-5',
      width: '100%'
    });
  }

  /*
  theme: "bootstrap-5",
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
         */

  // end select2

  // image uploadify
  if ($('.form-upload').length) {
    //$('.form-upload').imageuploadify();
  }

  // end image uploadify

  /* notify me */
  if ($('notification').length) {
    $('notification').each(function() {
      var type = $(this).attr('type') || 'info';
      var icon = $(this).attr('icon') || 'bi bi-info-circle';
      var message = $(this).attr('message') || '';
    Lobibox.notify(type, {
      pauseDelayOnHover: true,
      continueDelayOnInactiveTab: false,
      position: 'top right',
      width: 300,
      icon: icon,
      title: '',
      msg: message
    });
    });
  }  /* end notify me */

});
