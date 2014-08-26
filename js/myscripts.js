$(document).ready(function() {

	var edit_width = $('.edit_panel').width();//get width automaticly
	$('#slider').click(function() {
		if($(this).css("left") == "950px" && !$(this).is(':animated')) {
        $('.edit_panel,#slider').animate({"left": '+='+edit_width});
		} else {
			if(!$(this).is(':animated')) { //perevent double click to double margin
				$('.edit_panel,#slider').animate({"left": '-='+edit_width});
			}
		}
	});
	
	$(function() {
		$( "#datepicker" ).datepicker({dateFormat:'yy-mm-dd' });
	});
	
	var docked = false;
	var menu = $('.menu_bar');
	var init = menu.offset().top;

	$(window).scroll(function() 
	{       
			if (!docked && (menu.offset().top - $("body").scrollTop() < 0)) 
			{
				menu.css({
					position : "fixed",
					top: 0,
				});
				docked = true;
			} 
			else if(docked && $("body").scrollTop() <= init)
			{
				menu.css({
					position : "absolute",
					top: init + 'px',
				});

				docked = false;
			}
	});
	
	this.fosterToggle = function() {
		
		var location = document.getElementById('location').value;
		var foster_select = document.getElementById('foster_toggle');
		if(location == "Foster") {
			foster_select.style.visibility="visible";
		} else {
			foster_select.style.visibility="hidden";
		}
	};

	this.animateLine = function() {
		line.className = "nav_line " + prev + curr;
	};
	var line = document.getElementsByClassName('nav_line')[0];
	var prev = document.getElementsByClassName('pass')[0].value;
	var curr = document.getElementsByTagName('body')[0].className;
	this.animateLine();

    // If cookie is set, scroll to the position saved in the cookie.
    if ( $.cookie(".") !== null ) {
        $(document).scrollTop( $.cookie("scroll") );
    }

    // When a button is clicked...
    $('.submit').on("click", function() {
        // Set a cookie that holds the scroll position.
        $.cookie("scroll", $(document).scrollTop() );
    });
});

function overlay() {
	var el = document.getElementById("overlay");
	if (el.style == null) {

	} else {
		el.style.visibility = (el.style.visibility == "visible") ? "hidden" : "visible";
	}
}

function document_overlay_open(element) {
	var img = document.getElementById("document_overlay_img");
	var doc_overlay = document.getElementById("document_overlay");

	if (element.src != null) {
		img.src = element.src;
		doc_overlay.style.visibility = "visible";
	}
}

function document_overlay_close() {
	var doc_overlay = document.getElementById("document_overlay");
	doc_overlay.style.visibility = "hidden";
}

function toggle(location) {
	location.className = (location.className == "toggle check") ? "toggle uncheck_received" : "toggle check";
	
	var location_str = "cat_box ";

	if(location.id == "petsmart") {
		location_str += "PetSmart";
	} else {
		location_str += capitaliseFirstLetter(location.id);
	}

	elements = document.getElementsByClassName(location_str);
	
	for (var i in elements) {
		if (elements[i].style == null) {

		} else {
			if(location_str == "cat_box Adopted") {
				elements[i].style.display = (elements[i].style.display == "inline-block") ? "none" : "inline-block";
			} else if (elements[i] != null) {
				elements[i].style.display = (elements[i].style.display == "none") ? "inline-block" : "none";
			}
		}
	}
}

function capitaliseFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function photo_select(new_photo) {
	var old_photo = document.getElementsByClassName("overlay_photo selected");
	old_photo[0].className = "overlay_photo";
	new_photo.className = "overlay_photo selected";
}

function photo_update() {
	var elements = document.getElementsByClassName("overlay_photo selected");
	var selected_img = elements[0];
	
	if (selected_img != null) {
		var photo = selected_img.dataset.photo;
		var cat = selected_img.dataset.cat;
		var file = selected_img.dataset.file;

		var file_str = file.replace(" ", "%20");
		var img_str = "url(/upload/" + file_str + ")";
		var profile_photo = document.getElementById("profile_photo_div");
		profile_photo.style.backgroundImage = img_str;
		if (window.XMLHttpRequest) {
			xmlhttp=new XMLHttpRequest();
		}
		xmlhttp.open("GET","profile_photo.php?p="+photo+"&c="+cat,true);
		xmlhttp.send();
	}
}

function check_box_recieved(ele, id) {
	var action = "";

	switch (ele.className) {
		case "box_submit check_received":
			ele.className = "box_submit uncheck";
			ele.title = "Received: When treatment has been administered.";
			ele.parentElement.className = "treatment_row";
			action = "restore";
			break;
		case "box_submit uncheck":
			ele.className = "box_submit check_received";
			ele.title = "Restore:  Treatment has NOT been administered.";
			ele.parentElement.className = "treatment_row received_text";
			action = "received";
			break;
		case "box_submit x":
			ele.parentElement.style.display = "none";
			action = "delete";
		case "box_submit x_received":
			ele.parentElement.style.display = "none";
			action = "delete";
		default:
			break;
	}

	if (action != "") {
		if (window.XMLHttpRequest) {
			xmlhttp=new XMLHttpRequest();
		}
		xmlhttp.open("GET","check_box.php?id="+id+"&action="+action,true);
		xmlhttp.send();
	}
}

function cat_url(cat_id) {
	window.location.href = "cat.php?id=" + cat_id;
}
