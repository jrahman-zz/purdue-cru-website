jQuery(function($) {

    // Initialize Flexslider slideshow
	function init_slides() {
		$('.slide').css('margin', '0px');

		$('.slide-control').flexslider({
			animation: "slide",
			pauseOnAction: false,
			directionNav: true,
			controlNav: false
		});
	}

    // Track window resize events to modify the navigation bar
	var windowSize = new Number(601);
	$(window).resize(function() {
		var width = $(window).width();

		// Make sure that the menu is visible when transitioning from a hidden dropdown to normal menu bar
		if (width > 600) {
			$('ul.menu').css('display', 'block');
		} else if (width < 600 && windowSize >= 600) {
			$('ul.menu').css('display', 'none');
		} else if (width == 600) {
			$('ul.menu').css('display', 'block');
		}
		windowSize = $(window).width();

	});
    
    function init() {
	
		// Set the current window size
		windowSize = $(window).width();

		// Flip the no-js class
		$('.no-js').removeClass('no-js').addClass('js');

		// Set mobile nav-menu text with current page
		if ($('.page-title').html() != "") {
			$('#menu-header').html($('.page-title').html());
		} else if ($('.current-menu-item > a').html() != ""){
			$('#menu-header').html($('.current-menu-item > a').html());
		}
		
		// Mobile nav-menu dropdown
		$('#menu-icon').click(function() {
			$(this).toggleClass('clicked');
			$('.menu').slideToggle();
		});

		// Mobile nav-menu hover
		$('.menu-item > a').hover(function(e) {
			$(this).addClass('hover');
			e.stopPropagation();
		}, function(e) {
			$(this).removeClass('hover');
			e.stopPropagation();
		});

		init_slides();

		// Top auto scroll
		$('#top-link').click(function() {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	}
 
    $(document).ready(function() {
        init();
    });

    $("#contact-form").submit(function() {
        var retVal = true;
    
        // Get the input contents
        var name = $("input#name").val();
        var message = $("textarea#message").val();
        var email = $("input#email").val();
        var subject = $("input#subject").val();

        if (!testSubject(subject)) {
            retVal = false;
            $("#contact-form-subject-error").show();
        } else {
            $("#contact-form-subject-error").hide();
        }

        if (!testName(name)) {
            retVal = false;
            $("#contact-form-name-error").show();
        } else {
            $("#contact-form-name-error").hide();
        }

        if (!testEmail(email)) {
            retVal = false;
            $("#contact-form-email-error").show();
        } else {
            $("#contact-form-email-error").hide();
        }

        if (!testMessage(message)) {
            retVal = false;
            $("#contact-form-message-error").show();
        } else {
            $("#contact-form-message-error").hide();
        }
        return retVal;
    });
    
});

function testSubject(subject) {
    var re = /^.{0,50}\w.{0,50}$/;
    return re.test(subject);
}

function testMessage(message) {
    var re = /^.{1,5000}$/s;
    return re.test(message);
}

function testEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function testName(name) {
    var re = /^.{0,50}\w.{0,50}$/;
    return re.test(name);
}
