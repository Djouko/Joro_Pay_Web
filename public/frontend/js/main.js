(function ($) {
  "user strict";

  // preloader
  $(window).on('load', function () {
    $('.preloader').fadeOut(1000);
    var img = $('.bg_img');
    img.css('background-image', function () {
      var bg = ('url(' + $(this).data('background') + ')');
      return bg;
    });
  });

//Create Background Image
(function background() {
  let img = $('.bg_img');
  img.css('background-image', function () {
    var bg = ('url(' + $(this).data('background') + ')');
    return bg;
  });
})();



// header-fixed
var fixed_top = $(".header-section");
$(window).on("scroll", function(){
    if( $(window).scrollTop() > 100){
        fixed_top.addClass("animated fadeInDown header-fixed");
    }
    else{
        fixed_top.removeClass("animated fadeInDown header-fixed");
    }
});

// navbar-click
$(".navbar li a").on("click", function () {
  var element = $(this).parent("li");
  if (element.hasClass("show")) {
    element.removeClass("show");
    element.children("ul").slideUp(500);
  }
  else {
    element.siblings("li").removeClass('show');
    element.addClass("show");
    element.siblings("li").find("ul").slideUp(500);
    element.children('ul').slideDown(500);
  }
});

// scroll-to-top
var ScrollTop = $(".scrollToTop");
$(window).on('scroll', function () {
  if ($(this).scrollTop() < 100) {
      ScrollTop.removeClass("active");
  } else {
      ScrollTop.addClass("active");
  }
});

// faq
$('.faq-wrapper .faq-title').on('click', function (e) {
  var element = $(this).parent('.faq-item');
  if (element.hasClass('open')) {
    element.removeClass('open');
    element.find('.faq-content').removeClass('open');
    element.find('.faq-content').slideUp(300, "swing");
  } else {
    element.addClass('open');
    element.children('.faq-content').slideDown(300, "swing");
    element.siblings('.faq-item').children('.faq-content').slideUp(300, "swing");
    element.siblings('.faq-item').removeClass('open');
    element.siblings('.faq-item').find('.faq-title').removeClass('open');
    element.siblings('.taq-item').find('.faq-content').slideUp(300, "swing");
  }
});

// slider
var swiper = new Swiper('.testimonial-slider', {
  slidesPerView: 3,
  spaceBetween: 30,
  loop: true,
  centeredSlides: true,
  navigation: {
    nextEl: '.slider-next',
    prevEl: '.slider-prev',
  },
  autoplay: {
    speeds: 1000,
    delay: 2000,
  },
  speed: 1000,
  breakpoints: {
    991: {
      slidesPerView: 2,
    },
    767: {
      slidesPerView: 1,
    },
    575: {
      slidesPerView: 1,
    },
  }
});

// slider
var swiper = new Swiper(".brand-slider", {
    slidesPerView: 5,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      speed: 1000,
      delay: 3000,
    },
    speed: 1000,
    breakpoints: {
      1199: {
      slidesPerView: 5,
      },
      991: {
      slidesPerView: 4,
      },
      767: {
      slidesPerView: 3,
      },
      575: {
      slidesPerView: 2,
      },
    }
  });

//sidebar Menu
$(document).on('click', '.sidebar-collapse-icon', function () {
  $('.page-container').toggleClass('show');
});

// sidebar sub
$(".has-sub > a").on("click", function () {
  var element = $(this).parent("li");
  if (element.hasClass("active")) {
    element.removeClass("active");
    element.children("ul").slideUp(500);
  }
  else {
    element.siblings("li").removeClass('active');
    element.addClass("active");
    element.siblings("li").find("ul").slideUp(500);
    element.children('ul').slideDown(500);
  }
});

// Mobile Menu
$('.sidebar-mobile-menu').on('click', function () {
  $('.sidebar-main-menu').slideToggle();
});

//Profile Upload
function proPicURL(input) {
  if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
          var preview = $(input).parents('.preview-thumb').find('.profilePicPreview');
          $(preview).css('background-image', 'url(' + e.target.result + ')');
          $(preview).addClass('has-image');
          $(preview).hide();
          $(preview).fadeIn(650);
      }
      reader.readAsDataURL(input.files[0]);
  }
}
$(".profilePicUpload").on('change', function () {
  proPicURL(this);
});

$(".remove-image").on('click', function () {
  $(".profilePicPreview").css('background-image', 'none');
  $(".profilePicPreview").removeClass('has-image');
});


$(".logo-btn").click(function(){
  $(".main-side-menu").toggleClass("show");
});
$(".main-side-menu-cross").click(function(){
  $(".main-side-menu").removeClass("show");
});

$('.account-area-btn').on('click', function (e) {
  e.preventDefault();
  if($('.account').hasClass('active')) {
    $('.account').removeClass('active');
    $('.body-overlay').removeClass('active');
  }else {
    $('.account').addClass('active');
    $('.body-overlay').addClass('active');
    $('.navbar-collapse').removeClass('show');
  }
});
$('#body-overlay, .account-cross-btn').on('click', function (e) {
  e.preventDefault();
  $('.account').removeClass('active');
  $('.body-overlay').removeClass('active');
});

$('.account-control-btn').on('click', function () {
  $('.account-wrapper').toggleClass('change-form');
})

// notification
$(".notify-btn-area").click(function(){
  $(".notification-wrapper").slideToggle();
});


//Odometer
if ($(".statistics-item").length) {
    $(".statistics-item").each(function () {
      $(this).isInViewport(function (status) {
        if (status === "entered") {
          for (var i = 0; i < document.querySelectorAll(".odometer").length; i++) {
            var el = document.querySelectorAll('.odometer')[i];
            el.innerHTML = el.getAttribute("data-odometer-final");
          }
        }
      });
    });
  }


$('.header-mobile-search-btn').on('click', function (e) {
  e.preventDefault();
  if($('.header-mobile-search-form-area').hasClass('active')) {
    $('.header-mobile-search-form-area').removeClass('active');
    $('.body-overlay').removeClass('active');
  }else {
    $('.header-mobile-search-form-area').addClass('active');
    $('.body-overlay').addClass('active');
    $('.header-section').addClass('active');
  }
});
$('#body-overlay').on('click', function (e) {
  e.preventDefault();
  $('.header-mobile-search-form-area').removeClass('active');
  $('.body-overlay').removeClass('active');
});

// active menu JS
function splitSlash(data) {
  return data.split('/').pop();
}
function splitQuestion(data) {
  return data.split('?').shift().trim();
}
var pageNavLis = $('.sidebar-menu a');
var dividePath = splitSlash(window.location.href);
var divideGetData = splitQuestion(dividePath);
var currentPageUrl = divideGetData;

// find current sidevar element
$.each(pageNavLis,function(index,item){
    var anchoreTag = $(item);
    var anchoreTagHref = $(item).attr('href');
    var index = anchoreTagHref.indexOf('/');
    var getUri = "";
    if(index != -1) {
      // split with /
      getUri = splitSlash(anchoreTagHref);
      getUri = splitQuestion(getUri);
    }else {
      getUri = splitQuestion(anchoreTagHref);
    }
    if(getUri == currentPageUrl) {
      var thisElementParent = anchoreTag.parents('.sidebar-menu-item');
      (anchoreTag.hasClass('nav-link') == true) ? anchoreTag.addClass('active') : thisElementParent.addClass('active');
      (anchoreTag.parents('.sidebar-dropdown')) ? anchoreTag.parents('.sidebar-dropdown').addClass('active') : '';
      (thisElementParent.find('.sidebar-submenu')) ? thisElementParent.find('.sidebar-submenu').slideDown("slow") : '';
      return false;
    }
});

//sidebar Menu
$('.sidebar-menu-bar').on('click', function (e) {
    e.preventDefault();
    if($('.sidebar, .navbar-wrapper, .body-wrapper').hasClass('active')) {
      $('.sidebar, .navbar-wrapper, .body-wrapper').removeClass('active');
      $('.body-overlay').removeClass('active');
    }else {
      $('.sidebar, .navbar-wrapper, .body-wrapper').addClass('active');
      $('.body-overlay').addClass('active');
    }
  });
  $('#body-overlay').on('click', function (e) {
    e.preventDefault();
    $('.sidebar, .navbar-wrapper, .body-wrapper').removeClass('active');
    $('.body-overlay').removeClass('active');
  });

// dashboard-list
// $('.dashboard-list-item').on('click', function (e) {
//   var element = $(this).parent('.dashboard-list-item-wrapper');
//   if (element.hasClass('show')) {
//     element.removeClass('show');
//     element.find('.preview-list-wrapper').removeClass('show');
//     element.find('.preview-list-wrapper').slideUp(300, "swing");
//   } else {
//     element.addClass('show');
//     element.children('.preview-list-wrapper').slideDown(300, "swing");
//     element.siblings('.dashboard-list-item-wrapper').children('.preview-list-wrapper').slideUp(300, "swing");
//     element.siblings('.dashboard-list-item-wrapper').removeClass('show');
//     element.siblings('.dashboard-list-item-wrapper').find('.dashboard-list-item').removeClass('show');
//     element.siblings('.dashboard-list-item-wrapper').find('.preview-list-wrapper').slideUp(300, "swing");
//   }
// });
$(document).on('click','.dashboard-list-item',function (e) {
    if(e.target.classList.contains("select-btn")) {
      $(".dashboard-list-item-wrapper .select-btn").text("Select");
      $(e.target).text("Selected");
      return false;
    }
    var element = $(this).parent('.dashboard-list-item-wrapper');
    if (element.hasClass('show')) {
      element.removeClass('show');
      element.find('.preview-list-wrapper').removeClass('show');
      element.find('.preview-list-wrapper').slideUp(300, "swing");
    } else {
      element.addClass('show');
      element.children('.preview-list-wrapper').slideDown(300, "swing");
      element.siblings('.dashboard-list-item-wrapper').children('.preview-list-wrapper').slideUp(300, "swing");
      element.siblings('.dashboard-list-item-wrapper').removeClass('show');
      element.siblings('.dashboard-list-item-wrapper').find('.dashboard-list-item').removeClass('show');
      element.siblings('.dashboard-list-item-wrapper').find('.preview-list-wrapper').slideUp(300, "swing");
    }
  });
  $(document).on("click",".dashboard-list-item-wrapper .select-btn",function(){
    $(".dashboard-list-item-wrapper").removeClass("selected");
    $(this).parents(".dashboard-list-item-wrapper").toggleClass("selected");
  });

//sidebar Menu
$(document).on('click', '.push-icon', function () {
    $('.push-wrapper').toggleClass('active');
    $('.body-overlay').addClass('show');
  });
  $('#body-overlay').on('click', function (e) {
    e.preventDefault();
    $('.push-wrapper').removeClass('active');
    $('.body-overlay').removeClass('show');
  });

//action button
$('.action-btn .btn').on('click', function (e) {
    e.preventDefault();
    if($(this).siblings('.action-list').hasClass('active')) {
      $(this).siblings('.action-list').removeClass('active');
      $('.body-overlay').removeClass('active');
    }else {
      $(this).siblings('.action-list').addClass('active');
      $('.body-overlay').addClass('active');
    }
  });
  $('#body-overlay').on('click', function (e) {
    e.preventDefault();
    $('.action-list').removeClass('active');
    $('.body-overlay').removeClass('active');
  });

//info-btn
$(document).on('click', '.info-btn', function () {
  $('.support-profile-wrapper').addClass('active');
});
$(document).on('click', '.chat-cross-btn', function () {
  $('.support-profile-wrapper').removeClass('active');
});


$(".dash-payment-title-area").click(function(){
  $(this).parents('.body-wrapper .dash-payment-item-wrapper').find('.dash-payment-item').toggleClass("active");
});

$(".confirm-withdraw-method-item.proceed").click(function(){
  $(".confirm-withdraw-form").slideToggle();
  $(this).toggleClass("active");
});

// card-flip
$(document).on("click",".card-custom",function(){
  $(this).toggleClass("active");
});

// product + - start here
$(function () {
  var CartPlusMinus = $('.product-plus-minus');
  CartPlusMinus.prepend('<div class="dec qtybutton">-</div>');
  CartPlusMinus.append('<div class="inc qtybutton">+</div>');
  $(".qtybutton").on("click", function () {
    var $button = $(this);
    var oldValue = $button.parent().find("input").val();
    if ($button.text() === "+") {
      var newVal = parseFloat(oldValue) + 1;
    } else {
      // Don't allow decrementing below zero
      if (oldValue > 0) {
        var newVal = parseFloat(oldValue) - 1;
      } else {
        newVal = 1;
      }
    }
    $button.parent().find("input").val(newVal);
  });
});


// custom Select
$('.custom-select').on('click', function (e) {
    e.preventDefault();
    $(".custom-select-wrapper").removeClass("active");
    if($(this).siblings(".custom-select-wrapper").hasClass('active')) {
      $(this).siblings(".custom-select-wrapper").removeClass('active');
      $('.body-overlay').removeClass('active');
    }else {
      $(this).siblings(".custom-select-wrapper").addClass('active');
      $('.body-overlay').addClass('active');
    }
  });
  $('#body-overlay').on('click', function (e) {
    e.preventDefault();
    $('.custom-select-wrapper').removeClass('active');
    $('.body-overlay').removeClass('active');
  });
  $(document).on('click','.custom-option', function(){
    $(this).parent().find(".custom-option").removeClass("active");
    $(this).addClass('active');
    var flag = $(this).find("img").attr("src");
    var currencyCode = $(this).find(".custom-currency").text();
    var inputValue = setAdSelectInputValue($(this).attr("data-item"));
    $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find(".custom-currency").text(currencyCode);

    if($(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find("img").length > 0) {
      $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find("img").attr("src",flag);
    }else {
      var image = `<img src="${flag}" alt="flag" class="custom-flag">`;
      $(image).insertBefore($(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find("span.custom-currency"));
    }
    $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find("input").val(inputValue);
    $(this).parents(".custom-select-wrapper").removeClass("active");
    $('.body-overlay').removeClass('active');
  });

// nice-select
$(".nice-select").niceSelect()
$("form button[type=submit], form input[type=submit]").on("click", function (event) {
    var inputFileds = $(this).parents("form").find("input[type=text], input[type=number], input[type=email], input[type=password]");
    var mode = false;
    $.each(inputFileds, function (index, item) {
        if ($(item).attr("required") != undefined) {
            if ($(item).val() == "") {
                mode = true;
            }
        }
    });
    if (mode == false) {
        $(this).parents("form").find(".btn-ring").show();
        $(this).parents("form").find("button[type=submit],input[type=submit]").prop("disabled", true);
        $(this).parents("form").submit();
    }
});
$(".registerBtn,.verifyCode,.sendBtn").on("click", function (event) {
    // var inputFileds = $(this).parents("form").find("input[type=text], input[type=number], input[type=email], input[type=password],input[type=checkbox]");
    // var mode = false;
    // $.each(inputFileds, function (index, item) {
    //     if ($(item).attr("required") != undefined) {
    //         if ($(item).val() == "") {
    //             mode = true;
    //         }
    //     }
    // });
    // if (mode == false) {
        $(this).parents("form").find(".btn-ring").show();
        $(this).parents("form").find("button[type=button],input[type=button]").prop("disabled", true);
        // $(this).parents("form").submit();
    // }
});
$(document).ready(function () {
    $.each($(".btn-loading"), function (index, item) {
        $(item).append(`<span class="btn-ring"></span>`);
    });
});

$(document).ready(function() {
    $(".show_hide_password a").on('click', function(event) {
        event.preventDefault();
        if($('.show_hide_password input').attr("type") == "text"){
            $('.show_hide_password input').attr('type', 'password');
            $('.show_hide_password i').addClass( "fa-eye-slash" );
            $('.show_hide_password i').removeClass( "fa-eye" );
        }else if($('.show_hide_password input').attr("type") == "password"){
            $('.show_hide_password input').attr('type', 'text');
            $('.show_hide_password i').removeClass( "fa-eye-slash" );
            $('.show_hide_password i').addClass( "fa-eye" );
        }
    });
  });
  $(document).ready(function() {
      $(".show_hide_password-2 a").on('click', function(event) {
          event.preventDefault();
          if($('.show_hide_password-2 input').attr("type") == "text"){
              $('.show_hide_password-2 input').attr('type', 'password');
              $('.show_hide_password-2 i').addClass( "fa-eye-slash" );
              $('.show_hide_password-2 i').removeClass( "fa-eye" );
          }else if($('.show_hide_password-2 input').attr("type") == "password"){
              $('.show_hide_password-2 input').attr('type', 'text');
              $('.show_hide_password-2 i').removeClass( "fa-eye-slash" );
              $('.show_hide_password-2 i').addClass( "fa-eye" );
          }
      });
    });
    $(document).ready(function() {
      $(".show_hide_password-3 a").on('click', function(event) {
          event.preventDefault();
          if($('.show_hide_password-3 input').attr("type") == "text"){
              $('.show_hide_password-3 input').attr('type', 'password');
              $('.show_hide_password-3 i').addClass( "fa-eye-slash" );
              $('.show_hide_password-3 i').removeClass( "fa-eye" );
          }else if($('.show_hide_password-3 input').attr("type") == "password"){
              $('.show_hide_password-3 input').attr('type', 'text');
              $('.show_hide_password-3 i').removeClass( "fa-eye-slash" );
              $('.show_hide_password-3 i').addClass( "fa-eye" );
          }
      });
    });

    $(document).ready(function(){
        $.each($(".switch-toggles"),function(index,item) {
          var firstSwitch = $(item).find(".switch").first();
          var lastSwitch = $(item).find(".switch").last();
          if(firstSwitch.attr('data-value') == null) {
            $(item).find(".switch").first().attr("data-value",true);
            $(item).find(".switch").last().attr("data-value",false);
          }
          if($(item).hasClass("active")) {
            $(item).find('input').val(firstSwitch.attr("data-value"));
          }else {
            $(item).find('input').val(lastSwitch.attr("data-value"));
          }
        });
    });
    $(document).on("click",".switch",function() {
        if($(this).parents(".switch-toggles").attr("data-clickable") == undefined || $(this).parents(".switch-toggles").attr("data-clickable") == "false") {
            return false;
        }
        if($(this).parents(".switch-toggles").hasClass("active")) {
            $(this).parents(".switch-toggles").find(".switch").first().find(".btn-ring").show();
        }else {
            $(this).parents(".switch-toggles").find(".switch").last().find(".btn-ring").show();
        }
    })
    $(document).on('click','.switch-toggles .switch', function () {

          if($(this).parents(".switch-toggles").attr("data-clickable") == undefined || $(this).parents(".switch-toggles").attr("data-clickable") == "false") {
            return false;
          }
          // alert();
          var dataValue = $(this).parents(".switch-toggles").find(".switch").first().attr("data-value");
          if($(this).parents(".switch-toggles").hasClass("active")) {
              dataValue = $(this).parents(".switch-toggles").find(".switch").last().attr("data-value");
          }
          $(this).parents(".switch-toggles.default").find("input").val(dataValue);
          $(this).parents(".switch-toggles.default").toggleClass('active');
    });

})(jQuery);

/**
 * Refresh all button that have "btn-loading" class
 */
function btnLoadingRefresh() {
    $.each($(".btn-loading"), function (index, item) {
        if ($(item).find(".btn-ring").length == 0) {
            $(item).append(`<span class="btn-ring"></span>`);
        }
    });
}

function setAdSelectInputValue(item) {
    return item;
  }

  // getAllCountries();
  // select-2 init
  $('.select2-basic').select2();
  $('.select2-multi-select').select2();
  $(".select2-auto-tokenize").select2({
  tags: true,
  tokenSeparators: [',']
  });
function placePhoneCode(code) {
      if(code != undefined) {
          code = code.replace("+","");
          code = "+" + code;
          $("input.phone-code").val(code);
          $("div.phone-code").html(code);
      }
}
// switch-toggles
$(document).ready(function(){
    $.each($(".switch-toggles"),function(index,item) {
      var firstSwitch = $(item).find(".switch").first();
      var lastSwitch = $(item).find(".switch").last();
      if(firstSwitch.attr('data-value') == null) {
        $(item).find(".switch").first().attr("data-value",true);
        $(item).find(".switch").last().attr("data-value",false);
      }
      if($(item).hasClass("active")) {
        $(item).find('input').val(firstSwitch.attr("data-value"));
      }else {
        $(item).find('input').val(lastSwitch.attr("data-value"));
      }
    });
});

  function switcherAjax(hitUrl,method = "PUT") {
    $(document).on("click",".event-ready",function(event) {
      var inputName = $(this).parents(".switch-toggles").find("input").attr("name");
      if(inputName == undefined || inputName == "") {
        return false;
      }

      $(this).parents(".switch-toggles").find(".switch").removeClass("event-ready");
      var input = $(this).parents(".switch-toggles").find("input[name="+inputName+"]");
      var eventElement = $(this);
      if(input.length == 0) {
          alert("Input field not found.");
          $(this).parents(".switch-toggles").find(".switch").addClass("event-ready");
          $(this).find(".btn-ring").hide();
          return false;
      }

      var CSRF = $("head meta[name=csrf-token]").attr("content");

      var dataTarget = "";
      if(input.attr("data-target")) {
          dataTarget = input.attr("data-target");
      }

      var inputValue = input.val();
      var data = {
        _token: CSRF,
        _method: method,
        data_target: dataTarget,
        status: inputValue,
        input_name: inputName,
      };

      $.post(hitUrl,data,function(response) {

        throwMessage('success',response.message.success);
        // Remove Loading animation
        $(event.target).find(".btn-ring").hide();
      }).done(function(response){

        $(eventElement).parents(".switch-toggles").find(".switch").addClass("event-ready");

        $(eventElement).parents(".switch-toggles").find(".switch").find(".btn-ring").hide();

        $(eventElement).parents(".switch-toggles.btn-load").toggleClass('active');
        var dataValue = $(eventElement).parents(".switch-toggles").find(".switch").last().attr("data-value");
        if($(eventElement).parents(".switch-toggles").hasClass("active")) {
          dataValue = $(eventElement).parents(".switch-toggles").find(".switch").first().attr("data-value");
          $(eventElement).parents(".switch-toggles").find(".switch").first().find(".btn-ring").hide();
        }
        $(eventElement).parents(".switch-toggles.btn-load").find("input").val(dataValue);
        $(eventElement).parents(".switch-toggles").find(".switch").last().find(".btn-ring").hide();


      }).fail(function(response) {
          var response = JSON.parse(response.responseText);
          throwMessage(response.type,response.message.error);

          $(eventElement).parents(".switch-toggles").find(".switch").addClass("event-ready");
          $(eventElement).parents(".switch-toggles").find(".btn-ring").hide();
          return false;
      });

    });
  }
// slider

function copyToClipBoard(copyId) {
    var copyText = document.getElementById(copyId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);

    notification('success', 'URL Copied To Clipboard!');
}

function formAjaxRequest(formData,URL,formElement) {
    var data = formData;
    $.post(URL,data,function(response) {
      //response
    }).done(function(response){
      if(response.data?.url != undefined) {
        return window.location.href = response.data.url;
      }else {
        throwMessage('error',['Something went wrong! Please try again']);
      }

    }).fail(function(response) {
      var response = JSON.parse(response.responseText);
      throwMessage(response.type,response.message.error);
      $(formElement).find(".btn-ring").hide();
      $(formElement).find("button").attr("disabled",false);

      if(response.data?.url != undefined) {
        setTimeout(() => {
          return window.location.href = response.data.url;
        }, 1000);
      }
    });
}
$(document).ready(function(){
var forms = $(".onload-from");
$.each(forms,function(index,item) {
    $(item).submit(function(event){
    event.preventDefault();
    var formData = $(item).serialize();
    var submitURL = $(item).attr("action");
    formAjaxRequest(formData,submitURL,item);
    });
});
});
$(document).on("keyup",".number-input",function(){
  var pattern = /^[0-9]*\.?[0-9]*$/;
  var value = $(this).val();
  var test = pattern.test(value);
  if(test == false) {
    var rightValue = value;
    if(value.length > 0) {
      for (let index = 0; index < value.length; index++){
        if(!$.isNumeric(rightValue)) {
          rightValue = rightValue.slice(0, -1);
        }
      }
    }
    $(this).val(rightValue);
  }
});
