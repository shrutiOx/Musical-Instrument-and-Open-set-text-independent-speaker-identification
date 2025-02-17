var URL = 'http://www.philharmonia.co.uk';
var BASE = '';
var DIR = '';
var SUBDIR = '';
var MOBILE = false;
var TABLET = false;
var THEME = 'default';

///var/www/philharmonia.co.uk/core/js/admin.js
/****************
* called on page load
*****************/
$(document).ready(function() {
	"use strict";
	
	$('.editable').bind('click', function() {
		var val = $(this).text();
		$(this).hide();
		$(this).after("<form class='inline_edit_form'><input type='text' value=\""+val.replace(/\'/g, '"')+"\" name='"+$(this).data('col')+"' /><input type='hidden' value='"+$(this).data('id')+"' name='id' /><input type='hidden' value=\""+$(this).data('table')+"\" name='table' /><input type='hidden' value='"+$(this).data('db')+"' name='db' /><br /><a href='javascript:void(0)' onclick='admin.edit.save(this.parentNode);'>save</a> / <a href='javascript:void(0)' onclick='admin.edit.revert();'>exit</a></form>");
	});

});

var admin = {
	edit: {
		revert: function(update) {
			"use strict";
			if (update!==null && update===true) {
				$('.inline_edit_form').each(function() {
					$(this).prev('.editable').html($(this).children('input[type=text]').val());
					$(this).remove();
				});
			} else {
				$('.inline_edit_form').remove();
			}
			$('.editable').show();
			
		},
		save: function(form) {
			"use strict";
			$.ajax({
				type:'POST',
				url:BASE+'/core/lib/ajax/admin/inline_edit.php',
				data:$(form).serialize(),
				success:function() {
					admin.edit.revert(true);
				}
			});
		}
	}
};

///var/www/philharmonia.co.uk/core/js/assets.js
var assets = {
	
	images: {
		remove: function (src, target) {
			"use strict";
			deleteFile(src, target);
		},
		edit: function (src) {
			"use strict";
			alert(src);
		},
		makeDefault: function (dir) {
			"use strict";
			alert(dir);
		},
		clickView: function (e) {
			"use strict";
			e.preventDefault();
			
			var id = e.target.parentNode.id;
			var img = e.target.href.replace(URL,'').replace(BASE,'');

			assets.images.view(id, img);
		},
		view: function (id, img, path) {
			"use strict";
	
			function storeCoords(c) {
				$('#x1').val(c.x);
				$('#y1').val(c.y);
				$('#x2').val(c.x2);
				$('#y2').val(c.y2);
				$('#w').val(c.w);
				$('#h').val(c.h);
				$('#dir').val(dir);
				$('#file').val(file);
				$('#path').val(path);
			}
			function enableCrop(id) {
				if ($('#image_asset_'+id).is(':visible')) {
					var ratio = crop_image_width / crop_image_height;
					var aspect = (ratio<1.47) ? 4/3 : 16/9 ;
					
					$('#image_asset_'+id).Jcrop({
						onChange: storeCoords,
						onSelect: storeCoords,
						onRelease: storeCoords,
						aspectRatio: aspect
					});
					$('#modal').animate({
						height:($('#modal_inner').innerHeight())+'px'
					}, function() {
						position();
					});
				} else {
					setTimeout(function() {
						enableCrop(id);
					}, 200);
				}
			}
			
			var dir = img.replace(BASE,'').split('/');
			var file = dir.pop();
			dir = dir.join('/');
			
			if ($('#image_container_'+id).length) {
				$('#image_container_'+id).remove();
			} else {
				$('.image_container').remove();
				
				$('#'+id).append("<div id='image_container_"+id+"' class='image_container' style='margin-bottom:30px'><img width='300' style='display:block;' id='image_asset_"+id+"' src='"+BASE+dir+'/'+file+"' /><button onclick='assets.images.crop()'>Save</button></div>");
				
				setTimeout(function() {
					enableCrop(id);
				}, 200);
			}
		
		},
		
		crop: function() {
			"use strict";
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/assets/img_crop.php',
				data:$('#image_coords').serialize(),
				success:function() {
					$('.image_container').remove();
					exitModal();
				}
			});
		}
	}
};

///var/www/philharmonia.co.uk/core/js/block.js
var block = {
	/**********************
	* load relevent block form 
	* @pageid:Int - id of page
	* @block:String - name of block, must be equivilent to file name of block form
	* @type:String - 'page' or 'module'
	**********************/
	getBlockForm:function (pageid, block, type, content) {
		"use strict";
		content = (content==null) ? false : content;

		$.ajax({
			type:'post',
			data:{
				id:pageid,
				type:type,
				content:content
			},
			url:BASE+'/blocks/'+block+'/form.php',
			success:function(data) {
				$('#block_container').html(data);
			},
			error:function() {
				$('#block_container').html("<p><em>block type not found</em></p>");
			}
		});
	}
}

///var/www/philharmonia.co.uk/core/js/blog.js
function deleteBlogPost(page, id) {
	$.ajax({
		type:'POST',
		url:BASE+'/lib/ajax/admin/blog/delete.php',
		data:{
			page:page,
			id:id
		},
		success: function(data) {
			$('#post'+id).fadeOut();
		}
	});
}

///var/www/philharmonia.co.uk/core/js/carousel.js
var disable_carousel = false;
var carousel_skip = 237;
var animate_carousel = true;
var animation_timer = null;

function carousel(id, dir) {
	"use strict";
	clearTimeout(animation_timer);
	
	if (!disable_carousel && animate_carousel) {
		disable_carousel = true;
		
		var carousel_slider_width = $('#carousel'+id+' .carousel_slider').width();
		var carousel_width = $('#carousel'+id+' .carousel_inner').width();
		var cur_left = parseInt($('#carousel'+id+' .carousel_slider').css('left'), 2);
		var new_left = "";
		
		if (dir==='last') {
			new_left = -((carousel_slider_width-carousel_width))+'px';
			
		} else if (dir==='right') {	
			if (Math.abs(cur_left) < (carousel_slider_width-carousel_width)) {
				new_left = (cur_left-carousel_skip)+'px';
			} else {
				new_left = 0;
			}
		} else {
			if (cur_left < 0) {
				new_left = (cur_left+carousel_skip)+'px';
			} else {
				new_left = -((carousel_slider_width-carousel_width))+'px';
			}
		}
		$('#carousel'+id+' .carousel_slider').animate({
			left:new_left
		}, function() {
			disable_carousel = false;
		});
		
	}
	animation_timer = setTimeout(function() { carousel(id, 'right'); }, 7000);
}

///var/www/philharmonia.co.uk/core/js/checkForm.js
/*********************************
* validate forms - uses the css class 'required' to check
*
* @frm:String = id of relevent form
* @data:Object - OPTIONAL - ajax processing data
*							MUST INCLUDE	'processor' (path to processor script sitting below /processors/)
*											'func' (function name)
*							add debug:1 to get on-screen feedback without modifying database
* @msg:String = message to return on error OPTIONAL
*********************************/
function checkForm(frm, msg) {
	"use strict";
	
	var validated = true;
	
	$('#'+frm+' .required').each(function() {	
		// if is checkbox and not checked then not valid
		if ($(this).is('input[type=checkbox]')) {
			if (!$(this).attr('checked')) {
				validated = false;
				$(this).parent().css('color', '#f00');
				$(this).css('border-color', '#f00');
			} 
		
		// if value is empty and not a submit/button then not valid
		} else if (!$(this).val() && !$(this).is('input[type=submit]') && !$(this).is('input[type=button]')) {
			validated = false;
			$(this).parent().css('color', '#f00');
			$(this).css('border-color', '#f00');
		}
	});

	if (validated) {
		$('#'+frm).submit();

		return true;
		
	} else {
		if (!msg) { msg = 'Please fill in all required fields'; }
		alert(msg);
		return false ;
	}
	

}

///var/www/philharmonia.co.uk/core/js/ckeditor.js
function addCKEditor(id) {
	"use strict";
	
	if (typeof CKEDITOR !== 'undefined') {
		CKEDITOR.replace(id, {
			//toolbar : 'PhiloToolbar',
			uiColor : '#cccccc',
			allowedContent: 'a[target,!href]; p{text-align}; br; h1; h2; h3; h4; h5; h6; address; blockquote; ul; li; ol; strong; em; div; script;'
		});
	}
}

///var/www/philharmonia.co.uk/core/js/cookies.js
var cookies = {
	set: function(action) {
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/cookies.php',
			data:{
				action:action
			},
			success:function(response) {
				if (action=='block') window.location.href=window.location.href;
				
				if ($('#cookie_warning').length>0) {
					$('#cookie_warning').animate({
						bottom:'-210px'
					}, function() {
						$('#cookie_warning').remove();
					});
				} else {
					window.location.href=window.location.href;
				}
			}
		});
	}
}

///var/www/philharmonia.co.uk/core/js/css-media-query-ie.js
var css = {
	
	init: function() {
		var screenwidth = $(window).innerWidth();
		var ua = navigator.userAgent.toLowerCase();
		
		if (ua.indexOf('msie 6.0') || ua.indexOf('msie 7.0') || ua.indexOf('msie 8.0')) {

			$('link').each(function() {
				if ($(this).attr('media')!=null) {
					if ($(this).attr('media').indexOf('min-width')!=-1) {
						width = $(this).attr('media').match(/[0-9]+/i)
						width = parseInt(width[0]);
						if (screenwidth > width) {
							$(this).attr('media', 'screen');
						}
					}
					if ($(this).attr('media').indexOf('max-width')!=-1) {
						width = $(this).attr('media').match(/[0-9]+/i)
						width = parseInt(width[0]);
						if (screenwidth < width) {
							$(this).attr('media', 'screen');
						}
					}
				}
			});
		}
	}
}

$(document).ready(function() {
	css.init();
});

///var/www/philharmonia.co.uk/core/js/deleteFile.js
/**************************************
* universal ajax delete function
*
* @id:Int - record dB id
* @table:String - name of database table
* @settings:Object - additional settings
***************************************/
function deleteFile(path, target) {
	"use strict";
	if (confirm('Are you sure you wish to delete this file?')) {
		
		// append base dir 
		path = BASE+'/'+path;
		
		$.ajax({
			type:'POST',
			url:BASE+'/processors/admin/deleteFile.php',
			data:{
				path:path,
				'function':'delete_file'
			},
			success:function(data) {
				if (data==='fail') {
					alert("Sorry, you don't have permission to do that");
				} else {
					$('#'+target).fadeOut();
				}
			},
			error:function() {
				alert('Sorry, something went wrong...');
			}
		});
	}
}

///var/www/philharmonia.co.uk/core/js/deleteRecord.js
/**************************************
* universal ajax delete function
*
* @id:Int - record dB id
* @table:String - name of database table
* @settings:Object - additional settings
***************************************/
function deleteRecord(id, table, settings) {
	"use strict";
	if (settings!=null) {
		settings.site = (settings.site!=null) ? settings.site : 'site'
	} else {
		settings = new Object();
		settings.site = 'site';
	}
	
	// merge settings with other data to send
	var data = {
			id:id,
			table:table,
			'function':'delete_record'
		};
	if (settings!==null) {
		$.extend(data, settings);
	}
	
	$.ajax({
		type:'POST',
		url:BASE+'/processors/admin/delete.php',
		data:data,
		success:function(data) {
			//alert(data);
			if (data==='fail') {
				alert("Sorry, you don't have permission to do that");
			} else {
				window.location.href=window.location.href;
			}
		},
		error:function() {
			alert('Sorry, something went wrong...');
		}
	});
}

///var/www/philharmonia.co.uk/core/js/e_nav.js
var mobNavOpn = false,
    mobMenuActiveLevel = 0,
    l1Menu = null,
    topLevelMenus = 0,
	secondLevelMenus = 0,
    currentSizeForM = 1,
    prevSizeForM = -1,
	mobMenuULs = [ // used to open the menu at the correct level on each page
		{name : "/concerts", hierarchy: "mobMenuUL1_1"},
		{name : "/orchestra", hierarchy: "mobMenuUL1_2"},
		{name : "/explore", hierarchy: "mobMenuUL1_3"},
		{name : "/education", hierarchy: "mobMenuUL1_4"},
		{name : "/education/schools_and_young_people", hierarchy: "mobMenuUL2_1"},
		{name : "/education/communities_and_family", hierarchy: "mobMenuUL2_2"},
		{name : "/education/emerging_artists", hierarchy: "mobMenuUL2_3"},
		{name : "/education/the_orchestral_experience", hierarchy: "mobMenuUL2_4"},
		{name : "/digital", hierarchy: "mobMenuUL1_5"},
		{name : "/digital/technological_innovation", hierarchy: "mobMenuUL2_5"},
		{name : "/digital/installations", hierarchy: "mobMenuUL2_6"},
		{name : "/digital/in_the_community", hierarchy: "mobMenuUL2_7"},
		{name : "/support/individual", hierarchy: "mobMenuUL2_8"}
	],
	iconTextSwitchElements = ["topNav_home", "sml_search_label", "topBar_login", "topBar_logout", "basket_button", "topBar_profile", "topBar_admin"];
	
//****************************************************************************************************************************
//--------------------------------------------------------------------- TOP BAR 
//****************************************************************************************************************************

// allow screen reader users to skip past the nav block to main content
$(function() { 
	$("#skipToMain").click( function() {
		var focusTarget;
		
		if (window.location.pathname.indexOf("digital") !== -1) {
			focusTarget = "digitalContentHolder";
		}
		else if (window.location.pathname.indexOf("education") !== -1) {
			focusTarget = "educationContentHolder";
		}
		else {
			focusTarget = "main";
		}
		
		$("#" + focusTarget).attr("tabindex", "-1");
		$("#" + focusTarget).focus();
		$("#" + focusTarget).blur( function() {
			$("#" + focusTarget).attr("tabindex", "");
		});
	});
});

// adjust what is shown in the top bar for desktop / mobile
function switchToDesktop() {
	"use strict";
	$("#mobile_nav_toggle").attr("aria-hidden", "true");
    $("#philharmonia_logoMobH").attr("aria-hidden", "true");
	$("#shop_button").attr("aria-hidden", "false");
	$("#donate_button").attr("aria-hidden", "false");
	$("#mobileNav_donate").attr("aria-hidden", "true");
	$("#breadcrumb").attr("aria-hidden", "false");
	
	iconTextSwitchElements.forEach( function(val) {
		$("#" + val).find("span:first").css({ display: "inline-block" });
		$("#" + val).find("img").css({ display: "none" });
	});
}

function switchToMobile() {
	"use strict";
	$("#mobile_nav_toggle").attr("aria-hidden", "false");
    $("#philharmonia_logoMobH").attr("aria-hidden", "false");
	$("#shop_button").attr("aria-hidden", "true");
	$("#donate_button").attr("aria-hidden", "true");
	$("#breadcrumb").attr("aria-hidden", "true");
	
	iconTextSwitchElements.forEach( function(val) {
		$("#" + val).find("img").css({ display: "inline-block" });
		$("#" + val).find("span:first").css({ display: "none" });
	});
}

function digiEduHomeBtn() { // add the home button to the top bar if in a sub-site (always there for mobile)
	"use strict";
	if (window.location.pathname.indexOf("digital") !== -1 || window.location.pathname.indexOf("education") !== -1) {
		$("#topNav_home").attr("aria-hidden", "false").css({ visibility: "visible" });
		$("#philharmonia_logoMobH").attr("aria-hidden", "true").css({ visibility: "hidden" });
	}
	else {
		if (currentSizeForM === 0) {
			$("#philharmonia_logoMobH").attr("aria-hidden", "false").css({ visibility: "visible" });
		}
		else {
			$("#topNav_home").attr("aria-hidden", "true").css({ visibility: "hidden" });
		}
	}
}

function checkSizeChange() {
	"use strict";
	if ( $("#philharmonia_logoMobH").css("display") === "none") { //desktop size
		currentSizeForM = 1;
		if (prevSizeForM !== 1) { switchToDesktop(); }
		prevSizeForM = 1;
	}
	else {
		currentSizeForM = 0;
		if (prevSizeForM !== 0) { switchToMobile(); }
		prevSizeForM = 0;
	}
}
    
$(document).ready(function() {
    
    eNav();
    eMobileNav();
    checkSizeChange();
	digiEduHomeBtn();
    
    $(window).resize( function() {
        if ( $("#philharmonia_logoMobH").css("display") === "none" && mobNavOpn === true) { //desktop size
            mobNavOpn = false; 
            mmResetAll();
            $("#mobile_nav_toggle").attr("aria-expanded", "false");
            $('#mobile_nav nav').css({ right: "-100%" });
            $('#mobile_nav_overlay').css({ opacity: 0, display: "none" });
        }
        checkSizeChange();
    });
    
//****************************************************************************************************************************
//--------------------------------------------------------------------- MOBILE MENU 
//****************************************************************************************************************************
    
    //------------------------------------------- open / close
    
    // todo: change to separate bind for touch, add swipe
    $("#mobile_nav_toggle").click( function() { toggleMobileNav(); });
    $('#mobile_nav_overlay').click( function() { toggleMobileNav(); });
        
    function toggleMobileNav() {
        if ( mobNavOpn === true) { 
            $("#mobile_nav_toggle").attr("aria-expanded", "false");
            mobNavOpn = false; 
            closeMobileNav(); 
        } 
        else { 
            $("#mobile_nav_toggle").attr("aria-expanded", "true");
            mobNavOpn = true; 
            openMobileNav(); 
        }
    }
    
    function closeMobileNav() {
        TweenLite.to($('#mobile_nav nav'), 0.3, { right: "-100%", ease:Power1.easeIn });
        TweenLite.to($('#mobile_nav_overlay'), 0.2, { opacity: 0, ease:Power1.easeIn, onComplete: function() { $('#mobile_nav_overlay').css({ display: "none" }); } });
        
        mmResetAll();
    }
    
    function openMobileNav() {
		
		// find which level to open the menu at using the current url path
		var thisPage = window.location.pathname,
			thisActiveMenuID = "null",
			activatedUlItem;
			
		if (thisPage.length > 1) { //if not found, open at level 0
			mobMenuULs.forEach(function (value) {
				if (thisPage.indexOf(value.name) !== -1) {
					thisActiveMenuID = value.hierarchy;
					mobMenuActiveLevel = parseInt(thisActiveMenuID.substring(thisActiveMenuID.length - 3, thisActiveMenuID.length - 2));
				}
			});
		} 
		
        $('#mobile_nav_overlay').css({ display: "block" });
        TweenLite.to($('#mobile_nav nav'), 0.35, { right: 0, ease:Power1.easeOut, onComplete: function () {
				if (thisActiveMenuID !== "null" ) {
					activatedUlItem = $("#" + thisActiveMenuID);
					openNextMenu(activatedUlItem);
				}
				else {
					setFocus();
				}
			}
		});
        TweenLite.to($('#mobile_nav_overlay'), 0.2, { opacity: 1, ease:Power1.easeOut });
        
        // aria
        $("#mobileNavHolder").children("ul").children("li").children("a").attr("tabindex", 0);
        $("#mobileNavHolder").attr("aria-hidden", "false");
    }
	
	function setFocus() { // not from return button
		if (mobMenuActiveLevel > 0) {
			$("#mobNavReturn" + mobMenuActiveLevel).focus();
		}
		else {
			$("#mobileNavLink1").focus();
		} 
	}
	
	function openNextMenu(activatedUlItem) {
		
			//---------------- parent must be visible
			if ( mobMenuActiveLevel === 2 && activatedUlItem.parent().parent().attr("aria-hidden") === "true") {
				activatedUlItem.parent().parent().attr("aria-hidden", "false");
				activatedUlItem.parent().parent().css({ display: "block", right: "0%" });
				
				$("#mobNavReturn" + (mobMenuActiveLevel - 1)).css({ display: "block", right: "0%" });
				$("#mobNavTitleL" + (mobMenuActiveLevel - 1)).css({ display: "block", right: "0%" });
			}
			
			//---------------- css / visual
			var thisTitle = activatedUlItem.parent().children("a").html();
			shortenTitle();
			
			function shortenTitle () {
				$("#mobNavTitleL" + mobMenuActiveLevel).html( '<h3>' + thisTitle + '</h3>');
				
				if ( $("#mobNavTitleL" + mobMenuActiveLevel).height() > 74 ) {
					thisTitle = thisTitle.substring(0, thisTitle.length - 6) + "...";
					shortenTitle();
				}
			}
			
			$("#menuLayerCover").css({ display: "block" });
			$("#mobile_nav nav").css({backgroundColor: "#ccc" }); 
			activatedUlItem.parent().parent().css({backgroundColor: "#ccc" });
			
			activatedUlItem.css({ display: "block" });
			$("#mobNavReturn" + mobMenuActiveLevel).css({ display: "block" });
			$("#mobNavTitleL" + mobMenuActiveLevel).css({ display: "block" });
			
			TweenLite.to(activatedUlItem, 0.35, {right: "0%", ease:Power1.easeOut });
			TweenLite.to($("#mobNavReturn" + mobMenuActiveLevel), 0.35, { right: "0%", ease:Power1.easeOut });
			TweenLite.to($("#mobNavTitleL" + mobMenuActiveLevel), 0.35, {right: "0%", ease:Power1.easeOut });
			
			//---------------- aria / non-visual
			//------ lists and links
			$("#mobile_nav nav ul").find("a").attr("tabindex", -1);
			$("#mobile_nav nav").children("ul").children("li").children("a").attr("aria-hidden", "true");
			activatedUlItem.children("li").children("a").attr("tabindex", 0);
			activatedUlItem.attr("aria-hidden", "false");
			//------ buttons and title
			$(".mobNavReturn").attr("aria-hidden", "true").attr("tabindex", -1);
			$(".mobNavTitle").attr("aria-hidden", "true");
			$("#mobNavReturn" + mobMenuActiveLevel).attr("aria-hidden", "false").attr("tabindex", 0);
			$("#mobNavTitleL" + mobMenuActiveLevel).attr("aria-hidden", "false");
			
			//---------------- specific to level of depth
			if ( mobMenuActiveLevel === 2 ) {
				$("#mobNavReturn1").css({ backgroundColor: "#ba2c43", color: "#ccc" });
				$("#mobile_nav nav").children("ul").children("li").children("ul").children("li").children("a").attr("aria-hidden", "true");
			}
			// for setting focus after click of back button to first link of correct menu
			else { l1Menu = activatedUlItem.attr("id"); } 
			
			//---------------- set focus (once transform complete!)
			TweenLite.delayedCall(0.35, function() { 
				$("#menuLayerCover").css({ display: "none" });
				setFocus(); 
			});
		}
    
    //------------------------------------------- setup
    
    function eMobileNav() {
        
        // prevent cycle-through of hidden links
        $("#mobile_nav nav ul").find("a").attr("tabindex", -1);

        $("#mobile_nav nav ul").find("li").each( function() {
            
            // applies to all links with hidden menu
            if ( $(this).children("ul").length !== 0) {
                
                if ($(this).parent().attr("id") === "mobileNavLevel0") { 
                    topLevelMenus += 1;
                    $(this).children("ul").addClass("mobMenuUL1"); 
                    $(this).children("ul")[0].id = "mobMenuUL1_" + topLevelMenus;
                }
                else { 
					secondLevelMenus += 1;
					$(this).children("ul").addClass("mobMenuUL2"); 
					$(this).children("ul")[0].id = "mobMenuUL2_" + secondLevelMenus;
				}
                
    //------------------------------------------- open sub-menus

                $(this).children("a").click( function(e) {
                    e.preventDefault();
                    
					var activatedUlItem = $(this).parent().children("ul");
                    
					mobMenuActiveLevel += 1; 
					openNextMenu(activatedUlItem);
                });
            }
        });
        
        // Hide sub menus
        $(".mobNavReturn").click( function(e) {
			
            //---------------- css / visual
            $("#mobile_nav nav").css({ backgroundColor: "#f4f4f4" });
			
			$(".mobMenuUL" + mobMenuActiveLevel).each(function (index) {
				TweenLite.to($(".mobMenuUL" + mobMenuActiveLevel), 0.35, {right: "-100%", ease:Power1.easeIn, onComplete: function() { $(".mobMenuUL" + mobMenuActiveLevel).css({display: ""}); } });
			});
            
            TweenLite.to($("#mobNavReturn" + mobMenuActiveLevel), 0.35, { right: "-100%", ease:Power1.easeIn, onComplete: function() { $("#mobNavReturn" + mobMenuActiveLevel).css({display: "none"}); } });
            TweenLite.to($("#mobNavTitleL" + mobMenuActiveLevel), 0.35, { right: "-100%", ease:Power1.easeIn, onComplete: function() { $("#mobNavTitleL" + mobMenuActiveLevel).css({display: "none"}) } });
            
            //---------------- aria / non-visual
            $("#mobile_nav nav ul").find("a").attr("tabindex", -1);
            $("#mobile_nav nav ul li").find("ul").attr("aria-hidden", "true");
            $(".mobNavReturn").attr("aria-hidden", "true").attr("tabindex", -1);;
            $(".mobNavTitle").attr("aria-hidden", "true");
            
            //---------------- specific to level of depth
            if ( mobMenuActiveLevel == 1 ) { 
                TweenLite.to($("#mobNavReturn"), 0.35, { right: "-100%", ease:Power1.easeIn });
                $("#mobileNavLevel0").css({ backgroundColor: "#f4f4f4" });
                
                $("#mobileNavHolder").children("ul").children("li").children("a").attr("tabindex", 0);
                $("#mobile_nav nav").children("ul").children("li").children("a").attr("aria-hidden", "false");
                
                TweenLite.delayedCall(0.35, function() { $("#mobileNavLink1").focus(); }); // set focus
            }
            else {
                $("#mobNavReturn1").css({ backgroundColor: "#E83754", color: "#fff" });
                $(".mobMenuUL1").css({ backgroundColor: "#f4f4f4" });
                
                $("#mobNavTitleL1").attr("aria-hidden", "false");
                $("#mobNavReturn1").attr("aria-hidden", "false").attr("tabindex", 0);
                $("#" + l1Menu).attr("aria-hidden", "false");
                $("#" + l1Menu).children("li").children("a").attr("tabindex", 0);
                $("#mobile_nav nav").children("ul").children("li").children("ul").children("li").children("a").attr("aria-hidden", "false");
                
                TweenLite.delayedCall(0.35, function() { $("#mobNavReturn" + (mobMenuActiveLevel - 1)).focus(); }); // set focus
            }
            
            TweenLite.delayedCall(0.5, function() { mobMenuActiveLevel -= 1; });
        });
    }

    // reset on close
    function mmResetAll() {
        //---------------- css / visual
        $("#mobile_nav nav").css({ backgroundColor: "#f4f4f4" });
        $("#mobileNavLevel0").css({ backgroundColor: "#f4f4f4" });
        $(".mobMenuUL1").css({ right: "-100%", backgroundColor: "#f4f4f4" });
        $(".mobMenuUL2").css({ right: "-100%" });
        $(".mobNavTitle").css({ right: "-100%" });
        $(".mobNavReturn").css({ right: "-100%", backgroundColor: "#E83754", color: "#fff" });
        
        //---------------- aria / non-visual
        $("#mobile_nav nav ul").find("a").attr("tabindex", -1);
        $("#mobile_nav nav ul li").find("ul").attr("aria-hidden", "true");
        $(".mobNavReturn").attr("tabindex", -1);
        $(".mobNavTitle").attr("aria-hidden", "true");
        $("#mobileNavHolder").attr("aria-hidden", "true");
        
        mobMenuActiveLevel = 0;
    }
	
	// close when focus moves to rest of page
	function focusMoveCheck() {
		if ( mobNavOpn == true) { 
            $("#mobile_nav_toggle").attr("aria-expanded", "false");
            mobNavOpn = false; 
            closeMobileNav(); 
        }
	}
    
    $("#user_controls").find("a").focus(focusMoveCheck);
    $("#main").find("a").focus(focusMoveCheck);
	$("#top_nav_e").find("a").focus(focusMoveCheck);
	

//****************************************************************************************************************************
//--------------------------------------------------------------------- DESKTOP MENU
//****************************************************************************************************************************
    
//--------------------------------------------------------------------- Keyboard & screenreader show hide

    function eNav() {

        // show and hide inner menus on focus of parent / next parent links
        $("#acc_menubar").find("li").each( function(index) {
            // applies to all links with hidden menu, plus top level (logo, shop)
            if ( $(this).children("ul").length != 0 || $(this).parent().attr("id") == "acc_menubar" ) {
                
                $(this).children("a").focus(function(){
                    
                    // show hide inner lists
                    $(this).parent().prev().children("ul")
                        .attr('aria-hidden', 'true')
                        .css({ 'display':'none' })
                        .find('a').attr('tabIndex',-1);
                    $(this).parent().next().children("ul")
                        .attr('aria-hidden', 'true')
                        .css({ 'display':'none' })
                        .find('a').attr('tabIndex',-1);
                    $(this).next('ul')
                        .attr('aria-hidden', 'false')
                        .css({ 'display':'block' })
                        .find('a').attr('tabIndex',0);
                });
                
                // special for very first & last <li> el. Assumes this order of elements is fixed for the forseeable!
                $("#donate_button").focus(function(){
                    $("#acc_menubar").children("li").children("ul").css({ "display": "none"});
                });
                
                $("#top_nav").children("div").children("div").children("a").focus(function() {
                    $("#acc_menubar").children("li").children("ul").css({ "display": "none"});
                });
            }
        });
     
        // second layer hidden menus (level 3) - close when focus moves to sibling elements. 
        // (Tabindex attr added for each link as you tab along.)
        $(".siblingSecondLayer").focus(function(){
            if ($(this).parent().prev().children("ul").length > 0) {
                $(this).parent().prev().children("ul")
                .attr('aria-hidden', 'true')
                .css({ 'display':'none' })
                .find('a').attr('tabIndex',-1);
            }
            else if ($(this).parent().next().children("ul").length > 0) {
                $(this).parent().next().children("ul")
                .attr('aria-hidden', 'true')
                .css({ 'display':'none' })
                .find('a').attr('tabIndex',-1);
            }
        });
       
        
//--------------------------------------------------------------------- Mouse & screen show hide     
        
        // replace show menu on hover
        $("#acc_menubar").find("li").each( function(index) {
            
            if ( $(this).children("ul").length != 0 ) {
                
                $(this).children("ul").css({ "display":"none", backgroundColor: "#E83754" });             
                
                $(this).mouseover( function() { 
                    $(this).children("ul").css({"display":"block"}).attr('aria-hidden', 'false');
                });
                $(this).mouseout( function() { 
                    $(this).children("ul").css({"display":"none"}).attr('aria-hidden', 'true'); 
                });
            }
        });
        
        
//--------------------------------------------------------------------- Highlighting
        
        // style focused item layer 2
        $("#acc_menubar").children("li").children("ul").children("li").children("a").each( function() {
            $(this).addClass("navListLinkL2");
            $(this).parent().css({ backgroundColor: "#2c2829"});
        });
        
        $("#acc_menubar").find(".navListLinkL2").focus(function() { layer2FocusStyle($(this)); });
        $("#acc_menubar").find(".navListLinkL2").mouseover(function() { layer2FocusStyle($(this)); });
        $("#acc_menubar").find(".navListLinkL2").mouseout(function() { layer2ClearFocus(); });
        
        function layer2FocusStyle(el) {
            layer2ClearFocus();
            el.parent().css({ backgroundColor: "#E83754"});
            el.css({ color: "#2c2829"});
        }
        
        function layer2ClearFocus() {
            $("#acc_menubar").find(".navListLinkL2").each( function() {
                $(this).parent().css({ backgroundColor: "#2c2829"});
                $(this).css({ color: "#ffffff"});
            });
        }
        
        // style focused item layer 3
        $("#acc_menubar").children("li").children("ul").children("li").children("ul").children("li").children("a").each( function() {
            $(this).addClass("navListLinkL3");
            $(this).css({ backgroundColor: "#E83754"});
        });
        
        $("#acc_menubar").find(".navListLinkL3").focus(function() { layer3FocusStyle($(this)); });
        $("#acc_menubar").find(".navListLinkL3").mouseover(function() { layer3FocusStyle($(this)); });
        $("#acc_menubar").find(".navListLinkL3").mouseout(function() { layer3ClearFocus(); });
        
        function layer3FocusStyle(el) {
            layer3ClearFocus();
            el.css({ backgroundColor: "#2c2829"});
        }
        
        function layer3ClearFocus() {
            $("#acc_menubar").find(".navListLinkL3").each( function() {
                $(this).css({ backgroundColor: "#E83754"});
            });
        }

     }
});

///var/www/philharmonia.co.uk/core/js/email.js
var email = {
	send: function(subject,message,address) {
		"use strict";
		if (email==null) {
			address = 'douglas.robertson@philharmonia.co.uk';
		}
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/email/send.php',
			data:{
				subject:subject,
				message:message,
				email:address
			},
			success:function(response) {
				//console.log(response);
			}
		});
	}
};

///var/www/philharmonia.co.uk/core/js/expander.js
/**
 * toggle expand / contract on expanders (see expander class)
 *
 * @id:Int - id of expander block
 */
function expand(id) {
	"use strict";
	$('#expander'+id+' .expander_break').css('width', $('#expander'+id+' .expander_break').width()+'px');
	$('#expander'+id+' .expander_break').slideToggle();
	
	if ($('#expander_toggle_'+id+' img').attr('src') === BASE+'/images/expander_close.png') {
		$('#expander_toggle_'+id+' img').attr('src', BASE+'/images/expander.png');
		$('html, body').animate({scrollTop: $("#expander"+id).offset().top-100});
	} else {
		$('#expander_toggle_'+id+' img').attr('src', BASE+'/images/expander_close.png');
	}
}

///var/www/philharmonia.co.uk/core/js/help.js
var help = {
	init:function() {
		$('a.help').each(function() {
			$(this).bind('click', function(event) {
				help.load(event);
			});
		});
	},
	
	load:function(e) {
		var target = e.target.getAttribute("rel");
		var helpbox = document.createElement("div");
		$(helpbox).addClass('helpbox');
		$(helpbox).css({
			left:mouseX-100,
			top:mouseY+10
		});
		$('body').append(helpbox);
		console.log(help.text(target));
		$(helpbox).html("<div class='helparrow'></div><p>"+help.text(target)+"</p>");
		
		$(document).bind('mousedown', function(event) {
			help.close(event);
		});
	},
	
	close:function(e) {
		if (e.target.getAttribute('href')==null) {
			$('.helpbox').remove();
		}
	},
	
	text: function(target) {
		var output = {
			discount: 'When you buy a ticket to 3 or more concerts you will receive a 10% discount on the face-value of the tickets. The more tickets you buy the bigger the discount!<br /><br /><a href="'+BASE+'/concerts/booking/subscriptions">Find out more</a>',
			
			transaction_fee: '',
			
			reset_password: 'Your account has been flagged as needing a password confirmation. It may be that you have requested a forgotten password email reminder or you have not logged in to the site before.',
			
			telephone: "It's possible we may need to contact you urgently in the unlikely event that a change or cancellation affects any performances you have booked tickets for.",
			
			human_test: "In order to protect our website against <a href=http://simple.wikipedia.org/wiki/Spamming' target='_blank'>spammers</a> we need to check that this form is being completed by a human user. Apologies for the inconvenience.",
			
			data_protection: "Your privacy is important to us, we want to make sure we only use your data in ways you decide. To find out more please read our <a href='/privacy'>Privacy Policy</a>.",
			
			donation: "Here you can add a small donation to your basket which will provide invaluable support to the Philharmonia Orchestra. <a href='/support/donate'>Find out more.</a>",
			
			not_in_season: "This concert is not in the standard Philharmonia season and therefore is not eligible for our multi-ticket discount offer."
		};
		return output[target];
	}
}

$(document).ready(function() {
	help.init();
});

///var/www/philharmonia.co.uk/core/js/homePage_layout.js
var hpLRmargin = 10;

function setSizes() {
    
        // column widths large screens
        if ( $("#top_nav_e").css("font-size") == "4px" ) {
            
			// get rid of any css that has been set in other sizes and does not apply
            $("#homePageWrap .grid-blocks").css({ float: "", width: "", marginRight: "" });
            $("#homePageWrap .grid-last").css({ marginRight: "" });
			$("#homePageWrap .banner").css({ marginRight: "", width: "" });
			$("#homePageWrap #hpShopModule").css({ width: "", marginRight: "" });
			
			if ( $("#hpModule2").find("#lead").children().length > 0) { /* swap block order*/
				var leadContent = $("#hpModule2").html(),
					filmsContent = $("#hpModule1").html();
				$("#hpModule1").html(leadContent);
				$("#hpModule2").html(filmsContent);
				$("#hpFilmModule").removeClass("grid-last");
			}

            for (var i = 1; i < 12; i++) {
                $("#homePageWrap .c" + i).each( function(index) {
                    
					var thisW = ($(this).parent().parent().innerWidth() ) / 12 * i;
                    
                    if ( $(this).hasClass("last") && $(this).parent().parent().attr("id") === "homepageModuleList") { 
						thisW -= 6; 
					} else { 
						thisW -= hpLRmargin; 
					}
                    $(this).css({ width: thisW, float: "", margin: "" });
                });  
            }
        }

        // medium screens < 1024
        else if ( $("#top_nav_e").css("font-size") == "3px" ) {
			
			if ( $("#hpModule1").find("#lead").children().length > 0) { /* swap block order*/
				var leadContent = $("#hpModule1").html(),
					filmsContent = $("#hpModule2").html();
				$("#hpModule2").html(leadContent);
				$("#hpModule1").html(filmsContent);
				$("#hpFilmModule").addClass("grid-last");
			}
			
            for (var i = 1; i < 12; i++) {
                $("#homePageWrap .c" + i).css({ width: "100%", margin: "0px 0px 10px 0px"});
            }

			var halfW = ($("#homePageWrap").innerWidth() - (hpLRmargin * 3)) / 2;
			
            $("#homePageWrap .grid-blocks").css({ float: "left", width: halfW + "px", marginRight: 10 });
            $("#homePageWrap .grid-last").css({ marginRight: 0 });
			$("#homePageWrap .bookNow .c5").css({ width: $("#homePageWrap .bookNow .c5").parent().outerWidth(false) / 12 * 5, float: "left" });
            $("#homePageWrap .bookNow .c7").css({ width: ($("#homePageWrap .bookNow .c7").parent().outerWidth(false) / 12 * 7) - hpLRmargin, float: "left" });
        }
        // small screens < 600
        else {
            $("#homePageWrap .grid-blocks").css({ width: "100%", marginRight: 0 });
			$("#homePageWrap .banner").css({ width: "100%", marginRight: 0 });
			$("#homePageWrap #hpShopModule").css({ width: "100%", marginRight: 0 });
			
            $("#homePageWrap .bookNow .c4").css({ width: "100%", float: "none" });
            $("#homePageWrap .bookNow .c5").css({ width: "100%", float: "none", marginBottom: 0 });
            $("#homePageWrap .bookNow .c5").css({ width: "100%", float: "none", marginBottom: 0 });
            $("#homePageWrap .bookNow .c7").css({ width: "100%", float: "none", marginBottom: 0 });
        }
}

$(document).ready(function() {
    setSizes();
    $(window).resize( function() { setSizes(); }); 
});

///var/www/philharmonia.co.uk/core/js/js_php_bridge.js
/*******************
* expose a Js string to a PHP function
*******************/
function jsPhpBridge(func, args, callback) {
	"use strict";
	$.ajax({
		type:'POST',
		url:BASE+'/lib/functions/php_function_call.php',
		data:{
			func:func,
			args:args
		},
		success:function(data) {
			callback(data);
		}
	});
}

///var/www/philharmonia.co.uk/core/js/maps.js
var maps = {
	
	geocoder:null,
	latlng:null,
	map:null,
	
	init: function(lat, long) {
		"use strict";
		maps.geocoder = new google.maps.Geocoder();
		if (lat!=null && long!=null) {
			maps.latlng = new google.maps.LatLng(lat,long);
		} else {
			maps.latlng = new google.maps.LatLng(51.505652,-0.116439);
		}
		var mapOptions = {
			center: maps.latlng,
			zoom: 14,
			disableDefaultUI: true,
			zoomControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        maps.map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
	},
	
	geocode:function(address) {
		"use strict";
		maps.geocoder.geocode( { 'address': address}, function(results, status) {
			if (status === google.maps.GeocoderStatus.OK) {
				maps.map.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: maps.map,
					position: results[0].geometry.location
				});
			} else {
				console.log("Geocode was not successful for the following reason: " + status);
			}
		});
	}
};
var venue_address = null;
var venue_lat = null;
var venue_long = null;
$(document).ready(function() {
	"use strict";
	if ($('#venue').length>0 && venue_address!==null) {
		maps.init();
		maps.geocode(venue_address);
		
	} else if ($('#venue').length>0 && venue_lat!==null) {
		// if longitude and latitude available
		maps.init(venue_lat, venue_long);
		maps.map.setCenter(maps.latlng);
		var marker = new google.maps.Marker({
			map: maps.map,
			position: maps.latlng
		});
	}	
});

///var/www/philharmonia.co.uk/core/js/modal.js
/****************
* loads a modal dialog over the page (positioned center)
*
* @id:int = id of record that needs editing
* @form:String = path to form (base /lib/forms) to be called via ajax
* @size:Strng = auto / small / medium / large
* @data:Object = extra vars to send to form
*****************/
function modal(id, form, size, data) {
	"use strict";
	size = (size==null) ? 'auto' : size;
	
	var alldata = {id:id};
	if (data!==null) {
		$.extend(alldata, data);
	}

	$('#modal').css({
		width:'100px',
		height:'100px',
		padding:'20px'
	});
	$('#screener').css({opacity:0});
	$('#screener').show();
	$('#screener').animate({opacity:0.8});
	
	$('#modal_inner').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
	$('#modal_wrap').fadeIn();
	
	$.ajax({
		url:BASE+'/lib/forms/'+form+'.php',
		type:'POST',
		data:alldata,
		success:function(response) {
			if (form=='login') {
				var width = 400;
				var height = 220;
				var top = 100;
			} else if (form=='info/data') {
				var width = 600;
				var height = $(window).innerHeight()-220;
				var top = 70;
			} else if (form=='cart/select_seat') {
				var width = $(window).innerWidth()-100;
				var height = $(window).innerHeight()-100;
				var top = 30
			} else if (form=='users/concert_reminders') {
				var width = 500;
				var height = 320;
				var top = 100;
			} else if (form=='paris/map') {
				// var width = $(window).innerWidth()-100;
				var width="90%";
				var height = $(window).innerHeight()-130;
				var top = 60;
			} else {
				var width = $(window).innerWidth()-300;
				var height = $(window).innerHeight()-220;
				var top = 70;
			}
			$('#modal_wrap').css({	
				top:top
			});
			$('#modal').animate({
				width:width,
				height:height
			}, function() {
				$('#modal_inner').hide();
				var cross = "<img src='"+BASE+"/core/images/icons/cross.png' onclick='exitModal()' class='modal_cross' />";
				var clearer = "<div style='clear:both'></div>";
				$('#modal_inner').html(cross+response+clearer);
				$('#modal_inner').fadeIn(function() {
					help.init();
					if (form=='login') {
						$("#username").focus();
					}
					if (form=='users/data_protection') {
						$('.modal_cross').remove();
					}
				});
			});
		}
	});
}

/****************
* removes modal dialog from page
*****************/
function exitModal() {
	"use strict";
	$('#modal_wrap').fadeOut(500, function() {
		$('#modal_inner').html('');
	});
	$('#screener').fadeOut(1000);	
}

function resizeModal() {
	"use strict";
	position();
	$('#modal').animate({
		height:$('#modal_inner').innerHeight()+10 //-40
	});
	if (parseInt($('#modal').css('top'), 2)<0) {
		$('#modal').css('top', '35px');
		$('#modal').css('height', ($(window).height()-100)+'px');
	}
}



function modalSimple(data, params, callback) {
	"use strict";

	$('#modal').css({
		width:'100px',
		height:'100px'
	});
	$('#screener').css({opacity:0});
	$('#screener').show();
	$('#screener').animate({opacity:0.8});
	
	$('#modal_inner').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
	$('#modal_wrap').fadeIn();
	
	
	var width = (params!=null && params.width==null) ? $(window).innerWidth()-300 : params.width;
	var height = (params!=null && params.height==null) ? $(window).innerHeight()-220 : params.height;
	var top = (params!=null && params.top==null) ? 70 : params.top;
	
	$('#modal_wrap').css({	
		top:top
	});
	$('#modal').animate({
		width:width,
		height:height
	}, function() {
		$('#modal_inner').hide();
		var cross = "<img src='"+BASE+"/core/images/icons/cross.png' onclick='exitModal()' class='modal_cross' />";
		var clearer = "<div style='clear:both'></div>";
		$('#modal_inner').html(cross+data+clearer);
		$('#modal').css({
			padding:'0px',
			overflow:'hidden',
			overflowY:'hidden'
		});
		$('#modal_inner').fadeIn(function() {
			if (callback!=null) callback();
		});
	});
		
}


///var/www/philharmonia.co.uk/core/js/modules.js
var modules = {

	installAll:function() {
		"use strict";
		$.ajax({
			type:'POST',
			url:BASE+'/core/lib/ajax/admin/modules/install_all.php',
			success:function(response) {
				if (response==="") {
					alert('All modules installed');
				}
			}
		});
	},
	
	install:function(id) {
		"use strict";
		$.ajax({
			type:'POST',
			url:BASE+'/core/lib/ajax/admin/modules/install_module.php',
			data: {
				id:id
			},
			success:function(response) {
				if (response==="") {
					alert('Module installed');
					window.location.href = window.location.href;
				}
			}
		});
	}
};

///var/www/philharmonia.co.uk/core/js/onload.js
/****************
* called on page load
*****************/
$(document).ready(function() {
	"use strict";
	
	$('#screener').click(function() { exitModal(); });
	
	$(document).mousemove(function(e){
		mouseX = e.pageX;
		mouseY = e.pageY;
	});

	if (SUBDIR=='') {
		// position elements
		position();	
		
		// set up some content
		prepContent();
		
		// replace links on concert tickets
		concerts.linkify();
		
		// prep any video blocks
		video.onhover()	
		
		$(".page_image").lazyload({
			effect : "fadeIn"
		});
		
		$("#home_icon").bind('click', function() {
			document.location.href=BASE+"/";
		});
		
		$("#to_top_icon").bind('click', function() {
			window.scrollTo(0,0); 
		});
	}
	
});
var mouseX = 0;
var mouseY = 0;

function prepContent() {
	"use strict";
	// fix any placeholders in IE
	placeholderIEfix();
	
	// add switch function to tabs
	$('.tab').click(function(e) { 
		e.stopImmediatePropagation();                   
		e.preventDefault();
		e.stopPropagation();
		switchTab(this); 
	});
	
	$('a').each(function() {
		
		// loops through a tags and add href where missing
		if ($(this).attr('href')==null) {
			$(this).attr('href','javascript:void(0)');
		}
		// if onclick is specified then stop href's working
		if ($(this).attr('onclick')!=null) {
			$(this).click(function(e){
				e.stopImmediatePropagation();                   
				e.preventDefault();
				e.stopPropagation();          
			});
		}
	});	
}




///var/www/philharmonia.co.uk/core/js/onresize.js
/****************
* called each time the window is resized - primarily to reposition content
*****************/
$(window).resize(function() {
	"use strict";
	position();
	
	if (parseInt($('#modal').css('top'), 2)<0) {
		$('#modal').css('top', '35px');
		$('#modal').css('height', ($(window).height()-100)+'px');
	}
	
	setScreenSize();
});

$(window).bind('orientationchange', function() {
	"use strict";
	position();
	
	if (parseInt($('#modal').css('top'), 2)<0) {
		$('#modal').css('top', '35px');
		$('#modal').css('height', ($(window).height()-100)+'px');
	}
	
	setScreenSize();
});

function setScreenSize() {
	$.ajax({
		type:'POST',
		url:BASE+'/core/lib/ajax/screen_size.php',
		data:{
			width:$(window).innerWidth(),
			height:$(window).innerHeight()
		},
		success:function() {
			
		}
	});
}

///var/www/philharmonia.co.uk/core/js/pagination.js
/*******************************
* function to switch pages of multiple different paginator types (see /classes/pagination.php
*
* @id:String - unique id of pagination block (incase 2+ on same page)
* @type:String - type of pagination (count/alpha/date)
* @settings:Object - additional settings
*******************************/
var pagination_current_page = 0;
function pagination (id, type, settings) {
	"use strict";
	var loc = window.location.href;
	var path = loc.split('&')[0].split('?')[0];
	var query = "";
	if (loc.indexOf('?')!==-1) {
		query = loc.split('?')[1];
	} else if (loc.indexOf('&')!==-1) {
		query = loc.split('&')[1];
	}
	if (query!=="") {
		var split_query = query.split('&');
		for (var i=0; i<split_query.length; i++) {
			if (split_query[i].indexOf('date=')===0 || split_query[i].indexOf('p=')===0) {
				delete split_query[i];
			}
		}
		query = split_query.join('&').replace('&&', '&');
	}
	
	switch (type) {
		case 'date':
			$('.paginator_year').css('display','none'); 
			$('.paginator_month').css('display','none'); 
			
			$('#'+id+'_'+settings.year).css('display', 'block');
			if (settings.month!==null) {
				$('#'+id+'_'+settings.year+settings.month).css('display', 'block');
				pathChange(path+'?date='+settings.year+'_'+settings.month+"&"+query);
			} else {
				$('#'+id+'_'+settings.year+' .paginator_month').css('display', 'block');
				pathChange(path+'?date='+settings.year+"&"+query);
			}
			
			break;
		default:
		
			if (settings.letter) {
				$('.'+id+'_pages').css('display','none'); 
				$('#page_'+settings.letter+'_'+id).css('display', 'block');
				pathChange(path+'?p='+settings.letter+"&"+query);
				
			} else if (settings.direction) {

				if (settings.direction==='prev' && pagination_current_page>0) {
					$('.'+id+'_pages').css('display','none'); 
					pagination_current_page--;
					$('#page_'+pagination_current_page+'_'+id).css('display', 'block');
					
				} else if (settings.direction==='next' && document.getElementById('page_'+(pagination_current_page+1)+'_'+id)) {
					$('.'+id+'_pages').css('display','none'); 
					pagination_current_page++;
					$('#page_'+pagination_current_page+'_'+id).css('display', 'block');
				}
			}
			break;
	}
	
	$('html, body').animate({scrollTop: $(".pagination_block").offset().top-200}, 1000);
	
	
	
	// DODGY FIX - hide subnav on click 
	$('nav li ul').css('display','none');
	setTimeout(function() {
		$('nav li ul').css('display','');
	}, 500);
}


/******************
* switch between pages on a paginated table made from createDateTables of the Tabulate class
*
* @id:Int - page number OR :String 'all' to show all
********************/
function paginate(id, type) {
	if (id!='all') {
		$('.tablebody').hide();
		$('#page'+id).show();
		$('html, body').animate({scrollTop: $(".pagination").offset().top}, 1000);
	} else {
		$('.tablebody').show();	
	}
	if (type!=null) {
		$('#main').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
		$.ajax({
			type:'GET',
			url:DIR+'/modules/'+type+'.php',
			data:{
				p:id,
				paginate:true
			},
			success:function(response) {
				$('#main').html(response);
			}
		});
	}
	pathChange(window.location.href.split('&')[0].split('?')[0]+'?p='+id);
}

///var/www/philharmonia.co.uk/core/js/pathChange.js
function detectHistorySupport() {
	"use strict";
	return !!(window.history && history.pushState);
}

function pathChange(path, title) {	
	"use strict";
	title = (title==null) ? "Philharmonia Orchestra" : title;
	
	// if there is history support, use it
	if (detectHistorySupport()) {
		// change state and report that it worked
		if(window.history.pushState) {
			window.history.pushState("", title, path);
		} else {
			window.history.replaceState("", title, path);
		}		
		return true;

	// otherwise, report that the pathchange failed
	} else {
		return false;
		
	}
}

///var/www/philharmonia.co.uk/core/js/placeholderfix.js
function placeholderIEfix() {
	"use strict";
	if(!Modernizr.input.placeholder){
		$("input").each(function(){
			if($(this).val()==="" && $(this).attr("placeholder")!==""){
				$(this).val($(this).attr("placeholder"));
				$(this).focus(function(){
					if($(this).val()===$(this).attr("placeholder")) { $(this).val(""); }
				});
				$(this).blur(function(){
					if($(this).val()==="") { $(this).val($(this).attr("placeholder")); }
				});
			}
		});
	}
}


///var/www/philharmonia.co.uk/core/js/position.js
/**
 * Positions all nessecary elements according to screen/window size
 */
var scr = {};
function position() {
	"use strict";
	var sw = $(window).innerWidth();
	var sh = $(window).innerHeight();
	var sc = {};
	sc.left = sw/2;
	sc.top = sh/2;
	
	scr.width = $(window).innerWidth();
	scr.height = $(window).innerHeight();
		
	var mtop = sc.top-($('#modal').height()/2)-100;
	if (mtop<50) { mtop = 50; }

	if (!MOBILE) {
		// force min height on concert block so switching content doesnt cause page 'jerk'
		//$('.full_concert .concert_media').css('minHeight', $('.full_concert .concert_media .page_image').innerHeight()+'px');
		
		$('#desktop_nav > nav > ul > li > ul').css({
			left:($('#desktop_nav > nav > ul > li').width()-160)/2
		});
		
		// make footer appear at bottom of page
		$('#main').css('minHeight', ($(window).innerHeight()-490)+'px');
		
	} else {
		$('#mobile_nav').css('height', $(window).innerHeight()+50);
	}
	
	$('.carousel_element').width($('.carousel').innerWidth());
	$('.carousel_slider').width($('.carousel').innerWidth()*$('.carousel_element').length);
	var carousel_skip = $('.carousel').innerWidth();
	
	$('.carousel_slider').css({	left:0 });
			
	
	$('.film.film_listing p').each(function() {
		$(this).height($('.film_media img').innerHeight()-9);
	});
	
	$('#modal').css('maxHeight', $(window).innerHeight()-100);
}

$(window).load(function() {
	"use strict";
	position();
});

function positionBlocks() {
	"use strict";
	var main_width = Math.round(scr.width-((scr.width/100)*2))-20;
	var col_width = "";
	
	$('#main').width((main_width+20)+'px');

	if (main_width>1060) {
		$('article').width(Math.round(((main_width/3)*2)-(main_width/100)*5)+'px');
		$('article.mini').width(Math.round((main_width/3)-(main_width/100)*5)+'px');
		$('aside').width(Math.round((main_width/3)-(main_width/100)*5)+'px');
		col_width = (main_width)/3;
		
	} else if (main_width>710) {
		$('article').width((main_width)+'px');
		$('aside').width((main_width)+'px');
		$('article.mini').width(Math.round((main_width/2)-(main_width/100)*5)+'px');
		col_width = (main_width)/2;
		
	} else {
		$('article').width((main_width)+'px');
		$('aside').width((main_width)+'px');
		col_width = main_width;
	}
	
	$('#main').masonry({
		itemSelector : '.blocks',
		columnWidth: col_width
	});
}

///var/www/philharmonia.co.uk/core/js/switchLanguage.js
function switchLanguage(lang) {
	"use strict";
	$.ajax({
		type:'POST',
		url:BASE+'/lib/ajax/switch_language.php',
		data:{
			lang:lang
		},
		success:function() {
			window.location.href = window.location.href;
		}
	});
}

///var/www/philharmonia.co.uk/core/js/switchtabs.js
function switchTab(target) {
	"use strict";
	var t = target.id.replace('_tab', '');
	var rel = $(target).attr('rel');
	
	$('.'+rel+'_section').css('display', 'none');
	$('#'+t).fadeIn(1000);
	
	if (rel==='login') {
		$('.tab[rel='+rel+']').css('display', 'inline');
		$('#'+target.id).css('display', 'none');
	}
	
	$('#'+t+' .ckeditor').each(function() {
		CKEDITOR.instances[this.id].destroy(true); 
		CKEDITOR.replace(this.id, {
			toolbar:
				[
					['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Source' ],
					['UIColor']
				]
		});		
	});
	resizeModal();
}

///var/www/philharmonia.co.uk/core/js/tablet.js


///var/www/philharmonia.co.uk/core/js/touch.js
(function ($) {
    // Detect touch support
    $.support.touch = 'ontouchend' in document;
    // Ignore browsers without touch support
    if (!$.support.touch) {
    return;
    }
    var mouseProto = $.ui.mouse.prototype,
        _mouseInit = mouseProto._mouseInit,
        touchHandled;

    function simulateMouseEvent (event, simulatedType) { //use this function to simulate mouse event
    // Ignore multi-touch events
        if (event.originalEvent.touches.length > 1) {
        return;
        }
    event.preventDefault(); //use this to prevent scrolling during ui use

    var touch = event.originalEvent.changedTouches[0],
        simulatedEvent = document.createEvent('MouseEvents');
    // Initialize the simulated mouse event using the touch event's coordinates
    simulatedEvent.initMouseEvent(
        simulatedType,    // type
        true,             // bubbles                    
        true,             // cancelable                 
        window,           // view                       
        1,                // detail                     
        touch.screenX,    // screenX                    
        touch.screenY,    // screenY                    
        touch.clientX,    // clientX                    
        touch.clientY,    // clientY                    
        false,            // ctrlKey                    
        false,            // altKey                     
        false,            // shiftKey                   
        false,            // metaKey                    
        0,                // button                     
        null              // relatedTarget              
        );

    // Dispatch the simulated event to the target element
    event.target.dispatchEvent(simulatedEvent);
    }
    mouseProto._touchStart = function (event) {
    var self = this;
    // Ignore the event if another widget is already being handled
    if (touchHandled || !self._mouseCapture(event.originalEvent.changedTouches[0])) {
        return;
        }
    // Set the flag to prevent other widgets from inheriting the touch event
    touchHandled = true;
    // Track movement to determine if interaction was a click
    self._touchMoved = false;
    // Simulate the mouseover event
    simulateMouseEvent(event, 'mouseover');
    // Simulate the mousemove event
    simulateMouseEvent(event, 'mousemove');
    // Simulate the mousedown event
    simulateMouseEvent(event, 'mousedown');
    };

    mouseProto._touchMove = function (event) {
    // Ignore event if not handled
    if (!touchHandled) {
        return;
        }
    // Interaction was not a click
    this._touchMoved = true;
    // Simulate the mousemove event
    simulateMouseEvent(event, 'mousemove');
    };
    mouseProto._touchEnd = function (event) {
    // Ignore event if not handled
    if (!touchHandled) {
        return;
    }
    // Simulate the mouseup event
    simulateMouseEvent(event, 'mouseup');
    // Simulate the mouseout event
    simulateMouseEvent(event, 'mouseout');
    // If the touch interaction did not move, it should trigger a click
    if (!this._touchMoved) {
      // Simulate the click event
      simulateMouseEvent(event, 'click');
    }
    // Unset the flag to allow other widgets to inherit the touch event
    touchHandled = false;
    };
    mouseProto._mouseInit = function () {
    var self = this;
    // Delegate the touch handlers to the widget's element
    self.element
        .on('touchstart', $.proxy(self, '_touchStart'))
        .on('touchmove', $.proxy(self, '_touchMove'))
        .on('touchend', $.proxy(self, '_touchEnd'));

    // Call the original $.ui.mouse init method
    _mouseInit.call(self);
    };
})(jQuery);// JavaScript Document

///var/www/philharmonia.co.uk/core/js/upload.js
var uploader_id = "";
function uploadFileSelected(id, extensions) {
	"use strict";
	uploader_id = id;
	var file = document.getElementById(id).files[0];
	$('#upload_details').show();
	if (file) {
		var file_parts = file.name.split('.');
		var extension = file_parts[file_parts.length-1].toLowerCase();

		if (extensions===null) {
			extensions = [];
		} else {
			extensions = (typeof extensions==='object') ? extensions : new Array(extensions);
		}
		if (extensions.indexOf(extension)!==-1) {		
			var fileSize = 0;
			if (file.size > 1024 * 1024) {
				fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
			} else {
				fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
			}
			$('#fileName').html('<strong>Name</strong>: ' + file.name);
			$('#fileSize').html('<strong>Size</strong>: ' + fileSize);
			$('#fileType').html('<strong>Type</strong>: ' + file.type);
		} else {
			$('#fileName').html('');
			$('#fileSize').html('');
			$('#fileType').html('');
			alert('That file type is not allowed. Please choose one of the following: '+extensions.join(' / ').toUpperCase());
		}			
	}
}

function uploadFile(target, callback) {
	"use strict";
	var fd = new FormData();
	
	fd.append('file', document.getElementById(uploader_id).files[0]);
	fd.append('target', target);
	fd.append('function', 'upload_file');
	
	if (callback===null) { callback = uploadComplete; }
	
	var xhr = new XMLHttpRequest();
	xhr.upload.addEventListener("progress", uploadProgress, false);
	xhr.addEventListener("load", callback, false);
	xhr.addEventListener("error", uploadFailed, false);
	xhr.addEventListener("abort", uploadCanceled, false);
	xhr.open("POST", BASE+"/lib/functions/upload_file.php");
	xhr.send(fd);
}

function uploadProgress(evt) {
	"use strict";
	if (evt.lengthComputable) {
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
	} else {
		document.getElementById('progressNumber').innerHTML = 'unable to compute';
	}
}

function uploadComplete(evt) {
	"use strict";
	if (evt) {
		$('#audiofile').val(evt.target.responseText);
	}
}

function uploadFailed(evt) {
	"use strict";
	alert("There was an error attempting to upload the file.");
}

function uploadCanceled(evt) {
	"use strict";
	alert("The upload has been canceled by the user or the browser dropped the connection.");
}

///var/www/philharmonia.co.uk/js/libs/masonry.pkgd.min.js
/*!
 * Masonry PACKAGED v3.1.2
 * Cascading grid layout library
 * http://masonry.desandro.com
 * MIT License
 * by David DeSandro
 */

(function(t){"use strict";function e(t){if(t){if("string"==typeof n[t])return t;t=t.charAt(0).toUpperCase()+t.slice(1);for(var e,o=0,r=i.length;r>o;o++)if(e=i[o]+t,"string"==typeof n[e])return e}}var i="Webkit Moz ms Ms O".split(" "),n=document.documentElement.style;"function"==typeof define&&define.amd?define(function(){return e}):t.getStyleProperty=e})(window),function(t){"use strict";function e(t){var e=parseFloat(t),i=-1===t.indexOf("%")&&!isNaN(e);return i&&e}function i(){for(var t={width:0,height:0,innerWidth:0,innerHeight:0,outerWidth:0,outerHeight:0},e=0,i=s.length;i>e;e++){var n=s[e];t[n]=0}return t}function n(t){function n(t){if("string"==typeof t&&(t=document.querySelector(t)),t&&"object"==typeof t&&t.nodeType){var n=r(t);if("none"===n.display)return i();var h={};h.width=t.offsetWidth,h.height=t.offsetHeight;for(var p=h.isBorderBox=!(!a||!n[a]||"border-box"!==n[a]),u=0,f=s.length;f>u;u++){var c=s[u],d=n[c],l=parseFloat(d);h[c]=isNaN(l)?0:l}var m=h.paddingLeft+h.paddingRight,y=h.paddingTop+h.paddingBottom,g=h.marginLeft+h.marginRight,v=h.marginTop+h.marginBottom,_=h.borderLeftWidth+h.borderRightWidth,b=h.borderTopWidth+h.borderBottomWidth,E=p&&o,L=e(n.width);L!==!1&&(h.width=L+(E?0:m+_));var T=e(n.height);return T!==!1&&(h.height=T+(E?0:y+b)),h.innerWidth=h.width-(m+_),h.innerHeight=h.height-(y+b),h.outerWidth=h.width+g,h.outerHeight=h.height+v,h}}var o,a=t("boxSizing");return function(){if(a){var t=document.createElement("div");t.style.width="200px",t.style.padding="1px 2px 3px 4px",t.style.borderStyle="solid",t.style.borderWidth="1px 2px 3px 4px",t.style[a]="border-box";var i=document.body||document.documentElement;i.appendChild(t);var n=r(t);o=200===e(n.width),i.removeChild(t)}}(),n}var o=document.defaultView,r=o&&o.getComputedStyle?function(t){return o.getComputedStyle(t,null)}:function(t){return t.currentStyle},s=["paddingLeft","paddingRight","paddingTop","paddingBottom","marginLeft","marginRight","marginTop","marginBottom","borderLeftWidth","borderRightWidth","borderTopWidth","borderBottomWidth"];"function"==typeof define&&define.amd?define(["get-style-property/get-style-property"],n):t.getSize=n(t.getStyleProperty)}(window),function(t){"use strict";var e=document.documentElement,i=function(){};e.addEventListener?i=function(t,e,i){t.addEventListener(e,i,!1)}:e.attachEvent&&(i=function(e,i,n){e[i+n]=n.handleEvent?function(){var e=t.event;e.target=e.target||e.srcElement,n.handleEvent.call(n,e)}:function(){var i=t.event;i.target=i.target||i.srcElement,n.call(e,i)},e.attachEvent("on"+i,e[i+n])});var n=function(){};e.removeEventListener?n=function(t,e,i){t.removeEventListener(e,i,!1)}:e.detachEvent&&(n=function(t,e,i){t.detachEvent("on"+e,t[e+i]);try{delete t[e+i]}catch(n){t[e+i]=void 0}});var o={bind:i,unbind:n};"function"==typeof define&&define.amd?define(o):t.eventie=o}(this),function(t){"use strict";function e(t){"function"==typeof t&&(e.isReady?t():r.push(t))}function i(t){var i="readystatechange"===t.type&&"complete"!==o.readyState;if(!e.isReady&&!i){e.isReady=!0;for(var n=0,s=r.length;s>n;n++){var a=r[n];a()}}}function n(n){return n.bind(o,"DOMContentLoaded",i),n.bind(o,"readystatechange",i),n.bind(t,"load",i),e}var o=t.document,r=[];e.isReady=!1,"function"==typeof define&&define.amd?(e.isReady="function"==typeof requirejs,define(["eventie/eventie"],n)):t.docReady=n(t.eventie)}(this),function(){"use strict";function t(){}function e(t,e){for(var i=t.length;i--;)if(t[i].listener===e)return i;return-1}function i(t){return function(){return this[t].apply(this,arguments)}}var n=t.prototype;n.getListeners=function(t){var e,i,n=this._getEvents();if("object"==typeof t){e={};for(i in n)n.hasOwnProperty(i)&&t.test(i)&&(e[i]=n[i])}else e=n[t]||(n[t]=[]);return e},n.flattenListeners=function(t){var e,i=[];for(e=0;t.length>e;e+=1)i.push(t[e].listener);return i},n.getListenersAsObject=function(t){var e,i=this.getListeners(t);return i instanceof Array&&(e={},e[t]=i),e||i},n.addListener=function(t,i){var n,o=this.getListenersAsObject(t),r="object"==typeof i;for(n in o)o.hasOwnProperty(n)&&-1===e(o[n],i)&&o[n].push(r?i:{listener:i,once:!1});return this},n.on=i("addListener"),n.addOnceListener=function(t,e){return this.addListener(t,{listener:e,once:!0})},n.once=i("addOnceListener"),n.defineEvent=function(t){return this.getListeners(t),this},n.defineEvents=function(t){for(var e=0;t.length>e;e+=1)this.defineEvent(t[e]);return this},n.removeListener=function(t,i){var n,o,r=this.getListenersAsObject(t);for(o in r)r.hasOwnProperty(o)&&(n=e(r[o],i),-1!==n&&r[o].splice(n,1));return this},n.off=i("removeListener"),n.addListeners=function(t,e){return this.manipulateListeners(!1,t,e)},n.removeListeners=function(t,e){return this.manipulateListeners(!0,t,e)},n.manipulateListeners=function(t,e,i){var n,o,r=t?this.removeListener:this.addListener,s=t?this.removeListeners:this.addListeners;if("object"!=typeof e||e instanceof RegExp)for(n=i.length;n--;)r.call(this,e,i[n]);else for(n in e)e.hasOwnProperty(n)&&(o=e[n])&&("function"==typeof o?r.call(this,n,o):s.call(this,n,o));return this},n.removeEvent=function(t){var e,i=typeof t,n=this._getEvents();if("string"===i)delete n[t];else if("object"===i)for(e in n)n.hasOwnProperty(e)&&t.test(e)&&delete n[e];else delete this._events;return this},n.emitEvent=function(t,e){var i,n,o,r,s=this.getListenersAsObject(t);for(o in s)if(s.hasOwnProperty(o))for(n=s[o].length;n--;)i=s[o][n],i.once===!0&&this.removeListener(t,i.listener),r=i.listener.apply(this,e||[]),r===this._getOnceReturnValue()&&this.removeListener(t,i.listener);return this},n.trigger=i("emitEvent"),n.emit=function(t){var e=Array.prototype.slice.call(arguments,1);return this.emitEvent(t,e)},n.setOnceReturnValue=function(t){return this._onceReturnValue=t,this},n._getOnceReturnValue=function(){return this.hasOwnProperty("_onceReturnValue")?this._onceReturnValue:!0},n._getEvents=function(){return this._events||(this._events={})},"function"==typeof define&&define.amd?define(function(){return t}):"object"==typeof module&&module.exports?module.exports=t:this.EventEmitter=t}.call(this),function(t){"use strict";function e(){}function i(t){function i(e){e.prototype.option||(e.prototype.option=function(e){t.isPlainObject(e)&&(this.options=t.extend(!0,this.options,e))})}function o(e,i){t.fn[e]=function(o){if("string"==typeof o){for(var s=n.call(arguments,1),a=0,h=this.length;h>a;a++){var p=this[a],u=t.data(p,e);if(u)if(t.isFunction(u[o])&&"_"!==o.charAt(0)){var f=u[o].apply(u,s);if(void 0!==f)return f}else r("no such method '"+o+"' for "+e+" instance");else r("cannot call methods on "+e+" prior to initialization; "+"attempted to call '"+o+"'")}return this}return this.each(function(){var n=t.data(this,e);n?(n.option(o),n._init()):(n=new i(this,o),t.data(this,e,n))})}}if(t){var r="undefined"==typeof console?e:function(t){console.error(t)};t.bridget=function(t,e){i(e),o(t,e)}}}var n=Array.prototype.slice;"function"==typeof define&&define.amd?define(["jquery"],i):i(t.jQuery)}(window),function(t,e){"use strict";function i(t,e){return t[a](e)}function n(t){if(!t.parentNode){var e=document.createDocumentFragment();e.appendChild(t)}}function o(t,e){n(t);for(var i=t.parentNode.querySelectorAll(e),o=0,r=i.length;r>o;o++)if(i[o]===t)return!0;return!1}function r(t,e){return n(t),i(t,e)}var s,a=function(){if(e.matchesSelector)return"matchesSelector";for(var t=["webkit","moz","ms","o"],i=0,n=t.length;n>i;i++){var o=t[i],r=o+"MatchesSelector";if(e[r])return r}}();if(a){var h=document.createElement("div"),p=i(h,"div");s=p?i:r}else s=o;"function"==typeof define&&define.amd?define(function(){return s}):window.matchesSelector=s}(this,Element.prototype),function(t){"use strict";function e(t,e){for(var i in e)t[i]=e[i];return t}function i(t){for(var e in t)return!1;return e=null,!0}function n(t){return t.replace(/([A-Z])/g,function(t){return"-"+t.toLowerCase()})}function o(t,o,r){function a(t,e){t&&(this.element=t,this.layout=e,this.position={x:0,y:0},this._create())}var h=r("transition"),p=r("transform"),u=h&&p,f=!!r("perspective"),c={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"otransitionend",transition:"transitionend"}[h],d=["transform","transition","transitionDuration","transitionProperty"],l=function(){for(var t={},e=0,i=d.length;i>e;e++){var n=d[e],o=r(n);o&&o!==n&&(t[n]=o)}return t}();e(a.prototype,t.prototype),a.prototype._create=function(){this._transition={ingProperties:{},clean:{},onEnd:{}},this.css({position:"absolute"})},a.prototype.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},a.prototype.getSize=function(){this.size=o(this.element)},a.prototype.css=function(t){var e=this.element.style;for(var i in t){var n=l[i]||i;e[n]=t[i]}},a.prototype.getPosition=function(){var t=s(this.element),e=this.layout.options,i=e.isOriginLeft,n=e.isOriginTop,o=parseInt(t[i?"left":"right"],10),r=parseInt(t[n?"top":"bottom"],10);o=isNaN(o)?0:o,r=isNaN(r)?0:r;var a=this.layout.size;o-=i?a.paddingLeft:a.paddingRight,r-=n?a.paddingTop:a.paddingBottom,this.position.x=o,this.position.y=r},a.prototype.layoutPosition=function(){var t=this.layout.size,e=this.layout.options,i={};e.isOriginLeft?(i.left=this.position.x+t.paddingLeft+"px",i.right=""):(i.right=this.position.x+t.paddingRight+"px",i.left=""),e.isOriginTop?(i.top=this.position.y+t.paddingTop+"px",i.bottom=""):(i.bottom=this.position.y+t.paddingBottom+"px",i.top=""),this.css(i),this.emitEvent("layout",[this])};var m=f?function(t,e){return"translate3d("+t+"px, "+e+"px, 0)"}:function(t,e){return"translate("+t+"px, "+e+"px)"};a.prototype._transitionTo=function(t,e){this.getPosition();var i=this.position.x,n=this.position.y,o=parseInt(t,10),r=parseInt(e,10),s=o===this.position.x&&r===this.position.y;if(this.setPosition(t,e),s&&!this.isTransitioning)return this.layoutPosition(),void 0;var a=t-i,h=e-n,p={},u=this.layout.options;a=u.isOriginLeft?a:-a,h=u.isOriginTop?h:-h,p.transform=m(a,h),this.transition({to:p,onTransitionEnd:{transform:this.layoutPosition},isCleaning:!0})},a.prototype.goTo=function(t,e){this.setPosition(t,e),this.layoutPosition()},a.prototype.moveTo=u?a.prototype._transitionTo:a.prototype.goTo,a.prototype.setPosition=function(t,e){this.position.x=parseInt(t,10),this.position.y=parseInt(e,10)},a.prototype._nonTransition=function(t){this.css(t.to),t.isCleaning&&this._removeStyles(t.to);for(var e in t.onTransitionEnd)t.onTransitionEnd[e].call(this)},a.prototype._transition=function(t){if(!parseFloat(this.layout.options.transitionDuration))return this._nonTransition(t),void 0;var e=this._transition;for(var i in t.onTransitionEnd)e.onEnd[i]=t.onTransitionEnd[i];for(i in t.to)e.ingProperties[i]=!0,t.isCleaning&&(e.clean[i]=!0);if(t.from){this.css(t.from);var n=this.element.offsetHeight;n=null}this.enableTransition(t.to),this.css(t.to),this.isTransitioning=!0};var y=p&&n(p)+",opacity";a.prototype.enableTransition=function(){this.isTransitioning||(this.css({transitionProperty:y,transitionDuration:this.layout.options.transitionDuration}),this.element.addEventListener(c,this,!1))},a.prototype.transition=a.prototype[h?"_transition":"_nonTransition"],a.prototype.onwebkitTransitionEnd=function(t){this.ontransitionend(t)},a.prototype.onotransitionend=function(t){this.ontransitionend(t)};var g={"-webkit-transform":"transform","-moz-transform":"transform","-o-transform":"transform"};a.prototype.ontransitionend=function(t){if(t.target===this.element){var e=this._transition,n=g[t.propertyName]||t.propertyName;if(delete e.ingProperties[n],i(e.ingProperties)&&this.disableTransition(),n in e.clean&&(this.element.style[t.propertyName]="",delete e.clean[n]),n in e.onEnd){var o=e.onEnd[n];o.call(this),delete e.onEnd[n]}this.emitEvent("transitionEnd",[this])}},a.prototype.disableTransition=function(){this.removeTransitionStyles(),this.element.removeEventListener(c,this,!1),this.isTransitioning=!1},a.prototype._removeStyles=function(t){var e={};for(var i in t)e[i]="";this.css(e)};var v={transitionProperty:"",transitionDuration:""};return a.prototype.removeTransitionStyles=function(){this.css(v)},a.prototype.removeElem=function(){this.element.parentNode.removeChild(this.element),this.emitEvent("remove",[this])},a.prototype.remove=function(){if(!h||!parseFloat(this.layout.options.transitionDuration))return this.removeElem(),void 0;var t=this;this.on("transitionEnd",function(){return t.removeElem(),!0}),this.hide()},a.prototype.reveal=function(){delete this.isHidden,this.css({display:""});var t=this.layout.options;this.transition({from:t.hiddenStyle,to:t.visibleStyle,isCleaning:!0})},a.prototype.hide=function(){this.isHidden=!0,this.css({display:""});var t=this.layout.options;this.transition({from:t.visibleStyle,to:t.hiddenStyle,isCleaning:!0,onTransitionEnd:{opacity:function(){this.css({display:"none"})}}})},a.prototype.destroy=function(){this.css({position:"",left:"",right:"",top:"",bottom:"",transition:"",transform:""})},a}var r=document.defaultView,s=r&&r.getComputedStyle?function(t){return r.getComputedStyle(t,null)}:function(t){return t.currentStyle};"function"==typeof define&&define.amd?define(["eventEmitter/EventEmitter","get-size/get-size","get-style-property/get-style-property"],o):(t.Outlayer={},t.Outlayer.Item=o(t.EventEmitter,t.getSize,t.getStyleProperty))}(window),function(t){"use strict";function e(t,e){for(var i in e)t[i]=e[i];return t}function i(t){return"[object Array]"===f.call(t)}function n(t){var e=[];if(i(t))e=t;else if(t&&"number"==typeof t.length)for(var n=0,o=t.length;o>n;n++)e.push(t[n]);else e.push(t);return e}function o(t,e){var i=d(e,t);-1!==i&&e.splice(i,1)}function r(t){return t.replace(/(.)([A-Z])/g,function(t,e,i){return e+"-"+i}).toLowerCase()}function s(i,s,f,d,l,m){function y(t,i){if("string"==typeof t&&(t=a.querySelector(t)),!t||!c(t))return h&&h.error("Bad "+this.settings.namespace+" element: "+t),void 0;this.element=t,this.options=e({},this.options),this.option(i);var n=++v;this.element.outlayerGUID=n,_[n]=this,this._create(),this.options.isInitLayout&&this.layout()}function g(t,i){t.prototype[i]=e({},y.prototype[i])}var v=0,_={};return y.prototype.settings={namespace:"outlayer",item:m},y.prototype.options={containerStyle:{position:"relative"},isInitLayout:!0,isOriginLeft:!0,isOriginTop:!0,isResizeBound:!0,transitionDuration:"0.4s",hiddenStyle:{opacity:0,transform:"scale(0.001)"},visibleStyle:{opacity:1,transform:"scale(1)"}},e(y.prototype,f.prototype),y.prototype.option=function(t){e(this.options,t)},y.prototype._create=function(){this.reloadItems(),this.stamps=[],this.stamp(this.options.stamp),e(this.element.style,this.options.containerStyle),this.options.isResizeBound&&this.bindResize()},y.prototype.reloadItems=function(){this.items=this._itemize(this.element.children)},y.prototype._itemize=function(t){for(var e=this._filterFindItemElements(t),i=this.settings.item,n=[],o=0,r=e.length;r>o;o++){var s=e[o],a=new i(s,this);n.push(a)}return n},y.prototype._filterFindItemElements=function(t){t=n(t);for(var e=this.options.itemSelector,i=[],o=0,r=t.length;r>o;o++){var s=t[o];if(c(s))if(e){l(s,e)&&i.push(s);for(var a=s.querySelectorAll(e),h=0,p=a.length;p>h;h++)i.push(a[h])}else i.push(s)}return i},y.prototype.getItemElements=function(){for(var t=[],e=0,i=this.items.length;i>e;e++)t.push(this.items[e].element);return t},y.prototype.layout=function(){this._resetLayout(),this._manageStamps();var t=void 0!==this.options.isLayoutInstant?this.options.isLayoutInstant:!this._isLayoutInited;this.layoutItems(this.items,t),this._isLayoutInited=!0},y.prototype._init=y.prototype.layout,y.prototype._resetLayout=function(){this.getSize()},y.prototype.getSize=function(){this.size=d(this.element)},y.prototype._getMeasurement=function(t,e){var i,n=this.options[t];n?("string"==typeof n?i=this.element.querySelector(n):c(n)&&(i=n),this[t]=i?d(i)[e]:n):this[t]=0},y.prototype.layoutItems=function(t,e){t=this._getItemsForLayout(t),this._layoutItems(t,e),this._postLayout()},y.prototype._getItemsForLayout=function(t){for(var e=[],i=0,n=t.length;n>i;i++){var o=t[i];o.isIgnored||e.push(o)}return e},y.prototype._layoutItems=function(t,e){if(!t||!t.length)return this.emitEvent("layoutComplete",[this,t]),void 0;this._itemsOn(t,"layout",function(){this.emitEvent("layoutComplete",[this,t])});for(var i=[],n=0,o=t.length;o>n;n++){var r=t[n],s=this._getItemLayoutPosition(r);s.item=r,s.isInstant=e,i.push(s)}this._processLayoutQueue(i)},y.prototype._getItemLayoutPosition=function(){return{x:0,y:0}},y.prototype._processLayoutQueue=function(t){for(var e=0,i=t.length;i>e;e++){var n=t[e];this._positionItem(n.item,n.x,n.y,n.isInstant)}},y.prototype._positionItem=function(t,e,i,n){n?t.goTo(e,i):t.moveTo(e,i)},y.prototype._postLayout=function(){var t=this._getContainerSize();t&&(this._setContainerMeasure(t.width,!0),this._setContainerMeasure(t.height,!1))},y.prototype._getContainerSize=u,y.prototype._setContainerMeasure=function(t,e){if(void 0!==t){var i=this.size;i.isBorderBox&&(t+=e?i.paddingLeft+i.paddingRight+i.borderLeftWidth+i.borderRightWidth:i.paddingBottom+i.paddingTop+i.borderTopWidth+i.borderBottomWidth),t=Math.max(t,0),this.element.style[e?"width":"height"]=t+"px"}},y.prototype._itemsOn=function(t,e,i){function n(){return o++,o===r&&i.call(s),!0}for(var o=0,r=t.length,s=this,a=0,h=t.length;h>a;a++){var p=t[a];p.on(e,n)}},y.prototype.ignore=function(t){var e=this.getItem(t);e&&(e.isIgnored=!0)},y.prototype.unignore=function(t){var e=this.getItem(t);e&&delete e.isIgnored},y.prototype.stamp=function(t){if(t=this._find(t)){this.stamps=this.stamps.concat(t);for(var e=0,i=t.length;i>e;e++){var n=t[e];this.ignore(n)}}},y.prototype.unstamp=function(t){if(t=this._find(t))for(var e=0,i=t.length;i>e;e++){var n=t[e];o(n,this.stamps),this.unignore(n)}},y.prototype._find=function(t){return t?("string"==typeof t&&(t=this.element.querySelectorAll(t)),t=n(t)):void 0},y.prototype._manageStamps=function(){if(this.stamps&&this.stamps.length){this._getBoundingRect();for(var t=0,e=this.stamps.length;e>t;t++){var i=this.stamps[t];this._manageStamp(i)}}},y.prototype._getBoundingRect=function(){var t=this.element.getBoundingClientRect(),e=this.size;this._boundingRect={left:t.left+e.paddingLeft+e.borderLeftWidth,top:t.top+e.paddingTop+e.borderTopWidth,right:t.right-(e.paddingRight+e.borderRightWidth),bottom:t.bottom-(e.paddingBottom+e.borderBottomWidth)}},y.prototype._manageStamp=u,y.prototype._getElementOffset=function(t){var e=t.getBoundingClientRect(),i=this._boundingRect,n=d(t),o={left:e.left-i.left-n.marginLeft,top:e.top-i.top-n.marginTop,right:i.right-e.right-n.marginRight,bottom:i.bottom-e.bottom-n.marginBottom};return o},y.prototype.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},y.prototype.bindResize=function(){this.isResizeBound||(i.bind(t,"resize",this),this.isResizeBound=!0)},y.prototype.unbindResize=function(){i.unbind(t,"resize",this),this.isResizeBound=!1},y.prototype.onresize=function(){function t(){e.resize(),delete e.resizeTimeout}this.resizeTimeout&&clearTimeout(this.resizeTimeout);var e=this;this.resizeTimeout=setTimeout(t,100)},y.prototype.resize=function(){var t=d(this.element),e=this.size&&t;e&&t.innerWidth===this.size.innerWidth||this.layout()},y.prototype.addItems=function(t){var e=this._itemize(t);return e.length&&(this.items=this.items.concat(e)),e},y.prototype.appended=function(t){var e=this.addItems(t);e.length&&(this.layoutItems(e,!0),this.reveal(e))},y.prototype.prepended=function(t){var e=this._itemize(t);if(e.length){var i=this.items.slice(0);this.items=e.concat(i),this._resetLayout(),this._manageStamps(),this.layoutItems(e,!0),this.reveal(e),this.layoutItems(i)}},y.prototype.reveal=function(t){if(t&&t.length)for(var e=0,i=t.length;i>e;e++){var n=t[e];n.reveal()}},y.prototype.hide=function(t){if(t&&t.length)for(var e=0,i=t.length;i>e;e++){var n=t[e];n.hide()}},y.prototype.getItem=function(t){for(var e=0,i=this.items.length;i>e;e++){var n=this.items[e];if(n.element===t)return n}},y.prototype.getItems=function(t){if(t&&t.length){for(var e=[],i=0,n=t.length;n>i;i++){var o=t[i],r=this.getItem(o);r&&e.push(r)}return e}},y.prototype.remove=function(t){t=n(t);var e=this.getItems(t);if(e&&e.length){this._itemsOn(e,"remove",function(){this.emitEvent("removeComplete",[this,e])});for(var i=0,r=e.length;r>i;i++){var s=e[i];s.remove(),o(s,this.items)}}},y.prototype.destroy=function(){var t=this.element.style;t.height="",t.position="",t.width="";for(var e=0,i=this.items.length;i>e;e++){var n=this.items[e];n.destroy()}this.unbindResize(),delete this.element.outlayerGUID,p&&p.removeData(this.element,this.settings.namespace)},y.data=function(t){var e=t&&t.outlayerGUID;return e&&_[e]},y.create=function(t,i){function n(){y.apply(this,arguments)}return e(n.prototype,y.prototype),g(n,"options"),g(n,"settings"),e(n.prototype.options,i),n.prototype.settings.namespace=t,n.data=y.data,n.Item=function(){m.apply(this,arguments)},n.Item.prototype=new m,n.prototype.settings.item=n.Item,s(function(){for(var e=r(t),i=a.querySelectorAll(".js-"+e),o="data-"+e+"-options",s=0,u=i.length;u>s;s++){var f,c=i[s],d=c.getAttribute(o);try{f=d&&JSON.parse(d)}catch(l){h&&h.error("Error parsing "+o+" on "+c.nodeName.toLowerCase()+(c.id?"#"+c.id:"")+": "+l);continue}var m=new n(c,f);p&&p.data(c,t,m)}}),p&&p.bridget&&p.bridget(t,n),n},y.Item=m,y}var a=t.document,h=t.console,p=t.jQuery,u=function(){},f=Object.prototype.toString,c="object"==typeof HTMLElement?function(t){return t instanceof HTMLElement}:function(t){return t&&"object"==typeof t&&1===t.nodeType&&"string"==typeof t.nodeName},d=Array.prototype.indexOf?function(t,e){return t.indexOf(e)}:function(t,e){for(var i=0,n=t.length;n>i;i++)if(t[i]===e)return i;return-1};"function"==typeof define&&define.amd?define(["eventie/eventie","doc-ready/doc-ready","eventEmitter/EventEmitter","get-size/get-size","matches-selector/matches-selector","./item"],s):t.Outlayer=s(t.eventie,t.docReady,t.EventEmitter,t.getSize,t.matchesSelector,t.Outlayer.Item)}(window),function(t){"use strict";function e(t,e){var n=t.create("masonry");return n.prototype._resetLayout=function(){this.getSize(),this._getMeasurement("columnWidth","outerWidth"),this._getMeasurement("gutter","outerWidth"),this.measureColumns();var t=this.cols;for(this.colYs=[];t--;)this.colYs.push(0);this.maxY=0},n.prototype.measureColumns=function(){if(this.getContainerWidth(),!this.columnWidth){var t=this.items[0],i=t&&t.element;this.columnWidth=i&&e(i).outerWidth||this.containerWidth}this.columnWidth+=this.gutter,this.cols=Math.floor((this.containerWidth+this.gutter)/this.columnWidth),this.cols=Math.max(this.cols,1)},n.prototype.getContainerWidth=function(){var t=this.options.isFitWidth?this.element.parentNode:this.element,i=e(t);this.containerWidth=i&&i.innerWidth},n.prototype._getItemLayoutPosition=function(t){t.getSize();var e=Math.ceil(t.size.outerWidth/this.columnWidth);e=Math.min(e,this.cols);for(var n=this._getColGroup(e),o=Math.min.apply(Math,n),r=i(n,o),s={x:this.columnWidth*r,y:o},a=o+t.size.outerHeight,h=this.cols+1-n.length,p=0;h>p;p++)this.colYs[r+p]=a;return s},n.prototype._getColGroup=function(t){if(2>t)return this.colYs;for(var e=[],i=this.cols+1-t,n=0;i>n;n++){var o=this.colYs.slice(n,n+t);e[n]=Math.max.apply(Math,o)}return e},n.prototype._manageStamp=function(t){var i=e(t),n=this._getElementOffset(t),o=this.options.isOriginLeft?n.left:n.right,r=o+i.outerWidth,s=Math.floor(o/this.columnWidth);s=Math.max(0,s);var a=Math.floor(r/this.columnWidth);a=Math.min(this.cols-1,a);for(var h=(this.options.isOriginTop?n.top:n.bottom)+i.outerHeight,p=s;a>=p;p++)this.colYs[p]=Math.max(h,this.colYs[p])},n.prototype._getContainerSize=function(){this.maxY=Math.max.apply(Math,this.colYs);var t={height:this.maxY};return this.options.isFitWidth&&(t.width=this._getContainerFitWidth()),t},n.prototype._getContainerFitWidth=function(){for(var t=0,e=this.cols;--e&&0===this.colYs[e];)t++;return(this.cols-t)*this.columnWidth-this.gutter},n.prototype.resize=function(){var t=this.containerWidth;this.getContainerWidth(),t!==this.containerWidth&&this.layout()},n}var i=Array.prototype.indexOf?function(t,e){return t.indexOf(e)}:function(t,e){for(var i=0,n=t.length;n>i;i++){var o=t[i];if(o===e)return i}return-1};"function"==typeof define&&define.amd?define(["outlayer/outlayer","get-size/get-size"],e):t.Masonry=e(t.Outlayer,t.getSize)}(window);

///var/www/philharmonia.co.uk/js/QApairs.js
function setUpAllQApairs() {
	"use strict";
	
	$(".shoppingA").each( function(index) {
		$(this).addClass("hiddenArea");
		$(this).attr("id", "pageA" + index);
	});
	
	$(".shoppingQ").each( function(index) {
		$(this).attr("id", "pageQ" + index);
		$(this).attr("aria-controls", "pageA" + index);
		$(this).click( function(e) {
			var thisQId = e.target.id,
				thisQTarget;
			
			if (thisQId.length < 3) { thisQId = e.target.parentElement.id; }
			thisQTarget = thisQId.replace("Q", "A");
			
			if ($("#" + thisQTarget).hasClass("hiddenArea")) {
				$("#" + thisQTarget).removeClass("hiddenArea");
				$("#" + thisQTarget).attr('aria-expanded', 'true');
				$("#" + thisQTarget).focus();
			} else { 
				$("#" + thisQTarget).addClass("hiddenArea");
				$("#" + thisQTarget).attr('aria-expanded', 'false');
			}
		});
	});
}

///var/www/philharmonia.co.uk/js/accents.js
var accentMap = {
	'À':'A',
	'Á':'A',
	'Â':'A',
	'Ã':'A',
	'Ä':'A',
	'Å':'A',
	'Æ':'AE',
	'Ç':'C',
	'È':'E',
	'É':'E',
	'Ê':'E',
	'Ë':'E',
	'Ì':'I',
	'Í':'I',
	'Î':'I',
	'Ï':'I',
	'Ð':'D',
	'Ñ':'N',
	'Ò':'O',
	'Ó':'O',
	'Ô':'O',
	'Õ':'O',
	'Ö':'O',
	'Ø':'O',
	'Ù':'U',
	'Ú':'U',
	'Û':'U',
	'Ü':'U',
	'Ý':'Y',
	'Ł':'L',
	'à':'a',
	'á':'a',
	'â':'a',
	'ã':'a',
	'ä':'a',
	'å':'a',
	'æ':'ae',
	'ç':'c',
	'è':'e',
	'é':'e',
	'ê':'e',
	'ë':'e',
	'ì':'i',
	'í':'i',
	'î':'i',
	'ï':'i',
	'ð':'o',
	'ñ':'n',
	'ò':'o',
	'ó':'o',
	'ô':'o',
	'õ':'o',
	'ö':'o',
	'ø':'o',
	'ù':'u',
	'ú':'u',
	'û':'u',
	'ü':'u',
	'ý':'y',
	'ł':'l'
};

var normalize = function( term ) {
	"use strict";
	var ret = "";
	for ( var i = 0; i < term.length; i++ ) {
		ret += accentMap[ term.charAt(i) ] || term.charAt(i);
	}
	return ret;
};

///var/www/philharmonia.co.uk/js/accordion.js
//Accordion feature for Philharmonia Orchestra FAQ page
//The FAQ page is extendible. Make sure all


var currentQuestion = "",

	accordion = { 
	init: function() {
		accordion.hideAllAnswers();
		accordion.bindQuestionsToAnswers();
		accordion.bindExpandCollapse();
		accordion.nicePointer();
	}, //end init
	
	/**
	*	instantly hide all the answers, alled by init once page loaded
	*/
	hideAllAnswers: function() {
		$(".faq_answer").hide()
	}, //end hideAllAnswers
	
	/**
	*	assigns logic to h4 questions
	*/
	
	bindQuestionsToAnswers: function () {
		$(".faq_question").each(
			function() {
				$(this).click(
					function() {
						if($(this).hasClass("faq_active"))
						{
							$("#accordion h4").removeClass("faq_active");
							$(this).siblings("div").slideUp(400);
						}
						else
						{
							$("#accordion h4").siblings("div").slideUp(400);
							$(this).addClass("faq_active");
							$(this).siblings("div").slideDown(400);
						}
					}
				)
			}
		)
	},
	
	/**
	*	make .faq_question h4s obviously clickable
	*/
	
	nicePointer: function() {
		$(".faq_question").each(
			function() {
				$(this).css("cursor","pointer")
			}
		);
	},
	
	/**
	*	activate both buttons
	*/
	
	bindExpandCollapse: function() {
		$("#faq_expand_all").click(
			function() {
				$(".faq_answer").slideDown(400);			
			}//end click function
		)//end click
		$("#faq_collapse_all").click(
			function() {
				$(".faq_answer").slideUp(400);			
			}
		)
	}
}

$(document).ready(function() {
	accordion.init()
});

///var/www/philharmonia.co.uk/js/adminDiscoverMore.js
<!-- Add Discover More -->
$('#cms_editor_add').hide();
$( "#add_switch" ).click('click', function() {
	$('#cms_editor_add').animate({height: "toggle"});
	$('#edit_switch').animate({height: "toggle"});
	$('#discoverItem_next').animate({height: "toggle"});
	$('#discoverItem_prev').animate({height: "toggle"});
	$('#discover_container').animate({height: "toggle"});
	
	if ($('#cms_editor_add').height() <= 1) {
		document.getElementById("discover_box_title").innerHTML = 'Add';
	} else {
		document.getElementById("discover_box_title").innerHTML = 'Discover More';
	}
	
});

<!-- Add Type Switch -->
$('.type_add').hide();
$( ".cms_add_type" ).click('click', function() {
	var typeTitle = $(this).attr('data-box');
	
	$('#' + typeTitle + '').animate({height: "toggle"});
	$('#type_choice').animate({height: "toggle"});
	
});
$( ".type_up" ).click('click', function() {
	
	$(this).parent('p').parent('.type_add').animate({height: "toggle"});
	$('#type_choice').animate({height: "toggle"});
	
});

<!-- Edit Discover More -->
$('#cms_editor_edit').hide();
$( "#edit_switch" ).click('click', function() {
	$('#cms_editor_edit').animate({height: "toggle"});
	$('#add_switch').animate({height: "toggle"});
	$('#discoverItem_next').animate({height: "toggle"});
	$('#discoverItem_prev').animate({height: "toggle"});
	$('#discover_container').animate({height: "toggle"});
	
	if ($('#cms_editor_edit').height() <= 1) {
		document.getElementById("discover_box_title").innerHTML = 'Edit';
	} else {
		document.getElementById("discover_box_title").innerHTML = 'Discover More';
	}
	
});
	
<!-- Remove Item(s) -->
$(".fi-trash").css({color: "rgb(170, 170, 170)"});
$( ".fi-trash" ).click('click', function() {
	
	var itemID = $(this).attr('data-box');
	var currentRemove = $(this).attr('id');
	
	if ($(this).css('color') == 'rgb(232, 55, 84)') {
		$("#item_delete_" + itemID + "").remove();
		$("#current_item_" + itemID + "").remove();
		$(this).css({color: "rgb(170, 170, 170)"});
	} else {
		$(this).parent('.eir_controls').parent('.edit_item_row').parent('form').prepend('<input type="hidden" name="remove_item[]" id="item_delete_' + itemID + '" value="remove_' + itemID + '">');
		$(this).parent('.eir_controls').parent('.edit_item_row').parent('form').prepend('<input type="hidden" name="current_remove_' + itemID + '" id="current_item_' + itemID + '" value="' + currentRemove + '">');
		$(this).css({color: "rgb(232, 55, 84)"});
		
	}
});

///var/www/philharmonia.co.uk/js/artists.js
var artists = {
	/******************
	* pass object with type (soloists / conductors), alpha
	******************/
	page: function (data) {
		"use strict";
		$('#sub_title').fadeOut();
		if (data.alpha!=='coming') {
			$('.intro').text('');
		}
		$('#artists').css('height', $('#artists').innerHeight());
		$('.artist').each(function() {
			$(this).fadeOut();
		});
		$('.artist').promise().done(function () {
			/*****************************
			* show loading ani
			******************************/
			$('#artists').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
			

			/*****************************
			* update browser location
			******************************/
			if (data.alpha!=='coming') {
				pathChange(URL+BASE+'/orchestra/'+data.type+'&p='+data.alpha);
				
				// remove top section of conductors page
				$('#conductors_top').remove();
			} 
			
			/*****************************
			* Get the new list of artists
			******************************/
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/artists/artists.php',
				data: data,
				success:function(artists) {
					$('#artists').hide();
					$('#artists').html(artists);
					$('#artists').fadeIn('slow', function() {
						$('#artists').css('height', 'auto');
					});
					if (data.alpha!=='coming') {
						$('#sub_title').text(data.alpha);
						$('#sub_title').fadeIn();
					}
					
					$(".page_image").lazyload({
						effect : "fadeIn"
					});
					
					
					// reset all images for editing 
					if (typeof is_admin !== 'undefined'){
						$('.page_image').bind('load', function() {
							images.setForEdit($(this));
						});
					}
				}
			});
		});
	}
};

///var/www/philharmonia.co.uk/js/basket.js
var basket = {
	
	show: function() {
		"use strict";
		$('#shopping_cart').slideDown();
	},
	hide: function() {
		"use strict";
		$('#shopping_cart').slideUp();
	},
	
	deleteConcert:function (perf, line_item) {
		"use strict";
		$('#screener').show();
		$('#remove_perf_no').val(perf);
		$('#remove_line_item').val(line_item);
		$('#remove_from_cart').submit();
	},
	
	deleteTicket:function (perf, line_item, sub_line_item) {
		"use strict";
		$('#screener').show();
		$('#'+sub_line_item).append('<span style="color:#700017">deleting... please wait...</span>');
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/checkout/delete_seat.php',
			data: {
				perf:perf,
				lineitem:line_item,
				sublineitem:sub_line_item
			},
			success:function(response) {
				if (response!="") {
					alert('Sorry something went wrong - your ticket has not been removed from your basket');
				} else {
					document.location.href = document.location.href;
				}
			},
			error:function() {
				alert('Sorry something went wrong - your ticket has not been removed from your basket');
			}
		});	
	},
	
	deleteContribution:function(ref_no) {
		"use strict";
		$('#screener').show();
		$('#'+ref_no).append('<span style="color:#700017">deleting... please wait...</span>');
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/checkout/delete_contribution.php',
			data: {
				ref_no:ref_no,
			},
			success:function(response) {
				if (response!="") {
					alert('Sorry something went wrong - your donation has not been removed from your basket');
				} else {
					document.location.href = document.location.href;
				}
			},
			error:function() {
				alert('Sorry something went wrong - your ticket has not been removed from your basket');
			}
		});	
	},
	
	copyAddress:function() {
		"use strict";
		$('#delivery_address').val($('#address').val());
		$('#delivery_city').val($('#city').val());
		$('#delivery_postcode').val($('#postcode').val());
		$('#delivery_country').val($('#country').val());
	}
}

///var/www/philharmonia.co.uk/js/checkout.js
var checkout = {
	
	order_total:0,
	
	init: function() {
		$('input[name=delivery_method]').bind('click', function() {
			checkout.updateDeliveryMethod($(this).val(), JSON.stringify($('#checkout').serializeArray()));
		});
	},
	
	updateDeliveryMethod:function(method, data) {
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/checkout/update_shipping.php',
			data:{
				method:method,
				data:data
			},
			success:function(response) {
				
			}
		});
	},
	
	updateCommentsRequest:null,
	updateComments:function(comments) {
		if (checkout.updateCommentsRequest!=null) checkout.updateCommentsRequest.abort();
		checkout.updateCommentsRequest = $.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/checkout/add_comments.php',
			data:{
				comments:comments
			},
			success:function(response) {
				console.log(response);
			}
		});
	},
	
	setDataProtection:function() {
		modal(0, 'users/data_protection');
		$('#screener').unbind('click');
	},
	
	complete:function() {
		
		if ($('input[name=delivery_method]:checked').length>0) {
			$('#pay_now').replaceWith('<div class="loading" style="float:right; margin-right:50px;"><img src="'+BASE+'/core/images/loading.gif" /></div>');
	
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/checkout/update_shipping.php',
				data:{
					method:$('input[name=delivery_method]:checked').val(),
					data:JSON.stringify($('#checkout').serializeArray())
				},
				success:function(response) {
					
					
					
					if (response=='session expired') {
						alert('Sorry, your session expired and your chosen tickets were released. Please choose again.');
						document.location.href='/concert/venue/southbank_centre';
						
					} else {
						if ($('#total_price').length>0) {
							$.ajax({
								type:'POST',
								url:BASE+'/lib/ajax/checkout/update_donation.php',
								data:{
									gift_aid:($('#gift_aid').prop('checked')) ? 1 : 0,
									value:($('#donation').val()!='other')? $('#donation').val(): $('#other_donation').val()
								},
								success:function(response) {
									var response_split = response.split('---');
									$('#order_amount').val(response_split[0]);
									$('#order_desc').val(response_split[1]);
									
									$("#checkout").prepend('<input type="hidden" name="instId" value="95572">');
									checkForm('checkout');
								}
							});
						} else {
							$("#checkout").prepend('<input type="hidden" name="instId" value="95572">');
							checkForm('checkout');
						}
					}
				}
			});
		} else {
			alert('Please choose a delivery method before completing your purchase');
		}
	},
	
	selectDonation:function(val) {
		if (val=='other') { 
			$('#donation').fadeOut(function() { 
				$('#other_donation').prop('disabled', false); 
				$('#other_donation').fadeIn(); 
				$('#other_donation').focus().val('£'); 
			});  
		} else { 
			$('#other_donation').fadeOut(function() { 
				$('#other_donation').prop('disabled', true); 
				$('#donation').fadeIn(); 
			}); 
			$('#total_price').html(parseFloat(checkout.order_total)+parseFloat(val)); 
		}
	}
	
}

$(document).ready(function() {
	checkout.init();
});

///var/www/philharmonia.co.uk/js/concerts.js
var concerts = {
	
	init:function() {
		"use strict";
		$('#date_select').bind('change', function() {
			concerts.get({
				date:this.value, 
				title:$(this).find(':selected').text()
			});
			$(this).blur();
		});
		$('#venue_select').bind('change', function() {
			concerts.get({
				venue:this.value, 
				title:$(this).find(':selected').text()
			});
			$(this).blur();
		});
		$('#conductor_select').bind('change', function() {
			concerts.get({
				conductor:this.value, 
				title:$(this).find(':selected').text()
			});
			$(this).blur();
		});
		$('#soloist_select').bind('change', function() {
			concerts.get({
				soloist:this.value, 
				title:$(this).find(':selected').text()
			});
			$(this).blur();
		});
		$('#composer_select').bind('change', function() {
			concerts.get({
				composer:this.value, 
				title:$(this).find(':selected').text()
			});
			$(this).blur();
		});
	},
	
	related: function(video_id, target) {
		"use strict";
		$('#'+target).html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
		video.getVideo(video_id, function(data){
			$('#'+target).html(data);
		});
	},
	/***********************
	* send data object
	* - month (YYYY-MM-DD) 
	* - venue (id)
	***********************/
	get: function(data, append) {
		"use strict";
		
		append = (append==null) ? false : append;
		
		if (append==false) {
			$('#sub_title').fadeOut();
			$('#concerts').css('height', $('#concerts').innerHeight());
		} else {
			$('#more_concerts').remove();
		}
						
		$('.concert_listing').each(function() {
			if (append==false) {
				$(this).fadeOut();
			}
		});
		$('.concert_listing').promise().done(function () {
			/*****************************
			* show loading ani
			******************************/
			if (append == true) {
				$('#concerts').append('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
			} else {
				$('#concerts').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
			}
			
			if (data.date && data.date!='all') {
				$('#venue_select, #conductor_select, #soloist_select, #composer_select').val('');
				/*****************************
				* split date in to array
				******************************/
				var date_split = data.date.split('-');
				
				/*****************************
				* update browser location
				******************************/
				pathChange(BASE+'/concerts/date/'+date_split[0]+'/'+date_split[1]);
			
			} else if (data.venue && data.venue!='all') {		
				$('#date_select, #conductor_select, #soloist_select, #composer_select').val('');
				
				/*****************************
				* update browser location
				******************************/
				jsPhpBridge('urlify', data.title, function(response) {
					pathChange(BASE+'/concerts/venue/'+data.venue+'/'+response);
				});
			
			} else if (data.conductor && data.conductor!='all') {		
				$('#venue_select, #date_select, #soloist_select, #composer_select').val('');

				/*****************************
				* update browser location
				******************************/
				jsPhpBridge('urlify', data.title, function(response) {
					pathChange(BASE+'/concerts/conductor/'+data.conductor+'/'+response);
				});
				
			} else if (data.soloist && data.soloist!='all') {		
				$('#venue_select, #conductor_select, #date_select, #composer_select').val('');
				
				/*****************************
				* update browser location
				******************************/
				jsPhpBridge('urlify', data.title, function(response) {
					pathChange(BASE+'/concerts/soloist/'+data.soloist+'/'+response);
				});
			
			} else if (data.composer && data.composer!='all') {		
				$('#venue_select, #conductor_select, #date_select, #soloist_select').val('');

				/*****************************
				* update browser location
				******************************/
				jsPhpBridge('urlify', data.title, function(response) {
					pathChange(BASE+'/concerts/composer/'+data.composer+'/'+response);
				});
				
			} else {
				pathChange(BASE+'/concerts');
			}
			
			/*****************************
			* Get the new list of concerts
			******************************/
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/concerts/concerts.php',
				data: data,
				success:function(response) {
					if (append == true) {
						$('.loading').remove();
						$('#concerts').append(response);
					} else {
						$('#concerts').css('opacity', 0.1);
						$('#concerts').html(response);
					}
					concerts.linkify();
					$('#concerts').animate({
						opacity:1
					}, 'slow', function() {
						$('#concerts').css('height', 'auto');
						$('.concert_listing .concert_media .page_image').load(function() {
							concerts.position();
						});
					});
					$('#sub_title').text(data.title);
					$('#sub_title').fadeIn();
					$('.sub_nav select').val('');
					
					$(".page_image").lazyload({
						effect : "fadeIn"
					});
					$(window).trigger('scroll');
					
					// show/hide bottom concert filters
					if ($('.concert_listing').length<=5) {
						var i=0;
						$('.sub_nav').each(function() {
							i++;
							if (i>1) { 
								$(this).hide();
							}
						});
					} else {
						$('.sub_nav').show();
					}
					setTimeout(function() {
						concerts.position();
						// reset all images for editing 
						if (typeof is_admin !== 'undefined'){
							$('.page_image').each(function() {
								//images.setForEdit($(this));
							});
						}
					}, 2000);
				}
			});
		});
	},
	
	/**************************
	* positions the book / more links on the concert listings
	***************************/
	position:function() {
		"use strict";		
		/*if (!MOBILE) {
			var w = ($('.concert_listing').width()/100);
			var h = $('.concert_listing .concert_media .page_image').height();

			$('.concert_listing .concert_title').height(h-(w*3));
			$('.concert_listing .concert_details').height(h-(w*3));
		}*/
	},
	
	/**************************
	* convert links on concert blocks in to modal form
	***************************/
	linkify:function() {
		"use strict";
		if (!MOBILE) {
			$('.concert_details a, .concert_date a').each(function() {
				if ($(this).data('type')!='' && $(this).data('type')!=null) {
					$(this).attr('rel', 'modal');
					$(this).bind('click', function() {
						modal($(this).data('id'), 'info/data', 'auto', {type:$(this).data('type')});
						return false;
					});
				}
			});
			concerts.position();
		}
	},
	
	/**************************
	* add hover functionality to related links
	***************************/
	relatedLinks:function() {
		"use strict";
		$('.concert_related_content').each(function() {
			if ($(this).attr('rel')!=='' && $(this).attr('rel')!==null && $(this).attr('rel').indexOf('product')==-1) {
				$(this).bind('mouseenter', function() {
					$(this).append('<img class="play_icon" src="'+BASE+'/images/icons/play_pink.png" />');
				});
				$(this).bind('mouseleave', function() {
					$('.play_icon').remove();
				});
				$(this).bind('click', function() {
					concerts.related($(this).attr('rel'),'concert_media');
				});
			} else if ($(this).attr('rel').indexOf('product')!=-1) {
				var that = $(this);
				$(this).bind('click', function() {
					that.find('a').each(function() {
						document.location.href=$(this).attr('href');
						return false;
					});
				});
			}
		});
	},
	
	/*************************
	* Pass website concert ID and rep ID to hide/unhide repertoire
	***************************/
	hideRepertoire: function(id, rep) {
		if(confirm('Are you sure you want to edit this repertoire?')) {
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/concerts/hide_repertoire.php',
				data:{
					id:id,
					rep:rep
				}
			});
		}
	},
	
	content:null,
	
	flip:function(id) {
		if (concerts.content==null) {
			$('.full_concert').height($('.full_concert').innerHeight());
			$('.full_concert > div').fadeOut();
			$('.full_concert > div').promise().done(function() {			
				
				$('.full_concert').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
				$('.full_concert').fadeIn();
				$.ajax({
					type:'POST',
					url:BASE+'/lib/forms/cart/select_seat.php',
					data:{
						id:id
					},
					success:function(response) {
						concerts.content = $('.full_concert').html();
						$('.full_concert').html(response);
						$('#select_seats').fadeIn();
						$('.full_concert').height('auto');
						pathChange(document.URL+'/book', 'Book Tickets');
					}
				});
			});
		} else {
			$('.full_concert > div').fadeOut(function() {			
				$('.full_concert').html(concerts.content);
				$('.full_concert > div').hide();
				$('.full_concert > div').fadeIn();
				concerts.content=null;		
				pathChange(document.URL.replace('/book', ''), 'Concert');	
			});
		}
	},
	
	showCalendar:function() {
		$('#concerts_calendar').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
		$('#concerts_calendar').slideDown(function() {
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/concerts/calendar.php',
				data:{
				},
				success:function(response) {
					$('#concerts_calendar').html(response);
					$('.calendar_details').remove();
					$('#concerts_calendar').append("<p style='text-align:right; padding:10px 15px 0 0; font-size:12px;'><a href='javascript:void(0)' onclick='concerts.closeCalendar()'>close</a></p>");
					calendar.linkify();
				}
			});
		});
	},
	
	closeCalendar:function() {
		$('#concerts_calendar').slideUp(function() {
			$('#concerts_calendar').hide();
		});
	},
	
	setReminder:function(id) {
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/concerts/set_reminder.php',
			data:{
				id:id
			},
			success:function(response) {
				if (response=='not logged in') {
					modal(id, 'users/concert_reminders');
				} else {
					alert("Email reminder set. We'll email you the morning this concert goes on sale.");
				}
			}
		});
	}
	
};

$(document).ready(function() {
	"use strict";
	concerts.init();
	//concerts.position();
});

///var/www/philharmonia.co.uk/js/dictation.js
//Philharmonia Orchestra: Dictation Exercises
//global variables
var dictation = {
	
	dictation_audio:null,
	
	playDictationAudio: function() {
		dictation.dictation_audio.get(0).play();
	},
	
	//hide the answer image
	init:function() {
		
		dictation.dictation_audio = $("#dictation_player");
		
		//assign clicks to buttons
		$("#toggle_answer").click( function() {
			$("#answer").fadeToggle();
			
			if (this.innerHTML == "ANSWER") {
				this.innerHTML = "HIDE ANSWER";
				dictation.playDictationAudio();
			} else {
				this.innerHTML = "ANSWER"
			}
		});
		
		$("#dictation_clear_all").click( function() {
			$("#answer").fadeOut();
			$("#toggle_answer").html("ANSWER");
			$(".new_note").fadeOut();
		});
		
		$('.toolbox_button').each(function() {
			$(this).bind('mouseup', function() {
				var new_note = $('<img />').attr({ 	
						'src'	: '/assets/dictation/img/'+$(this).attr('id')+'.png', 
						'alt'	: $(this).attr('id'), 
						'class'	: 'new_note'
					}).appendTo($('#'+$(this).attr('id')+'_new')).hide();
					
				$(".new_note").draggable();
				$(".new_note").fadeIn();
				$(this).unbind('mouseup');
			});
		});
		
		//set audio player button
		$("#dictation_play").click(function() {
			dictation.playDictationAudio();
		});
		
		//make all .dictation_first_button's draggable
		$(".dictation_first_button").draggable();
		//navigate("next");
	}
}
	
$(document).ready(function() {
	dictation.init();
});
		



///var/www/philharmonia.co.uk/js/froogaloop.js
// Init style shamelessly stolen from jQuery http://jquery.com
var Froogaloop = (function(){
    // Define a local copy of Froogaloop
    function Froogaloop(iframe) {
        // The Froogaloop object is actually just the init constructor
        return new Froogaloop.fn.init(iframe);
    }

    var eventCallbacks = {},
        hasWindowEvent = false,
        isReady = false,
        slice = Array.prototype.slice,
        playerDomain = '';

    Froogaloop.fn = Froogaloop.prototype = {
        element: null,

        init: function(iframe) {
            if (typeof iframe === "string") {				
                iframe = document.getElementById(iframe);
            }
console.log(iframe);
            this.element = iframe;

            // Register message event listeners
            playerDomain = getDomainFromUrl(this.element.getAttribute('src'));

            return this;
        },

        /*
         * Calls a function to act upon the player.
         *
         * @param {string} method The name of the Javascript API method to call. Eg: 'play'.
         * @param {Array|Function} valueOrCallback params Array of parameters to pass when calling an API method
         *                                or callback function when the method returns a value.
         */
        api: function(method, valueOrCallback) {
            if (!this.element || !method) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null,
                params = !isFunction(valueOrCallback) ? valueOrCallback : null,
                callback = isFunction(valueOrCallback) ? valueOrCallback : null;

            // Store the callback for get functions
            if (callback) {
                storeCallback(method, callback, target_id);
            }

            postMessage(method, params, element);
            return self;
        },

        /*
         * Registers an event listener and a callback function that gets called when the event fires.
         *
         * @param eventName (String): Name of the event to listen for.
         * @param callback (Function): Function that should be called when the event fires.
         */
        addEvent: function(eventName, callback) {
            if (!this.element) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null;


            storeCallback(eventName, callback, target_id);

            // The ready event is not registered via postMessage. It fires regardless.
            if (eventName != 'ready') {
                postMessage('addEventListener', eventName, element);
            }
            else if (eventName == 'ready' && isReady) {
                callback.call(null, target_id);
            }

            return self;
        },

        /*
         * Unregisters an event listener that gets called when the event fires.
         *
         * @param eventName (String): Name of the event to stop listening for.
         */
        removeEvent: function(eventName) {
            if (!this.element) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null,
                removed = removeCallback(eventName, target_id);

            // The ready event is not registered
            if (eventName != 'ready' && removed) {
                postMessage('removeEventListener', eventName, element);
            }
        }
    };

    /**
     * Handles posting a message to the parent window.
     *
     * @param method (String): name of the method to call inside the player. For api calls
     * this is the name of the api method (api_play or api_pause) while for events this method
     * is api_addEventListener.
     * @param params (Object or Array): List of parameters to submit to the method. Can be either
     * a single param or an array list of parameters.
     * @param target (HTMLElement): Target iframe to post the message to.
     */
	 
    function postMessage(method, params, target) {
        if (!target.contentWindow.postMessage) {
            return false;
        }

        var url = target.getAttribute('src').split('?')[0],
            data = JSON.stringify({
                method: method,
                value: params
            });

        if (url.substr(0, 2) === '//') {
            url = window.location.protocol + url;
        }

        target.contentWindow.postMessage(data, url);
    }

    /**
     * Event that fires whenever the window receives a message from its parent
     * via window.postMessage.
     */
    function onMessageReceived(event) {
        var data, method;

        try {
            data = JSON.parse(event.data);
            method = data.event || data.method;
        }
        catch(e)  {
            //fail silently... like a ninja!
        }

        if (method == 'ready' && !isReady) {
            isReady = true;
        }

        // Handles messages from moogaloop only
        if (event.origin != playerDomain) {
            return false;
        }

        var value = data.value,
            eventData = data.data,
            target_id = target_id === '' ? null : data.player_id,

            callback = getCallback(method, target_id),
            params = [];

        if (!callback) {
            return false;
        }

        if (value !== undefined) {
            params.push(value);
        }

        if (eventData) {
            params.push(eventData);
        }

        if (target_id) {
            params.push(target_id);
        }

        return params.length > 0 ? callback.apply(null, params) : callback.call();
    }


    /**
     * Stores submitted callbacks for each iframe being tracked and each
     * event for that iframe.
     *
     * @param eventName (String): Name of the event. Eg. api_onPlay
     * @param callback (Function): Function that should get executed when the
     * event is fired.
     * @param target_id (String) [Optional]: If handling more than one iframe then
     * it stores the different callbacks for different iframes based on the iframe's
     * id.
     */
    function storeCallback(eventName, callback, target_id) {
        if (target_id) {
            if (!eventCallbacks[target_id]) {
                eventCallbacks[target_id] = {};
            }
            eventCallbacks[target_id][eventName] = callback;
        }
        else {
            eventCallbacks[eventName] = callback;
        }
    }

    /**
     * Retrieves stored callbacks.
     */
    function getCallback(eventName, target_id) {
        if (target_id) {
            return eventCallbacks[target_id][eventName];
        }
        else {
            return eventCallbacks[eventName];
        }
    }

    function removeCallback(eventName, target_id) {
        if (target_id && eventCallbacks[target_id]) {
            if (!eventCallbacks[target_id][eventName]) {
                return false;
            }
            eventCallbacks[target_id][eventName] = null;
        }
        else {
            if (!eventCallbacks[eventName]) {
                return false;
            }
            eventCallbacks[eventName] = null;
        }

        return true;
    }

    /**
     * Returns a domain's root domain.
     * Eg. returns http://vimeo.com when http://vimeo.com/channels is sbumitted
     *
     * @param url (String): Url to test against.
     * @return url (String): Root domain of submitted url
     */
    function getDomainFromUrl(url) {
        if (url.substr(0, 2) === '//') {
            url = window.location.protocol + url;
        }

        var url_pieces = url.split('/'),
            domain_str = '';

        for(var i = 0, length = url_pieces.length; i < length; i++) {
            if(i<3) {domain_str += url_pieces[i];}
            else {break;}
            if(i<2) {domain_str += '/';}
        }

        return domain_str;
    }

    function isFunction(obj) {
        return !!(obj && obj.constructor && obj.call && obj.apply);
    }

    function isArray(obj) {
        return toString.call(obj) === '[object Array]';
    }

    // Give the init function the Froogaloop prototype for later instantiation
    Froogaloop.fn.init.prototype = Froogaloop.fn;

    // Listens for the message event.
    // W3C
    if (window.addEventListener) {
        window.addEventListener('message', onMessageReceived, false);
    }
    // IE
    else {
        window.attachEvent('onmessage', onMessageReceived);
    }

    // Expose froogaloop to the global object
    return (window.Froogaloop = window.$f = Froogaloop);

})();

///var/www/philharmonia.co.uk/js/functionsConcerts.js
<!-- Extended Repertoire Listings [START] -->
$('.repertoire_text.extended').hide();

$( "#extended_activate" ).click('click', function() {
	$(this).parent('.repertoire_container').children('.repertoire_text.extended').animate({height: "toggle"});
	
	if ($(this).parent('.repertoire_container').children('.repertoire_text.extended').height() <= 1) {
		this.innerHTML = "SHOW LESS";
	} else {
		this.innerHTML = "SHOW ALL";
	}
});
<!-- Extended Repertoire Listings [END] -->

<!-- Resize Iframe Height [START] -->
function seatSel_checkiFrameH(myH) {
	$("#seatSelectionProcessiFrame").css({ height: myH + 60});
}

function changeHeadBorderStyle() {
	$(".new_concert_grid .concert_header").css({ borderWidth: "3px 1px 1px 1px" });
}

function addSeatsToBsktLoad() {
	$("#concertsWhiteOut").removeClass("hiddenArea");
	TweenLite.to($("#concertsWhiteOut"), 0.3, {opacity: 1, ease: Power1.easeOut, onComplete: function() {
			
			$("#addToBasketTimeMsg").removeClass("hiddenArea").attr("aria-hidden", "false");
			loaderLoop("outer");
		}
	});
}
<!-- Resize Iframe Height [END] -->

<!-- Discover More Setup [START] -->
var totalItems = $("#discover_container").children().length,
	currentIndex = 0,
	previousIndex = 0,
	galleryW = $("#discover_more").width();
	
function updateDiscoverSlider(direction) {
	
	TweenLite.to($("#discover_0" + (previousIndex + 1)), 0.3, {left: galleryW * direction, ease:Power1.easeIn, onComplete: function() {
			$("#discover_0" + (previousIndex + 1)).removeClass("show").attr("aria-hidden", "true");
			$("#discover_0" + (currentIndex + 1)).addClass("show").attr("aria-hidden", "false");
			$("#discover_0" + (currentIndex + 1)).css({ left: galleryW * direction * -1 });
			TweenLite.to($("#discover_0" + (currentIndex + 1)), 0.3, { left: 0, ease:Power1.easeOut });
		}
	});
	
	if (currentIndex === 0) {
		$("#discoverItem_prev").css({ opacity: 0.5 }).attr("aria-disabled", "true");
	}
	else if (currentIndex === 1) {
		$("#discoverItem_prev").css({ opacity: 1 }).attr("aria-disabled", "false");
	}
	
	if (currentIndex === totalItems - 1) {
		$("#discoverItem_next").css({ opacity: 0.5 }).attr("aria-disabled", "true");
	}
	else if (currentIndex === totalItems - 2) {
		$("#discoverItem_next").css({ opacity: 1 }).attr("aria-disabled", "false");
	}
}

$("#discoverItem_prev").click( function() {
	if (currentIndex > 0) {
		previousIndex = currentIndex;
		currentIndex -= 1;
		updateDiscoverSlider(1);
	}
});

$("#discoverItem_next").click( function() {
	if (currentIndex < (totalItems - 1)) {
		previousIndex = currentIndex;
		currentIndex += 1;
		updateDiscoverSlider(-1);
	}
});
<!-- Discover More Setup [END] -->

///var/www/philharmonia.co.uk/js/images.js
var images = {
	
};

///var/www/philharmonia.co.uk/js/instruments.js
var instruments = {
	toggleSection: function(sec) {
		$('.instrument_section').fadeOut();
		$('#instrument_section_nav li a').removeClass('selected');
		$('.instrument_section').promise().done(function() {
			$('#'+sec).fadeIn();
			$('#'+sec+'_nav').addClass('selected');
		});
	}
}

///var/www/philharmonia.co.uk/js/konami.js
var konami = {
	konami_code: new Array(38, 38, 40, 40, 37, 39, 37, 39, 65, 66),
	
	current_code_pos: 0,
	
	init: function() {
		$(window).on('keydown', function(e) {
			if (konami.konami_code[konami.current_code_pos]==e.which) {
				if (konami.current_code_pos==9) {
					$('body').append('<audio autoplay loop src="/assets/audio/repertoire/152/elgar_celloconcerto_2_ad_070819.mp3" type="audio/mpeg">Your browser does not support the audio element.</audio>');
					
					konami.current_code_pos = 0;
				} else {
					konami.current_code_pos++;
				}
			} else {
				konami.current_code_pos = 0;
			}
		});
	}
}

$(document).ready(function() {
	konami.init();
});

///var/www/philharmonia.co.uk/js/loadingAnim.js
function loaderLoop(thisLoaderIDprefix) { 
	if (!$("#" + thisLoaderIDprefix + "loaderHolder").hasClass("hiddenArea")) {
		for (var c = 0; c < 3; c++) {
			TweenLite.to($("#" + thisLoaderIDprefix +  "loaderCirc" + c), 0.5, {delay: c * 0.25, scaleX: 0.5, scaleY: 0.5, opacity: 0, ease: Power1.easeIn });
		}
		TweenLite.delayedCall(1, function() {
			for (var c = 0; c < 3; c++) {
				TweenLite.to($("#" + thisLoaderIDprefix +  "loaderCirc" + c), 0.5, {delay: c * 0.25, scaleX: 1, scaleY: 1, opacity: 1, ease: Power1.easeOut });
			}
		});
		TweenLite.delayedCall(1.6, function() {
			loaderLoop(thisLoaderIDprefix);
		});
	}
}


///var/www/philharmonia.co.uk/js/navigation.js
var navigation = {
	orderSetup: function() {
		"use strict";
		$(function() {
			$( "#section_navigation" ).sortable({
				stop:function(event, ui) {
					$.ajax({
						type:'POST',
						url:BASE+'/core/lib/ajax/admin/navigation/order_navigation.php',
						data:{
							order:$( "#section_navigation").sortable( "serialize" )
						},
						success:function(data) {
							//console.log(data);
						}
					});
				}
			});
			$( ".section_navigation ul li ul" ).disableSelection();
		});
	},
	
	mobile: {
		touchX: null,
		touchY: null,
		opening:false,
		
		position: function() {
			var h = $(window).innerHeight();
			$('.navtab').css({
				paddingTop:(((h/100)*31)/3)+'px',
				height:((((h/100)*31)/3)*2)+'px'
			});
			$('#navtabs').css({
				height:h
			});
			
			if (MOBILE) {
				$('#container, #mobile_nav > nav').hammer().on("drag swipe", function(event) {
					if(event.type=='drag' && (event.gesture.direction=='left' || event.gesture.direction=='right')) {
						event.gesture.preventDefault();
					}
					if(event.type=='swipe' && event.gesture.direction=='left') {
						event.gesture.preventDefault();
						navigation.mobile.openNavigation();
					} else if(event.type=='swipe' && event.gesture.direction=='right') {
						event.gesture.preventDefault();
						navigation.mobile.closeNavigation();
					} else {
						return;
					}
	
				});
			}
			
		},
		toggleNavigation: function() {
			if ($('#mobile_nav > nav').css('right')=='0px') {
				navigation.mobile.closeNavigation();
			} else {
				navigation.mobile.openNavigation();
				
			}	
		},
		
		i: 0,
		fadeTabs: function() {
			$('#navtab'+navigation.mobile.i).fadeIn();
			navigation.mobile.i++;
			if (navigation.mobile.i<6) {
				setTimeout(function() {
					navigation.mobile.fadeTabs();
				}, 200);
			}
		},
		openNavigation: function() {
			if (navigation.mobile.opening == false) {
				console.log("opening");
				navigation.mobile.opening = true;
				$('#mobile_nav > nav').animate({
					right:'0px'
				}, function() {
					navigation.mobile.opening = false;
				});
				$('#main').bind('click', function() {
					navigation.mobile.closeNavigation();
				});
			} 
		},
		closeNavigation: function() {
			if (navigation.mobile.opening == false) {
				console.log("closing");
				navigation.mobile.opening = true;
				$('#mobile_nav > nav').animate({
					right:'-1800px'
				}, function() {
					navigation.mobile.opening = false;
				});
				$('#main').unbind('click');
			}
		}
	}
};

$(document).ready(function() {
	$('#navtabs').hide();
	navigation.mobile.position();
	$('.navtab').bind('click', function() { navigation.mobile.closeNavigation() });
});
$(window).resize(function() {
	navigation.mobile.position()
});

///var/www/philharmonia.co.uk/js/players.js
function switchMemberSection(sec) {	
	"use strict";
	
	$('#sub_title').fadeOut();
	$('#players').css('height', $('#players').innerHeight());
	$('.artist').each(function() {
		$(this).fadeOut();
	});
	
	$('.artist').promise().done(function () {
		/*****************************
		* show loading ani
		******************************/
		$('#players').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
		
		/*****************************
		* update browser location
		******************************/
		pathChange(BASE+'/orchestra/players/'+sec.toLowerCase().replace(" ", "_"));
		
		/*************************
		* If on base players page then AJAXify, otherwise redirect
		*************************/
		if ($('#players').length>0) {
			/*****************************
			* Get the new list of artists
			******************************/
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/orchestra/players.php',
				data: {
					section:sec
				},
				success:function(artists) {
					$('#players').hide();
					$('#players').html(artists);
					$('#players').fadeIn('slow', function() {
						$('#players').css('height', 'auto');
					});
					$('#sub_title').text(sec.replace(/_/gi, ' '));
					$('#sub_title').fadeIn();
					
					$(".page_image").lazyload({
						 effect : "fadeIn"
					 });
					 $(window).trigger('scroll');
					
					// reset all images for editing 
					if (typeof is_admin !== 'undefined'){
						$('.page_image').bind('load', function() {
							images.setForEdit($(this));
						});
					}
				}
			});
		} else {
			document.location.href = URL+BASE+'/orchestra/players/'+sec.toLowerCase().replace(" ", "_"); 
		}
	});
}


///var/www/philharmonia.co.uk/js/resize.js
var screen_width = $(window).innerWidth();
$(window).resize(function(e) {
	"use strict";

	screen_width = $(window).innerWidth();
	concerts.position();
	
	// remove image height on resize to prevent distortion from lazyload
	$('.page_image').attr('height', 'auto');
});

///var/www/philharmonia.co.uk/js/search.js
function searchOpen() {
	"use strict";
	
	$('#sml_search').blur();
	$('#screener').bind('click', function() {
		$('#screener').css('opacity', 0.8);
		$('#screener').hide();
		$('#search_container').slideUp();	
	});
	$('#screener').css('opacity', 0.01);
	$('#screener').show();
	$('#search_container').show();
	$('#search_container').slideDown(function() {
		$('#search_container').children().fadeIn(function() {
			$('#search').focus();
		});
	});	
}

$(document).ready(function(){	
	"use strict";
	$.widget( "custom.catcomplete", $.ui.autocomplete, {
		_renderMenu: function( ul, items ) {
			var that = this,
			currentCategory = "";
			$.each( items, function( index, item ) {
				if (item.category !== currentCategory) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				that._renderItemData( ul, item );
			});
		}
	});
	setAutoComplete('search');
	setAutoComplete('big_search');
	
	$('#big_search').bind('keyup', function (e) {
		if (e.keyCode===13) {
			doSearch($(this).val());
		}
	});
	$('#search, #sml_search').keyup(function (e) {
		if (e.keyCode===13) {
			if ($(this).attr('id')==='sml_search') { $('#search').val($(this).val()); }
			$('#search_from').submit();
		}
	});
	$('.search_types').bind('click', function() {
		$('#search').focus();
	});
});

function jumpToSearch(term) {
	"use strict";
	window.location.href=BASE+"/search?q="+term;
}
function setAutoComplete (target, callback) {
	"use strict";
	if (callback===null) {
		callback = function(event, ui) {
			if (!$('#search_results').length) {
				jumpToSearch(ui.item.label);
			} else {
				doSearch(ui.item.label);
			}
		};
	}
	$(function() {
		$( "#"+target).catcomplete({
			source: function(request, response) { 
						$.ajax({
							type: "GET",
							url: BASE+'/lib/ajax/search_suggestions.php',
							dataType: 'json',
							data: {
								q: request.term,
								format: 'json'
							},
							success: function (data) {
								if (data!=='') {
									response($.map(data, function (item) {
										return {
											label: item.title,
											category: item.category,
											value: item.title,
											id: item.id
										};
									}));
								}
							}
						}); 
					},
			minLength: 2,
			select: callback
		});
	});
}


function doSearch(q, cat) {
	"use strict";
	$('#big_search, #search').blur();
	
	/**************************
	* Break query in to array and get longest two words
	**************************/
	var arr = q.split(" ");
	var sorted = arr.sort(function (a, b) { return b.length - a.length; });
	
	$('#search_tags').html('');
	for (var i=0; i<sorted.length; i++) {
		if (i===2) { break; }
		$('#search_tags').append("<input type='button' onclick='doSearch(\""+sorted[i]+"\")' value=\""+sorted[i]+"\">");
	}
	
	if (cat===null) {
		cat = '';
	}
	$.ajax({
		type:'GET',
		url:BASE+'/lib/search.php',
		data:{
			q:q,
			category:cat
		},
		beforeSend:function() {
			$('#sub_title').html('searching...');
		},
		success:function(data) {
			$('#sub_title').html('');
			$('#search_results').html(data);
			var query = 'q='+q;
			query += (cat!=='' && cat!='undefined') ? '&cat='+cat : '';
			pathChange(BASE+'/search?'+query);
			
			$(".page_image").lazyload({
				effect : "fadeIn"
			});
			$(window).trigger('scroll');
			
			$('.search_tab').bind('click', function() {
				$('.search_tab').removeClass('search_active');
				$(this).addClass('search_active');
				
				$('.search_results_category').css('display', 'none');
				$('.search_category').css('display', 'none');
				$('#other_search_results').css('display','none');
				
				cat = $(this).text().toLowerCase();
				if (cat!=='all results') {
					$('#search_'+cat).css('display', 'block');
				} else {
					$('.search_category').css('display', 'block');
					$('#other_search_results').css('display','block');
				}
			});
		}
	});
}

///var/www/philharmonia.co.uk/js/seating.js
var seating = {
	init:function() {
		$('img.seating_plan').each(function() {
			$(this).bind('mouseenter', function() {
				$(this).attr('src', $(this).attr('src').replace("_off", "_on"));
			});
			$(this).bind('mouseleave', function() {
				$(this).attr('src', $(this).attr('src').replace("_on", "_off"));
			});
		});
	},
	
	areaMap:null,
	
	selectArea: function(id, perf) {
		if (seating.areaMap==null) {
			seating.areaMap = $('#seating_plan').html();
			$('#return_to_hall').fadeIn();
		}
		if (id!="") {
			$('#seating_area_select').val(id);
			$('#seating_plan').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
			$.ajax({
				url:BASE+'/lib/ajax/concerts/seating.php',
				type:'POST',
				data: {
					area:id,
					perf:perf
				},
				success:function(response) {
					$('#seating_plan').html(response);
					$('.seat').bind('click', function() {
						seating.addSeat(this)
					});
					seating.init();
					$('img.seating_plan').each(function() {
						$(this).attr('src', $(this).attr('src').replace("_on", "_off"));
					});
					$('.'+id+'_icon').attr('src', $('.'+id+'_icon').attr('src').replace("_off", "_here"));
					$('.'+id+'_icon').unbind('mouseenter');
					$('.'+id+'_icon').unbind('mouseleave');
					
					/********************
					* Make sure select seat button is in view
					********************/
					if ($('#add_seats_btn').offset().top > $(window).innerHeight()-70) {
						$('#add_seats_btn').css({
							bottom:Math.round($('#add_seats_btn').offset().top - ($(window).innerHeight()-70))
						});
					}
				}
			});
		}
	},
	
	returnToHall:function() {
		$('#return_to_hall').fadeOut();
		$('#seating_plan').fadeOut(function() {
			$('#seating_plan').html(seating.areaMap);	
			$('#seating_plan').fadeIn();
			seating.init();
			seating.areaMap=null;
		});
	},
	
	addSeat:function (seat) {
		"use strict";

		if ($(seat).hasClass('selected')) {
			$(seat).removeClass('selected');
			$('#seat'+$(seat).data('row')+$(seat).data('seatnum')).remove();
			$(seat).addClass('available');
			
		} else if ($(seat).hasClass('available')) {
			$('#selected_seats').append('<div id="seat'+$(seat).data('row')+$(seat).data('seatnum')+'"><p><a href="javascript:void(0)" onclick="$(\'#seat'+$(seat).data('row')+$(seat).data('seatnum')+'\').remove(); $(\'#'+$(seat).data('row')+$(seat).data('seatnum')+'\').removeClass(\'selected\');"><img style="display:inline" src="'+BASE+'/core/images/icons/redcross.png" /></a> <strong>Row</strong>:'+$(seat).data('row')+' <strong>Seat</strong>:'+$(seat).data('seatnum')+' <span style="float:right">&pound;'+$(seat).data('price')+'</span></p><input type="hidden" value="1789" name="price_type[]" /><input type="hidden" value="" name="zone" /><input type="hidden" value="'+$(seat).data('seat')+'" name="seat[]" /></div>');
			
			if ($(seat).hasClass('wheelchair')) {
				alert('Please note, you have chosen a Wheelchair seat. This includes an additional standard seat, but is only valid to those accompanying a wheelchair user.');
			}

			$('#seat_perf_no').val($(seat).data('perf'));
			$(seat).addClass('selected');
		}
	
	},
	
	selectSeats:function (seat) {
		"use strict";
		$('#add_seat_to_cart').submit();
	}
}

///var/www/philharmonia.co.uk/js/settings.js
var settings = {
	screenSize:function (callback) {
		"use strict";
		
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/settings/screen_size.php',
			data:{
				width:$(window).innerWidth(),
				height:$(window).innerHeight()
			},
			success:function() {
				if (typeof callback === "function") {
					callback();
				}
			}
		});
	}
};

$(document).ready(function() {
	"use strict";
	settings.screenSize();
});

///var/www/philharmonia.co.uk/js/shop.js
var shop = {
	init:function() {
		$('#shop_filters select').each(function() {
			$(this).bind('mousedown', function() {
				shop.clearFilters()
			});
			$(this).bind('change', function() {
				shop.filter($(this).data('type'), $(this).val())
			});
		});
	},
	filter: function(type, val) {
		$('.featured').fadeOut();
			
		$('#futher_products').html("<h4 style='width:98%;'>Other products available at Amazon</h4><img src='"+BASE+"/core/images/loading.gif' />");
		
		$('.product').fadeOut();
			
		$('.product').promise().done(function() {
			$('.product').each(function() {
				if ($(this).data(type).indexOf(val)!=-1) {
					$(this).fadeIn();
				} else {
					$(this).fadeOut();
				}
			});
		});
		
		pathChange(BASE+'/shop?'+type+"="+val);
		
		$.ajax({
			type:'GET',
			url:BASE+'/tools/amazon/amazon.php',
			data:{
				q:val
			},
			success:function(response) {
				$('#futher_products').html(response);
			}
		});
	},
	
	clearFilters: function() {
		$('#shop_filters select').val('');
		$('.product').show();	
	}
}
$(document).ready(function() {
	shop.init();
});

///var/www/philharmonia.co.uk/js/video.js
var video = {

	/***********************
	* send video id to get output code of video
	***********************/
	getVideo: function(video_id, callback) {
		"use strict"; 
		
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/videos/get_video.php',
			data: {
				id:video_id
			},
			success:function(data) {
				if (callback!==null) {
					callback(data);
				}
			}
		});
	},
	
	/*********************************
	* Connect or exclude video from content (link_type 1 = connect, 2 = exclude)
	*********************************/
	connect: function (video_id, resource_id, resource_type, link_type) {
		"use strict"; 
		
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/assets/add_video_connections.php',
			data:{
				video_id:video_id,
				resource_id:resource_id,
				resource_type:resource_type,
				link_type:link_type,
				'function':'add_video_connections'
			},
			success:function() {
				window.location.href=window.location.href;
			}
		});
	},
	
	disconnect: function (video_id, resource_id, resource_type) {
		"use strict"; 
		
		if (confirm('Are you sure you wish to delete this video connection/exclusion?')) {
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/assets/delete_video_connections.php',
				data:{
					video_id:video_id,
					resource_id:resource_id,
					resource_type:resource_type,
					'function':'delete_video_connections'
				},
				success:function() {
					window.location.href=window.location.href;
				}
			});
		}
	},
	
	carousel: {
		current_film: 0,
		film_type:'All',
		right: function() {
			"use strict"; 
		
			$('.channel_film').each(function() {
				$(this).fadeOut('slow');
			});
			video.carousel.current_film += 5;
			$('.channel_film').promise().done(function() {
				video.carousel.getFilms();
			});
		},
		left: function() {
			"use strict"; 
		
			$('.channel_film').each(function() {
				$(this).fadeOut('slow');
			});
			video.carousel.current_film -= 5;
			if (video.carousel.current_film<0) { video.carousel.current_film = 0; }
			$('.channel_film').promise().done(function() {
				video.carousel.getFilms();
			});
		},
		getFilms:function() { 
			"use strict"; 
		
			$('#film_channel_container').html('<div class="loading"><img src="'+BASE+'/core/images/loading.gif" /></div>');
			$.ajax({
				type:'POST',
				url:BASE+'/lib/ajax/videos/get_videos.php',
				data: {
					offset:video.carousel.current_film,
					type:video.carousel.film_type
				},
				success:function(data) {
					$('#film_channel_container').html(data);
				}
			});
		}
	},
	
	/**************************
	* add hover functionality to film library
	***************************/
	onhover:function() {
		"use strict"; 
		
		$('.film_listing').each(function() {			
			//console.log($(this).children('img').length);
			$(this).children('p').css('height', $(this).children('img').innerHeight());
		});
	}
};

///var/www/philharmonia.co.uk/js/vimeo.js
var vimeo = {
	/**
	 * Called once a vimeo player is loaded and ready to receive
	 * commands. You can add events and make api calls only after this
	 * function has been called.
	 */
	ready:function(player_id) {

		// Keep a reference to Froogaloop for this player
		var froogaloop = $f(player_id);

		/**
		 * Prepends log messages to the example console for you to see.
		 */
		function apiLog(message) {
			console.log(message);
		}
		
		froogaloop.addEvent('loadProgress', function(data) {
			
			/*apiLog('loadProgress event : ' + data.percent + ' : ' + data.bytesLoaded + ' : ' + data.bytesTotal + ' : ' + data.duration);
			
			var loaded = (data.duration/100)*(data.percent*100);
		
			$('.'+player_id).each(function() {				
				if ($(this).data('time') < loaded) {
					$(this).removeClass('vimeo_disabled');
				}
			});*/
								
		});

		/**
		 * Sets up the actions for the buttons that will perform simple
		 * api calls to Froogaloop (play, pause, etc.). These api methods
		 * are actions performed on the player that take no parameters and
		 * return no values.
		 */
		// Call play when play button clicked
		$('.play').bind('click', function() {
			froogaloop.api('play');
		});

		// Call pause when pause button clicked
		$('.pause').bind('click', function() {
			froogaloop.api('pause');
		});

		// Call unload when unload button clicked
		$('.unload').bind('click', function() {
			froogaloop.api('unload');
		});

		// Call seekTo when seek button clicked
		$('.seek').bind('click', function(e) {
			// Don't do anything if clicking on anything but the button (such as the input field)
			if (e.target != this) {
				return false;
			}

			// Grab the value in the input field
			var seekVal = this.querySelector('input').value;

			// Call the api via froogaloop
			froogaloop.api('seekTo', seekVal);
		});

		// Call setVolume when volume button clicked

		$('.volume').bind('click', function(e) {
			// Don't do anything if clicking on anything but the button (such as the input field)
			if (e.target != this) {
				return false;
			}

			// Grab the value in the input field
			var volumeVal = this.querySelector('input').value;

			// Call the api via froogaloop
			froogaloop.api('setVolume', volumeVal);
		});
	

		// Get the current time and log it to the API console when time button clicked
		$('.time').bind('click', function(e) {
			froogaloop.api('getCurrentTime', function (value, player_id) {
				apiLog('getCurrentTime : ' + value);
			});
		});

		// Get the duration and log it to the API console when time button clicked
		$('.duration').bind('click', function(e) {
			froogaloop.api('getDuration', function (value, player_id) {
				apiLog('getDuration : ' + value);
			});
		});

		// Get the video url and log it to the API console when time button clicked
		$('.url').bind('click', function(e) {
			froogaloop.api('getVideoUrl', function (value, player_id) {
				apiLog('getVideoUrl : ' + value);
			});
		});

		// Get the embed code and log it to the API console when time button clicked
		$('.embed').bind('click', function(e) {
			froogaloop.api('getVideoEmbedCode', function (value, player_id) {
				// Use html entities for less-than and greater-than signs
				value = value.replace(/</g, '&lt;').replace(/>/g, '&gt;');

				apiLog('getVideoEmbedCode : ' + value);
			});
		});

		// Get the paused state and log it to the API console when time button clicked
		$('.paused').bind('click', function(e) {
			froogaloop.api('paused', function (value, player_id) {
				apiLog('paused : ' + value);
			});
		});

		// Get the paused state and log it to the API console when time button clicked
		$('.getVolume').bind('click', function(e) {
			froogaloop.api('getVolume', function (value, player_id) {
				apiLog('volume : ' + value);
			});
		});

		// Get the paused state and log it to the API console when time button clicked
		$('.width').bind('click', function(e) {
			froogaloop.api('getVideoWidth', function (value, player_id) {
				apiLog('getVideoWidth : ' + value);
			});
		});

		// Get the paused state and log it to the API console when time button clicked
		$('.height').bind('click', function(e) {
			froogaloop.api('getVideoHeight', function (value, player_id) {
				apiLog('getVideoHeight : ' + value);
			});
		});
	
		var prev_elem = null;
		froogaloop.addEvent('playProgress', function(data) {
			//apiLog('playProgress event : ' + data.seconds + ' : ' + data.percent + ' : ' + data.duration);
			
			$('.'+player_id).each(function() {				
				if (parseFloat($(this).data('time'))-1 < parseFloat(data.seconds) && 
					(!$(this).hasClass('vimeo_active') && !$(this).hasClass('vimeo_past'))) 
					{
						$(this).addClass('vimeo_active');
						
						if (prev_elem!=null) {
							prev_elem.removeClass('vimeo_active');
							prev_elem.addClass('vimeo_past');
						}
						prev_elem = $(this);
				}
			});
		});
	
		froogaloop.addEvent('play', function(data) {
			//apiLog('play event');
		});
	
		froogaloop.addEvent('pause', function(data) {
			//apiLog('pause event');
		});
	
		froogaloop.addEvent('finish', function(data) {
			//apiLog('finish');
		});

		froogaloop.addEvent('seek', function(data) {
			//apiLog('seek event : ' + data.seconds + ' : ' + data.percent + ' : ' + data.duration);
		});
	},
	
	cue: function (val, cls) {
		$('.'+cls).each(function() {
			froogaloop = $f($(this).attr('id'));

			froogaloop.api('seekTo', val);
			froogaloop.api('play');
		});				
	},
	
	play: function (id) {
		froogaloop = $f(id);
		froogaloop.api('play');
	},
	
	seek: function (val, id, percent) {
		froogaloop = $f(id);
		
		$('.vimeo_past').each(function() {				
			$(this).removeClass('vimeo_past');		
		});
		
		if (percent==null || !percent) {
			froogaloop.api('seekTo', val);
			froogaloop.api('play');
		} else {
			var duration = 0;
			
			froogaloop.api('volume', function (value, player_id) {
				duration = parseInt(value);
				
				seekpos = (duration/100) * val;
				console.log(duration, seekpos);
				// Call the api via froogaloop
				froogaloop.api('seekTo', seekpos);
				froogaloop.api('play');
			});
		}				
	},
	
	changeVolumeUp: function(id,volume,target) {
		froogaloop = $f(id);
		froogaloop.api('setVolume', volume);
		if (volume<target) {
			setTimeout(function() {
				volume+=0.1;
				vimeo.changeVolumeUp(id,volume,target);
			}, 50)
		}
	},
	
	changeVolumeDown: function(id,volume,target) {
		froogaloop = $f(id);
		froogaloop.api('setVolume', volume);
		if (volume>target) {
			setTimeout(function() {
				volume-=0.1;
				vimeo.changeVolumeDown(id,volume,target);
			}, 50)
		}
	},
	
	instantMute: function(id) {
		froogaloop = $f(id);
		froogaloop.api('setVolume', 0);
	},
	
	mute: function(id) {
		vimeo.changeVolumeDown(id,1,0);
	},
	
	unmute:  function(id) {
		vimeo.changeVolumeUp(id,0,1);
	}
};

$(document).ready(function() {	
	$('.vimeo_film').each(function() {
		$f($(this).attr('id')).addEvent('ready', vimeo.ready);
	});	
});

///var/www/philharmonia.co.uk/js/adminNewMenu.js
$('#dropdown_admin').hide();
$( "#topBar_admin" ).click('click', function() {
	$('#dropdown_admin').animate({height: "toggle"});
});

///var/www/philharmonia.co.uk/js/layout_designer.js
// SET - Starting ID
var block_id = 1;

// ADD - Text Box
function addTextBox(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_textblock" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><div class="line md"></div><div class="line xl"></div><div class="line lg"></div><div class="line sm"></div><div class="gap"></div><div class="line lg"></div><div class="line sm"></div><input type="hidden" name="blog_blocks[' + block_id + ']" value="textbox"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.line').hide();
	$('#element_' + block_id).children('.line').delay(500).animate({height: "toggle"});
	block_id++;
}

// ADD - Quote
function addQuote(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_quote" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-quote"></i><div class="line lg"></div><div class="line sm"></div><input type="hidden" name="blog_blocks[' + block_id + ']" value="quote"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.line').hide();
	$('#element_' + block_id).children('.line').delay(500).animate({height: "toggle"});
	block_id++;
}

// ADD - Image
function addImage(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_image" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-trees"></i><input type="hidden" name="blog_blocks[' + block_id + ']" value="image"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.icons').hide();
	$('#element_' + block_id).children('.icons').delay(100).animate({fontSize: "toggle"});
	block_id++;
}

// ADD - Gallery
function addGallery(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_gallery" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-trees"></i><button disabled class="controls disabled" id="gallery_prev"><img src="../../../../images/icons/newArrow.png"></button><button disabled class="controls" id="gallery_next"><img src="../../../../images/icons/newArrow.png"></button><input type="hidden" name="blog_blocks[' + block_id + ']" value="gallery"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.icons').hide();
	$('#element_' + block_id).children('.icons').delay(100).animate({fontSize: "toggle"});
	block_id++;
}

// ADD - Video
function addVideo(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_video" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-play"></i><input type="hidden" name="blog_blocks[' + block_id + ']" value="video"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.icons').hide();
	$('#element_' + block_id).children('.icons').delay(100).animate({fontSize: "toggle"});
	block_id++;
}

// ADD - Files
function addFile(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_file" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-page"></i><div class="line md"></div><div class="line sm"></div><input type="hidden" name="blog_blocks[' + block_id + ']" value="file"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.line').hide();
	$('#element_' + block_id).children('.line').delay(500).animate({height: "toggle"});
	block_id++;
}

// ADD - Embed
function addEmbed(){
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<div class="layout_embed" id="element_' + block_id + '"><i class="icons fi-trash remove_block" onClick="deleteBlock(' + block_id + ');"></i><i class="icons fi-comment-quotes"></i><div class="line sm"></div><div class="line lg"></div><div class="line lg"></div><div class="line lg"></div><input type="hidden" name="blog_blocks[' + block_id + ']" value="embed"></div>';
    document.getElementById('form_builder').appendChild(newdiv);
	$('#element_' + block_id).hide();
	$('#element_' + block_id).delay(50).animate({padding: "toggle"});
	$('#element_' + block_id).children('.line').hide();
	$('#element_' + block_id).children('.line').delay(500).animate({height: "toggle"});
	block_id++;
}

function deleteBlock(blockID){
	$('#element_' + blockID).fadeOut(300, function(){ $(this).remove();});
}

// Gallery Extension
function GalleryExtension(id){
	var id = id + 4;
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<input type="file" name="gallery_' + id + '">';
    document.getElementById('gallery_extension').appendChild(newdiv);
}

///var/www/philharmonia.co.uk/js/news.js
var currentNewsFilter = "all",
	currentNewsItemCount = 0,
	newNewsData,
	months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
	weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
	filters = ["all", "orchestra", "digital", "education", "events", "recordings"],
	newsFilterTotals = [],
	targetHnewsitem = 0,
	currentWindow = 1,
	previousWindow = -1,
	initialFilterSet = true;
	
/* Check for filter to apply on page load */
function setInitialFilter() {
	var possibleParams = window.location.search,
		paramPairs = [],
		param;
	
	if (possibleParams.length > 0) {
		paramPairs = possibleParams.split("&");
		paramPairs.forEach( function(val) {
			if (val.indexOf("newsFilter") !== -1) {
				param = val.split("=");
				currentNewsFilter = param[1]
				if (currentNewsFilter.indexOf("?") !== -1) {
					currentNewsFilter = currentNewsFilter.splitString(1, currentNewsFilter.length);
				}
			}
		});
	}
	
	if (filters.indexOf(currentNewsFilter) === -1) {
		currentNewsFilter = "all";
	}
	
	$("#filterOpts input:checked")[0].checked = false;
	$("#filterOpts input[value=" + currentNewsFilter + "]")[0].checked = true;
	newsFilterSetActive();
}
	
/* switch between desktop and mobile filters */
function switchDsktopMobileFilter()  {
	"use strict";
	if ($(window).width() > 1024) {
		currentWindow = 1;
		if (currentWindow !== previousWindow) {
			previousWindow = currentWindow;
			$("#mobileShowFilter").attr("aria-hidden", "true");
			if ($("#filterOpts").hasClass("hiddenArea")) {
				$("#filterOpts").removeClass("hiddenArea").attr("aria-hidden", "false");
			}
		}
	}
	else {
		currentWindow = 0;
		if (currentWindow !== previousWindow) {
			previousWindow = currentWindow;
			$("#mobileShowFilter").attr("aria-hidden", "false");
			$("#filterOpts").addClass("hiddenArea").attr("aria-hidden", "true");
		}
	}
}

/* (< 1024) show hide filter options */
function showHideFilter() {
	if ($("#filterOpts").hasClass("hiddenArea")) {
		$("#filterOpts").removeClass("hiddenArea").attr("aria-hidden", "false");
	} 
	else {
		$("#filterOpts").addClass("hiddenArea").attr("aria-hidden", "true");
	}
}

	
/*newsFilterTotals*/
function getTotalPostsbySubject() {
	"use strict";
	var totalsString = $("#newsFilterTotals").text();
	newsFilterTotals = totalsString.split(",");
}

/* change the active filter */
function newsFilterSetActive() {
	"use strict";
	
	$('#newsFilter input[type=radio]').each( function() {
		if ($(this).parent().hasClass("activeNewsTag")) {
			$(this).parent().removeClass("activeNewsTag");
		}
	});
	
	$('#newsFilter input[type=radio]:checked').parent().addClass("activeNewsTag");
	
	if (window.location.href.indexOf("article") === -1) {
		if (currentNewsFilter !== $('#newsFilter input[type=radio]:checked').val()) {
			$("#newsItemsHolder").html("");
			currentNewsItemCount = 0;
			currentNewsFilter = $('#newsFilter input[type=radio]:checked').val();
		}
		
		requestNewItems();
	}
	else if (initialFilterSet !== true) {
		currentNewsFilter = $('#newsFilter input[type=radio]:checked').val();
		window.location = "/news?newsFilter=" + currentNewsFilter;
	}
	
	initialFilterSet = false;
}

/* add to / replace news feed */
function populateNewsfeed() {
	"use strict";
		
	newNewsData.forEach( function(val) {
		var urlTitle = val.news_title.replace(/[^a-zA-Z0-9]/g, ""),
			date = new Date(val.news_date * 1000),
			year = date.getFullYear(),
			month = months[date.getMonth()],
			day = date.getDate(),
			dOW = weekdays[date.getDay()];
		
		$("#newsItemsHolder").append(
			'<div class="newsItemThumb" id="newsThumb' + val.id + '">'
			+ '<div class="newsThumbImg" aria-hidden="true"><a href="/news/article/' + val.id + '/' + val.news_title + '?newsFilter=' + currentNewsFilter + '" class="newsLink"></a></div>'
			+ '<div class="newsThumbInfo">'
			+ '<div class="newsThumbCopy">'
			+ '<h2><a href="/news/article/' + val.id + '/' + val.news_title + '?newsFilter=' + currentNewsFilter + '" style="font-size: inherit;">' + val.news_title + '</a></h2>'
			+ '<p class="newsDate">' + dOW + " " + day + " " + month + " " + year + '</p>'
			+ '</div>'
			/* Href includes filter information as this should still show up on article page*/
			/* + '<a href="/news/' + val.news_id + "/" + urlTitle + '?newsFilter=' + currentNewsFilter + '" class="newsLink">Read more</a>'*/
			/* Real href to use above, temporary fake below */
			+ '<a href="/news/article/' + val.id + '/' + val.news_title + '?newsFilter=' + currentNewsFilter + '" class="newsLink">Read more</a>'
			+ '</div>'
			+ '</div>'
		);
		
		$("#newsThumb" + val.id).find(".newsThumbImg").css({
			backgroundImage: 'url(../assets/news/thumbnails/' + val.news_thumb + ')'
		});
	});
	newsFeedCheckLayout();
}

/* Check word length of descriptions to fit current sizes */
function newsFeedCheckLayout() {
	"use strict";
	var newsItemContentH;
	targetHnewsitem = $(".newsThumbImg").outerHeight() - $(".newsLink").outerHeight();
		
	$(".newsItemThumb").each( function() {
		newsItemContentH = $(this).find(".newsThumbCopy").outerHeight();
		
		if (newsItemContentH > targetHnewsitem) {
			shortenDescription($(this));
		}
	});
}

/* shorten description length */
function shortenDescription(element, toRemove) {
	"use strict";
	if (!toRemove > 0) { 
		toRemove = 20;
	}
	
	var currentTxt = element.find(".newsDesc").text();
	
	if (currentTxt.length > 20) {
		
		var newTxt  = currentTxt.substring(0, currentTxt.length - toRemove) + "...",
			currentH = element.find(".newsThumbCopy").outerHeight();
			
		element.find(".newsDesc").text(newTxt);
		
		if (currentH > targetHnewsitem && toRemove > 5) {
			currentTxt = element.find(".newsDesc").text(); /* now has new content */
			var newRemove = Math.floor(currentTxt.length - ((targetHnewsitem / currentH) * currentTxt.length));
			shortenDescription(element, newRemove);
		}
	}
	else {
		element.find(".newsDesc").text("");
	}
}

/* get new news items */
function requestNewItems() {
	"use strict";
	
	
	var xmlhttp = new XMLHttpRequest(),
		filterNo = filters.indexOf(currentNewsFilter);
		  
	xmlhttp.onreadystatechange=function() {
		console.log('test');
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
			
			newNewsData = JSON.parse(xmlhttp.responseText);
			currentNewsItemCount += newNewsData.length;
			populateNewsfeed();
			checkMorePostsExist();
		}
	};
		
	if (currentNewsItemCount < newsFilterTotals[filterNo]) {
		xmlhttp.open("GET","/lib/ajax/news.php?tag=" + currentNewsFilter + "&start_point=" + currentNewsItemCount, true);
		xmlhttp.send();
	}
}

/* if no more posts, disable button */
function checkMorePostsExist() {
	"use strict";
	var filterNo = filters.indexOf(currentNewsFilter);
	
	if (currentNewsItemCount >= newsFilterTotals[filterNo]) {
		$("#news_morePosts").addClass("buttonDisabled").attr("aria-disabled", "true");
	}
	else if ($("#news_morePosts").hasClass("buttonDisabled")) {
		$("#news_morePosts").removeClass("buttonDisabled").attr("aria-disabled", "false");
	}
}

///var/www/philharmonia.co.uk/js/homepage_builder.js
// FUNCTION - Update Banners
function updateBanners(str,name) {

	// FILTERS - New
	var filterValue = str;
	var filterName = name;
	
	// GET - Target Div
	xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("banners-wrapper").innerHTML=xmlhttp.responseText;
		}
	}
	
	if (filterName === 'block_style' && filterValue === 'vertical_split') {
		$("#artist1_name").attr("disabled","disabled");
		$("#artist1_instrument").attr("disabled","disabled");
		$("#artist2_name").attr("disabled","disabled");
		$("#artist2_instrument").attr("disabled","disabled");
		$("#description").attr("disabled","disabled");
	}
	
	if (filterName === 'block_style' && filterValue === 'horizontal_split') {
		$("#artist1_name").removeAttr("disabled","disabled");
		$("#artist1_instrument").removeAttr("disabled","disabled");
		$("#artist2_name").removeAttr("disabled","disabled");
		$("#artist2_instrument").removeAttr("disabled","disabled");
		$("#description").removeAttr("disabled","disabled");
	}
	
	if (filterName === 'bgcolour') {
		$("#brandselect").val('Select');
	}
	if (filterName === 'bgbrandcolour') {
		$("#bgcolour").val(filterValue);
	}
	
	if (filterName === 'textbrandcolour') {
		$("#textcolour").val(filterValue);
	}
	
	// OUTPUT
	xmlhttp.open("GET","/lib/ajax/admin/homepage_banner_builder.php?filter_name=" + filterName + "&filter_value=" + filterValue,true);
	xmlhttp.send();

};

$(document).ready(function (e) {
	// Function to preview image after validation
	$(function() {
		$("#file").change(function() {
			$("#message").empty(); // To remove the previous error message
			var file = this.files[0];
			var imagefile = file.type;
			var match= ["image/jpeg","image/png","image/jpg"];
			if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
			{
				$('#previewing').attr('src','noimage.png');
				$("#message").html("<p id='error'>Please Select A valid Image File</p>"+"<h4>Note</h4>"+"<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
				return false;
			}
			else
			{
				var reader = new FileReader();
				reader.onload = imageIsLoaded;
				reader.readAsDataURL(this.files[0]);
			}
		});
	});
	function imageIsLoaded(e) {
		$("#file").css("color","green");
		$('#image_preview').css("display", "block");
		$('#previewing').attr('src', e.target.result);
		$('#previewing').attr('width', '100%');
		$('#previewing').attr('height', '100%');
	};
});

///var/www/philharmonia.co.uk/js/colourpicker.js
/**
 *
 * Color picker
 * Author: Stefan Petre www.eyecon.ro
 * 
 * Dual licensed under the MIT and GPL licenses
 * 
 */

	var ColorPicker = function () {
		var
			ids = {},
			inAction,
			charMin = 65,
			visible,
			tpl = '<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>',
			defaults = {
				eventName: 'click',
				onShow: function () {},
				onBeforeShow: function(){},
				onHide: function () {},
				onChange: function () {},
				onSubmit: function () {},
				color: 'ff0000',
				livePreview: true,
				flat: false
			},
			fillRGBFields = function  (hsb, cal) {
				var rgb = HSBToRGB(hsb);
				$(cal).data('colorpicker').fields
					.eq(1).val(rgb.r).end()
					.eq(2).val(rgb.g).end()
					.eq(3).val(rgb.b).end();
			},
			fillHSBFields = function  (hsb, cal) {
				$(cal).data('colorpicker').fields
					.eq(4).val(hsb.h).end()
					.eq(5).val(hsb.s).end()
					.eq(6).val(hsb.b).end();
			},
			fillHexFields = function (hsb, cal) {
				$(cal).data('colorpicker').fields
					.eq(0).val(HSBToHex(hsb)).end();
			},
			setSelector = function (hsb, cal) {
				$(cal).data('colorpicker').selector.css('backgroundColor', '#' + HSBToHex({h: hsb.h, s: 100, b: 100}));
				$(cal).data('colorpicker').selectorIndic.css({
					left: parseInt(150 * hsb.s/100, 10),
					top: parseInt(150 * (100-hsb.b)/100, 10)
				});
			},
			setHue = function (hsb, cal) {
				$(cal).data('colorpicker').hue.css('top', parseInt(150 - 150 * hsb.h/360, 10));
			},
			setCurrentColor = function (hsb, cal) {
				$(cal).data('colorpicker').currentColor.css('backgroundColor', '#' + HSBToHex(hsb));
			},
			setNewColor = function (hsb, cal) {
				$(cal).data('colorpicker').newColor.css('backgroundColor', '#' + HSBToHex(hsb));
			},
			keyDown = function (ev) {
				var pressedKey = ev.charCode || ev.keyCode || -1;
				if ((pressedKey > charMin && pressedKey <= 90) || pressedKey == 32) {
					return false;
				}
				var cal = $(this).parent().parent();
				if (cal.data('colorpicker').livePreview === true) {
					change.apply(this);
				}
			},
			change = function (ev) {
				var cal = $(this).parent().parent(), col;
				if (this.parentNode.className.indexOf('_hex') > 0) {
					cal.data('colorpicker').color = col = HexToHSB(fixHex(this.value));
				} else if (this.parentNode.className.indexOf('_hsb') > 0) {
					cal.data('colorpicker').color = col = fixHSB({
						h: parseInt(cal.data('colorpicker').fields.eq(4).val(), 10),
						s: parseInt(cal.data('colorpicker').fields.eq(5).val(), 10),
						b: parseInt(cal.data('colorpicker').fields.eq(6).val(), 10)
					});
				} else {
					cal.data('colorpicker').color = col = RGBToHSB(fixRGB({
						r: parseInt(cal.data('colorpicker').fields.eq(1).val(), 10),
						g: parseInt(cal.data('colorpicker').fields.eq(2).val(), 10),
						b: parseInt(cal.data('colorpicker').fields.eq(3).val(), 10)
					}));
				}
				if (ev) {
					fillRGBFields(col, cal.get(0));
					fillHexFields(col, cal.get(0));
					fillHSBFields(col, cal.get(0));
				}
				setSelector(col, cal.get(0));
				setHue(col, cal.get(0));
				setNewColor(col, cal.get(0));
				cal.data('colorpicker').onChange.apply(cal, [col, HSBToHex(col), HSBToRGB(col)]);
			},
			blur = function (ev) {
				var cal = $(this).parent().parent();
				cal.data('colorpicker').fields.parent().removeClass('colorpicker_focus');
			},
			focus = function () {
				charMin = this.parentNode.className.indexOf('_hex') > 0 ? 70 : 65;
				$(this).parent().parent().data('colorpicker').fields.parent().removeClass('colorpicker_focus');
				$(this).parent().addClass('colorpicker_focus');
			},
			downIncrement = function (ev) {
				var field = $(this).parent().find('input').focus();
				var current = {
					el: $(this).parent().addClass('colorpicker_slider'),
					max: this.parentNode.className.indexOf('_hsb_h') > 0 ? 360 : (this.parentNode.className.indexOf('_hsb') > 0 ? 100 : 255),
					y: ev.pageY,
					field: field,
					val: parseInt(field.val(), 10),
					preview: $(this).parent().parent().data('colorpicker').livePreview					
				};
				$(document).bind('mouseup', current, upIncrement);
				$(document).bind('mousemove', current, moveIncrement);
			},
			moveIncrement = function (ev) {
				ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
				if (ev.data.preview) {
					change.apply(ev.data.field.get(0), [true]);
				}
				return false;
			},
			upIncrement = function (ev) {
				change.apply(ev.data.field.get(0), [true]);
				ev.data.el.removeClass('colorpicker_slider').find('input').focus();
				$(document).unbind('mouseup', upIncrement);
				$(document).unbind('mousemove', moveIncrement);
				return false;
			},
			downHue = function (ev) {
				var current = {
					cal: $(this).parent(),
					y: $(this).offset().top
				};
				current.preview = current.cal.data('colorpicker').livePreview;
				$(document).bind('mouseup', current, upHue);
				$(document).bind('mousemove', current, moveHue);
			},
			moveHue = function (ev) {
				change.apply(
					ev.data.cal.data('colorpicker')
						.fields
						.eq(4)
						.val(parseInt(360*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.y))))/150, 10))
						.get(0),
					[ev.data.preview]
				);
				return false;
			},
			upHue = function (ev) {
				fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				$(document).unbind('mouseup', upHue);
				$(document).unbind('mousemove', moveHue);
				return false;
			},
			downSelector = function (ev) {
				var current = {
					cal: $(this).parent(),
					pos: $(this).offset()
				};
				current.preview = current.cal.data('colorpicker').livePreview;
				$(document).bind('mouseup', current, upSelector);
				$(document).bind('mousemove', current, moveSelector);
			},
			moveSelector = function (ev) {
				change.apply(
					ev.data.cal.data('colorpicker')
						.fields
						.eq(6)
						.val(parseInt(100*(150 - Math.max(0,Math.min(150,(ev.pageY - ev.data.pos.top))))/150, 10))
						.end()
						.eq(5)
						.val(parseInt(100*(Math.max(0,Math.min(150,(ev.pageX - ev.data.pos.left))))/150, 10))
						.get(0),
					[ev.data.preview]
				);
				return false;
			},
			upSelector = function (ev) {
				fillRGBFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				fillHexFields(ev.data.cal.data('colorpicker').color, ev.data.cal.get(0));
				$(document).unbind('mouseup', upSelector);
				$(document).unbind('mousemove', moveSelector);
				return false;
			},
			enterSubmit = function (ev) {
				$(this).addClass('colorpicker_focus');
			},
			leaveSubmit = function (ev) {
				$(this).removeClass('colorpicker_focus');
			},
			clickSubmit = function (ev) {
				var cal = $(this).parent();
				var col = cal.data('colorpicker').color;
				cal.data('colorpicker').origColor = col;
				setCurrentColor(col, cal.get(0));
				cal.data('colorpicker').onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data('colorpicker').el);
			},
			show = function (ev) {
				var cal = $('#' + $(this).data('colorpickerId'));
				cal.data('colorpicker').onBeforeShow.apply(this, [cal.get(0)]);
				var pos = $(this).offset();
				var viewPort = getViewport();
				var top = pos.top + this.offsetHeight;
				var left = pos.left;
				if (top + 176 > viewPort.t + viewPort.h) {
					top -= this.offsetHeight + 176;
				}
				if (left + 356 > viewPort.l + viewPort.w) {
					left -= 356;
				}
				cal.css({left: left + 'px', top: top + 'px'});
				if (cal.data('colorpicker').onShow.apply(this, [cal.get(0)]) != false) {
					cal.show();
				}
				$(document).bind('mousedown', {cal: cal}, hide);
				return false;
			},
			hide = function (ev) {
				if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
					if (ev.data.cal.data('colorpicker').onHide.apply(this, [ev.data.cal.get(0)]) != false) {
						ev.data.cal.hide();
					}
					$(document).unbind('mousedown', hide);
				}
			},
			isChildOf = function(parentEl, el, container) {
				if (parentEl == el) {
					return true;
				}
				if (parentEl.contains) {
					return parentEl.contains(el);
				}
				if ( parentEl.compareDocumentPosition ) {
					return !!(parentEl.compareDocumentPosition(el) & 16);
				}
				var prEl = el.parentNode;
				while(prEl && prEl != container) {
					if (prEl == parentEl)
						return true;
					prEl = prEl.parentNode;
				}
				return false;
			},
			getViewport = function () {
				var m = document.compatMode == 'CSS1Compat';
				return {
					l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
					t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
					w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
					h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
				};
			},
			fixHSB = function (hsb) {
				return {
					h: Math.min(360, Math.max(0, hsb.h)),
					s: Math.min(100, Math.max(0, hsb.s)),
					b: Math.min(100, Math.max(0, hsb.b))
				};
			}, 
			fixRGB = function (rgb) {
				return {
					r: Math.min(255, Math.max(0, rgb.r)),
					g: Math.min(255, Math.max(0, rgb.g)),
					b: Math.min(255, Math.max(0, rgb.b))
				};
			},
			fixHex = function (hex) {
				var len = 6 - hex.length;
				if (len > 0) {
					var o = [];
					for (var i=0; i<len; i++) {
						o.push('0');
					}
					o.push(hex);
					hex = o.join('');
				}
				return hex;
			}, 
			HexToRGB = function (hex) {
				var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
				return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
			},
			HexToHSB = function (hex) {
				return RGBToHSB(HexToRGB(hex));
			},
			RGBToHSB = function (rgb) {
				var hsb = {
					h: 0,
					s: 0,
					b: 0
				};
				var min = Math.min(rgb.r, rgb.g, rgb.b);
				var max = Math.max(rgb.r, rgb.g, rgb.b);
				var delta = max - min;
				hsb.b = max;
				if (max != 0) {
					
				}
				hsb.s = max != 0 ? 255 * delta / max : 0;
				if (hsb.s != 0) {
					if (rgb.r == max) {
						hsb.h = (rgb.g - rgb.b) / delta;
					} else if (rgb.g == max) {
						hsb.h = 2 + (rgb.b - rgb.r) / delta;
					} else {
						hsb.h = 4 + (rgb.r - rgb.g) / delta;
					}
				} else {
					hsb.h = -1;
				}
				hsb.h *= 60;
				if (hsb.h < 0) {
					hsb.h += 360;
				}
				hsb.s *= 100/255;
				hsb.b *= 100/255;
				return hsb;
			},
			HSBToRGB = function (hsb) {
				var rgb = {};
				var h = Math.round(hsb.h);
				var s = Math.round(hsb.s*255/100);
				var v = Math.round(hsb.b*255/100);
				if(s == 0) {
					rgb.r = rgb.g = rgb.b = v;
				} else {
					var t1 = v;
					var t2 = (255-s)*v/255;
					var t3 = (t1-t2)*(h%60)/60;
					if(h==360) h = 0;
					if(h<60) {rgb.r=t1;	rgb.b=t2; rgb.g=t2+t3}
					else if(h<120) {rgb.g=t1; rgb.b=t2;	rgb.r=t1-t3}
					else if(h<180) {rgb.g=t1; rgb.r=t2;	rgb.b=t2+t3}
					else if(h<240) {rgb.b=t1; rgb.r=t2;	rgb.g=t1-t3}
					else if(h<300) {rgb.b=t1; rgb.g=t2;	rgb.r=t2+t3}
					else if(h<360) {rgb.r=t1; rgb.g=t2;	rgb.b=t1-t3}
					else {rgb.r=0; rgb.g=0;	rgb.b=0}
				}
				return {r:Math.round(rgb.r), g:Math.round(rgb.g), b:Math.round(rgb.b)};
			},
			RGBToHex = function (rgb) {
				var hex = [
					rgb.r.toString(16),
					rgb.g.toString(16),
					rgb.b.toString(16)
				];
				$.each(hex, function (nr, val) {
					if (val.length == 1) {
						hex[nr] = '0' + val;
					}
				});
				return hex.join('');
			},
			HSBToHex = function (hsb) {
				return RGBToHex(HSBToRGB(hsb));
			},
			restoreOriginal = function () {
				var cal = $(this).parent();
				var col = cal.data('colorpicker').origColor;
				cal.data('colorpicker').color = col;
				fillRGBFields(col, cal.get(0));
				fillHexFields(col, cal.get(0));
				fillHSBFields(col, cal.get(0));
				setSelector(col, cal.get(0));
				setHue(col, cal.get(0));
				setNewColor(col, cal.get(0));
			};
		return {
			init: function (opt) {
				opt = $.extend({}, defaults, opt||{});
				if (typeof opt.color == 'string') {
					opt.color = HexToHSB(opt.color);
				} else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
					opt.color = RGBToHSB(opt.color);
				} else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
					opt.color = fixHSB(opt.color);
				} else {
					return this;
				}
				return this.each(function () {
					if (!$(this).data('colorpickerId')) {
						var options = $.extend({}, opt);
						options.origColor = opt.color;
						var id = 'collorpicker_' + parseInt(Math.random() * 1000);
						$(this).data('colorpickerId', id);
						var cal = $(tpl).attr('id', id);
						if (options.flat) {
							cal.appendTo(this).show();
						} else {
							cal.appendTo(document.body);
						}
						options.fields = cal
											.find('input')
												.bind('keyup', keyDown)
												.bind('change', change)
												.bind('blur', blur)
												.bind('focus', focus);
						cal
							.find('span').bind('mousedown', downIncrement).end()
							.find('>div.colorpicker_current_color').bind('click', restoreOriginal);
						options.selector = cal.find('div.colorpicker_color').bind('mousedown', downSelector);
						options.selectorIndic = options.selector.find('div div');
						options.el = this;
						options.hue = cal.find('div.colorpicker_hue div');
						cal.find('div.colorpicker_hue').bind('mousedown', downHue);
						options.newColor = cal.find('div.colorpicker_new_color');
						options.currentColor = cal.find('div.colorpicker_current_color');
						cal.data('colorpicker', options);
						cal.find('div.colorpicker_submit')
							.bind('mouseenter', enterSubmit)
							.bind('mouseleave', leaveSubmit)
							.bind('click', clickSubmit);
						fillRGBFields(options.color, cal.get(0));
						fillHSBFields(options.color, cal.get(0));
						fillHexFields(options.color, cal.get(0));
						setHue(options.color, cal.get(0));
						setSelector(options.color, cal.get(0));
						setCurrentColor(options.color, cal.get(0));
						setNewColor(options.color, cal.get(0));
						if (options.flat) {
							cal.css({
								position: 'relative',
								display: 'block'
							});
						} else {
							$(this).bind(options.eventName, show);
						}
					}
				});
			},
			showPicker: function() {
				return this.each( function () {
					if ($(this).data('colorpickerId')) {
						show.apply(this);
					}
				});
			},
			hidePicker: function() {
				return this.each( function () {
					if ($(this).data('colorpickerId')) {
						$('#' + $(this).data('colorpickerId')).hide();
					}
				});
			},
			setColor: function(col) {
				if (typeof col == 'string') {
					col = HexToHSB(col);
				} else if (col.r != undefined && col.g != undefined && col.b != undefined) {
					col = RGBToHSB(col);
				} else if (col.h != undefined && col.s != undefined && col.b != undefined) {
					col = fixHSB(col);
				} else {
					return this;
				}
				return this.each(function(){
					if ($(this).data('colorpickerId')) {
						var cal = $('#' + $(this).data('colorpickerId'));
						cal.data('colorpicker').color = col;
						cal.data('colorpicker').origColor = col;
						fillRGBFields(col, cal.get(0));
						fillHSBFields(col, cal.get(0));
						fillHexFields(col, cal.get(0));
						setHue(col, cal.get(0));
						setSelector(col, cal.get(0));
						setCurrentColor(col, cal.get(0));
						setNewColor(col, cal.get(0));
					}
				});
			}
		};
	}();
	$.fn.extend({
		ColorPicker: ColorPicker.init,
		ColorPickerHide: ColorPicker.hidePicker,
		ColorPickerShow: ColorPicker.showPicker,
		ColorPickerSetColor: ColorPicker.setColor
	});


///var/www/philharmonia.co.uk/js/18/concertsExtended.js
// GET - Concert Info Page
function displayConcertInfoPage(pageID,pageURL) {
	"use strict";
	
	concertInfoMergeMask(pageID);
	
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("dynamic_concert_" + pageID).innerHTML=xmlhttp.responseText;
			
			ga('set', 'page', 'concerts/' + pageURL);
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			displayConcertInfoPage(pageID,pageURL);
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/concerts.php?concert=" + pageID + "&location=info",true);
	xmlhttp.send();
}

// GET - Concert Booking Page
function displayConcertBookingPage(pageID,pageLocation,pageURL) {
	"use strict";
		
	// Merge Masks
	var outputDiv = '';
	if(pageLocation === 'individual') {
		concertBookingMergeMask(pageID);
		outputDiv = "booking_block_" + pageID;
	} else {
		concertInfoMergeMask(pageID);
		outputDiv = "dynamic_concert_" + pageID;
	}
	
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById(outputDiv).innerHTML=xmlhttp.responseText;
		} else if (xmlhttp.status===502) {
			displayConcertBookingPage(pageID,pageLocation,pageURL);
			
			ga('set', 'page', 'concerts/' + pageURL + '/book');
			ga('send', 'pageview'); 
			
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/concerts.php?concert=" + pageID + "&location=" + pageLocation,true);
	xmlhttp.send();
	
}

// GET - Concert Overview Block
function displayConcertOverviewBlock(pageID,pageURL) {
	"use strict";
	
	concertInfoMergeMask(pageID);
	
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElement
			ById("dynamic_concert_" + pageID).innerHTML=xmlhttp.responseText;
			
			ga('set', 'page', 'concerts/' + pageURL);
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			displayConcertOverviewBlock(pageID,pageURL);
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/concerts.php?concert=" + pageID + "&location=info",true);
	xmlhttp.send();
}

// MERGE - Info Mask
function concertInfoMergeMask(pageID) {
	"use strict";
	
	// DISPLAY - Only the selected concert & pre-loader
	$('.concert_list_block').animate({opacity: "0.2"});
	
	//$('#dynamic_concert_' + pageID).animate({opacity: "1"});
	document.getElementById("dynamic_concert_" + pageID).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	
}

// MERGE - Booking Mask
function concertBookingMergeMask(pageID) {
	"use strict";
	document.getElementById("booking_block_" + pageID).innerHTML = '<div class="whiteBlock"><img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"></div>';

}

// GET - Concert Single Listing
function displayConcertSingleListing(concertID,concertState) {
	"use strict";
	concertListingMerge(concertID);
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("dynamic_concert_" + concertID).innerHTML=xmlhttp.responseText;
		} else if (xmlhttp.status===502) {
			displayConcertSingleListing(concertID,concertState);
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/single_listing.php?concert=" + concertID + "&listing=single&state=" + concertState,true);
	xmlhttp.send();
	
	
}

// MERGE - Concert Listing (Single)
function concertListingMerge(concertID) {
	"use strict";
	// DISPLAY - Only the selected concert & pre-loader
	$('.concert_list_block').animate({opacity: "1"});
	document.getElementById("dynamic_concert_" + concertID).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
}

// DISPLAY - Booked Concert
function displayBookedConcert(concertID) {
	"use strict";
	$("#dynamic_concert_" + concertID).delay(1500).animate({opacity: "0.5"});
	$("#dynamic_concert_" + concertID).append('<p>Booked<p>');
	$("#basketBtnNo").css({display: 'block'});
	$("#basket_button").css({"padding-right": '30px'});
}

// GET - Concert Explore Page
function displayConcertExplorePage(pageID) {
	"use strict";
		
	concertInfoMergeMask(pageID);
	
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("dynamic_concert_" + pageID).innerHTML=xmlhttp.responseText;
			
		} else if (xmlhttp.status===502) {
			displayConcertInfoPage(pageID);
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/explore.php",true);
	xmlhttp.send();
}

// GET - Concert Explore Listing
function displayExploreListing(concertID) {
	"use strict";
	concertListingMerge(concertID);
	// GET - Target Div
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("dynamic_concert_" + concertID).innerHTML=xmlhttp.responseText;
		} else if (xmlhttp.status===502) {
			displayExploreListing(concertID);
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/single_listing_explore.php",true);
	xmlhttp.send();
}

///var/www/philharmonia.co.uk/js/18/login.js

function closeLoginModal() {
	$('#pageWhiteOut').remove();
	$('#login-modal').remove();
}
function displayPasswordTyped(pass) {
	$('#visible-password').val(pass);
}
function togglePasswordView() {
	$('#visible-password').animate({height: "toggle"});
	if($('#visible-password').height() >= 1) {
		$('#toggle-password-view').removeClass('fa-eye-slash');
		$('#toggle-password-view').addClass('fa-eye');
		$('#toggle-password-view').attr('title','Show Password');
		$('#toggle-password-view').attr('aria-title','Show Password');
		//document.getElementById('invisible-password').style.borderRadius = '5px 5px 5px 5px';
	} else {
		$('#toggle-password-view').removeClass('fa-eye');
		$('#toggle-password-view').addClass('fa-eye-slash');
		$('#toggle-password-view').attr('title','Hide Password');
		$('#toggle-password-view').attr('aria-title','Hide Password');
		//document.getElementById('invisible-password').style.borderRadius = '5px 5px 0px 0px';
	}
}

///var/www/philharmonia.co.uk/js/18/tessituraExtended.js
/**********************************************************
LOGIN (GENERAL)
**********************************************************/
// Load Page
function loginScreen() {
	"use strict";
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/login_screen.php",true);
	xmlhttp.send();
}
// Submit Form
function submitLogin(location) {
	"use strict";
	var data = $('#login-form').serializeArray();
	document.getElementById("login-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/login_process.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response) {
			responseLogin(response,location);
		}
	});
}
// Process Form
function responseLogin(customerID,location) {
	"use strict";
	var loginSet = 'login_screen';
	var loginSetExt = '.php?settings=failed_login';
	
	// LOGIN - Checkout Pathway
	if(location === 'checkoutPath' && customerID) {
		loadLoggedIn();
		$("#topBar_profile").css({display: 'inline-block'});
		$("#topBar_logout").css({display: 'inline-block'});
		$("#topBar_login").css({display: 'none'});
	
	// LOGIN - Standard
	} else {
		if(customerID) {
			loginSet = 'login_confirmed';
			loginSetExt = '.php';
			$("#topBar_profile").css({display: 'inline-block'});
			$("#topBar_logout").css({display: 'inline-block'});
			$("#topBar_login").css({display: 'none'});
		}
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState===4 && xmlhttp.status===200) {
				document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
			}
		};
		xmlhttp.open("GET","/lib/ajax/tessitura/" + loginSet + loginSetExt,true);
		xmlhttp.send();
		$("#login-wrapper").addClass(loginSet);
	}
}

/**********************************************************
FORGOTTEN PASSWORD (GENERAL)
**********************************************************/
// Load Page
function forgottenPassword() {
	"use strict";
	var email = $('#form-email').val();
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/login_forgotten.php?email=" + email,true);
	xmlhttp.send();
}
// Submit Form
function submitForgottenPassword(location) {
	"use strict";
	var data = $('#forgotten-password-form').serializeArray();
	document.getElementById("login-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/forgotten_password_process.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response,location) {
			responseForgottenPassword(response,location);
		}
	});
}
// Process Form
function responseForgottenPassword(msg,location) {
	"use strict";
	var forgottenPasswordSet = '';
	var forgottenPasswordSetExt = '';
	console.log(msg);
	if(location === 'checkoutPath' && msg === '1') {
			forgottenPasswordSet = 'login_checkout';
			forgottenPasswordSetExt = 'email_sent';
	} else if(msg === '1') {
			forgottenPasswordSet = 'login_screen';
			forgottenPasswordSetExt = 'email_sent';
	} else {
		forgottenPasswordSet = 'login_forgotten';
		forgottenPasswordSetExt = 'error';
	}
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/" + forgottenPasswordSet + ".php?settings="  + forgottenPasswordSetExt,true);
	xmlhttp.send();
}
/**********************************************************
LOGIN (CHECKOUT)
**********************************************************/
// Load Page
function loginCheckout() {
	"use strict";
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/login_checkout.php",true);
	xmlhttp.send();
}
/**********************************************************
FORGOTTEN PASSWORD (CHECKOUT)
**********************************************************/
// Load Page
function forgottenPasswordCheckout() {
	"use strict";
	var email = $('#form-email').val();
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/login_forgotten_checkout.php?email=" + email,true);
	xmlhttp.send();
}

/**********************************************************
LOGIN (STAFF)
**********************************************************/
// Load Page
function loginScreenStaff() {
	"use strict";
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-modal").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/admin/login_screen.php",true);
	xmlhttp.send();
}
/*
// Submit Form
function submitLogin() {
	"use strict";
	var data = $('#login-form').serializeArray();
	document.getElementById("login-modal").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/login_process.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response) {
			responseLogin(response);
		}
	});
}
// Process Form
function responseLogin(customerID) {
	"use strict";
	var loginSet = 'login_screen';
	var loginSetExt = '.php?settings=failed_login';
	if(customerID) {
		loginSet = 'login_confirmed';
		loginSetExt = '.php';
		$("#topBar_profile").css({display: 'inline-block'});
		$("#topBar_logout").css({display: 'inline-block'});
		$("#topBar_login").css({display: 'none'});
	}
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-modal").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/admin" + loginSet + loginSetExt,true);
	xmlhttp.send();
	$("#login-modal").addClass(loginSet);
}
*/

/**********************************************************
FORGOTTEN PASSWORD (STAFF)
**********************************************************/
/*
// Load Page
function forgottenPassword() {
	"use strict";
	var email = $('#form-email').val();
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-modal").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/admin/login_forgotten.php?email=" + email,true);
	xmlhttp.send();
}
// Submit Form
function submitForgottenPassword() {
	"use strict";
	var data = $('#forgotten-password-form').serializeArray();
	document.getElementById("login-modal").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/admin/forgotten_password_process.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response) {
			responseForgottenPassword(response);
		}
	});
}
// Process Form
function responseForgottenPassword(msg) {
	"use strict";
	var forgottenPasswordSet = 'login_forgotten';
	var forgottenPasswordSetExt = 'error';
	if(msg === 1) {
		forgottenPasswordSet = 'login_screen';
		forgottenPasswordSetExt = 'email_sent';
	}
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("login-modal").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/admin/" + forgottenPasswordSet + ".php?settings="  + forgottenPasswordSetExt,true);
	xmlhttp.send();
}
*/
/**********************************************************
BASKET LOADING
**********************************************************/
function goToBasket() {
	"use strict";
	document.getElementById("main").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
}

/**********************************************************
CONCERTS UPSELL
**********************************************************/
function membershipUpsell(valueOfMembership,levelOfMembership) {
	"use strict";
	//document.getElementById("priority-ticket_" + valueOfMembership).innerHTML = '<i class="fa fa-check"></i>';
	//$('.priority-action-button').attr({disabled: 'disabled'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("basketBtnNo").innerHTML=xmlhttp.responseText;
			$("#basketBtnNo").css({display: 'block'});
			$("#basket_button").css({"padding-right": '30px'});
			updateListings(54);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/upsell_friend.php?value=" + valueOfMembership + "&level=" + levelOfMembership,true);
	xmlhttp.send();
}
function updateListings(loginType) {
	"use strict";
	document.getElementById("listings-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">'; 
	
	// Remove Upsell if MOS 54
	if(loginType === 54) {
		//document.getElementById("priority-ticket_60").innerHTML = '<i class="fa fa-check"></i>';
		//$('.priority-action-button').attr({disabled: 'disabled'});
		$('#priority-message-bedford').css({display: 'none'});
		//$("#priority-message").animate({padding: '11px 11px 11px 40px', margin: '41px 40px 0px -40px', width: '120%'});
		//document.getElementById("priority-message").innerHTML = 'You now have access to priority booking.';
	}
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("listings-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/concerts/listings.php?season=80",true);
	xmlhttp.send();
}

/**********************************************************
ADD TO BASKET
**********************************************************/
// Add To Baket (Select Seats)
function addToBasket(concertID) {
	"use strict";
	var win = document.getElementById('seatSelectionProcessiFrame').contentWindow;
	var form = win.$('#add_seat_to_cart');
	var data = form.serializeArray();
	
	window.scrollBy(0, -650);
	document.getElementById("dynamic_concert_" + concertID).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/add_to_basket_selected.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response) {
			if(!isNaN(response)) {
				postBasket(concertID);
			}
		}
	});
}

// Add To Baket (Best Seats)
function addBestSeat(concertID) {
	"use strict";
	var outerIframe = document.getElementById('seatSelectionProcessiFrame');
	var outerIframeDocument = outerIframe.contentDocument || outerIframe.contentWindow.document;
	var innerIframe = outerIframeDocument.getElementById('baSeats_iframe');
	var innerIframeDocument = innerIframe.contentDocument || innerIframe.contentWindow.document;
	var form = innerIframeDocument.getElementById('bestAvailPost');
	var data = $(form).serializeArray();
	
	window.scrollBy(0, -350);
	parent.document.getElementById("dynamic_concert_" + concertID).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	
	$.ajax({
		type:'POST',
		url:'/lib/ajax/tessitura/add_to_basket_best.php',
		data:{
			method: 'POST',
			data: data
		},
		success:function(response) {
			console.log(response);
			if(!isNaN(response)) {
				postBasket(concertID);
			}
		}
	});
}

// Update Basket (Select Seats)
function postBasket(concertID) {
	"use strict";
	
	displayConcertSingleListing(concertID,'booked');
	$("#basketBtnNo").css({display: 'block'});
	$("#basket_button").css({"padding-right": '30px'});

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("basketBtnNo").innerHTML= xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/check_basket.php?var=" + concertID,true);
	xmlhttp.send();
}

/**********************************************************
EMPTY BASKET
**********************************************************/
function emptyBasket() {
	"use strict";
	document.getElementById("main").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("main").innerHTML=xmlhttp.responseText;
			$("#basketBtnNo").css({display: 'none'});
			$("#basket_button").css({"padding-right": '0px'});
			
			ga('send', 'event', 'action', 'empty_basket', 'checkout');
			
		} else if (xmlhttp.status===502) {
			emptyBasket();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/empty_basket.php",true);
	xmlhttp.send();
}

/**********************************************************
REMOVE ITEM
**********************************************************/
function removeItem(perf_details,line_nos) {
	"use strict";
	var perf_details_copy = perf_details;
	var line_nos_copy = line_nos;
	var performance_details = perf_details.split("_");
	var performance_number = parseInt(performance_details[0]);
	var item_number = parseInt(performance_details[1]);
	var subline_item = parseInt(performance_details[2]);
	var line_numbers = line_nos.split("_");
	var current_line = parseInt(line_numbers[0]);
	var  total_lines = parseInt(line_numbers[1]);
	
	document.getElementById("item_" + item_number).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('.remove-item').animate({opacity: '0'});
	$('.remove-item').css({display: 'none'});
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			if(xmlhttp.responseText > 0) {
				document.getElementById("basketBtnNo").innerHTML=xmlhttp.responseText;
				$('.remove-item').animate({opacity: '0.5'});
				$('.remove-item').css({display: 'block'});
			} else {
				$("#basket_button").css({"padding-right": '0px'});
				$("#basketBtnNo").css({display: 'none'});
				document.getElementById("main").innerHTML="<p><em>Your basket is currently empty.</em></p>";
			}
			$("#item_" + item_number).css({display: 'none'});
						
			if(current_line <= 4) {
				$('#5_' + total_lines).parent('.checkout-line-items').children('.ticket-type').children('form').children('select').prop('disabled', false);
			}
			
			ga('send', 'event', 'action', 'remove_ticket', 'checkout');
			
			updateBasket();
						
		} else if (xmlhttp.status===502) {
			removeItem(perf_details_copy,line_nos_copy);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/remove_item.php?perf_no=" + performance_number + "&sli_no=" + item_number + "&li_seq_no=" + subline_item,true);
	xmlhttp.send();
}

/**********************************************************
REMOVE CONTRIBUTION
**********************************************************/
function removeContribution(ref_no,checkout_check) {
	"use strict";
	var contributionReference = ref_no;
	var emptyCheckout = checkout_check;
	
	document.getElementById("item_" + contributionReference).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('#contribution_details_' + contributionReference).animate({height: 0, padding: 0});
	$('#contribution_details_' + contributionReference).css({'border': '0px'});
	$('.remove-item').animate({opacity: '0'});
	$('.remove-item').css({display: 'none'});
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			if(xmlhttp.responseText > 0) {
				document.getElementById("basketBtnNo").innerHTML=xmlhttp.responseText;
				$('.remove-item').animate({opacity: '0.5'});
				$('.remove-item').css({display: 'block'});
			} else {
				$("#basket_button").css({"padding-right": '0px'});
				$("#basketBtnNo").css({display: 'none'});
				document.getElementById("main").innerHTML="<p><em>Your basket is currently empty.</em></p>";
			}
			$("#item_" + contributionReference).css({display: 'none'});
			
			// Empty Basket (if customer removes load bearing contribution from basket)
			if(emptyCheckout === 'remove_all') {
				emptyBasket();
				ga('send', 'event', 'action', 'remove_priority_contribution', 'checkout');
			} else {
				updateBasket();
				ga('send', 'event', 'action', 'remove_standard_contribution', 'checkout');
			}
			
		} else if (xmlhttp.status===502) {
			removeContribution(contributionReference);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/remove_contribution.php?ref_no=" + contributionReference,true);
	xmlhttp.send();
	
}

/**********************************************************
CONTRIBUTION WARNING
**********************************************************/
function contributionWarning(ref_no) {
	"use strict";
	var confirmation = confirm("If you remove this membership from your basket you will lose access to priority booking and all tickets in your basket.");
	if(confirmation === true) {
		removeContribution(ref_no,'remove_all');
	}
}

/**********************************************************
UPDATE BASKET
**********************************************************/
function updateBasket() {
	"use strict";
	document.getElementById("checkout-basket-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center">Updating Basket</h3>';
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("checkout-basket-wrapper").innerHTML=xmlhttp.responseText;
			if(document.getElementById('checkout-your-details-wrapper').clientHeight > 0) {
				checkDelivery();
			}
			if(document.getElementById('checkout-delivery-wrapper').clientHeight > 0) {
				reloadDeliveryOptions();
			}
		}  else if (xmlhttp.status===502) {
			updateBasket();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/basket.php?basket=refresh",true);
	xmlhttp.send();
}

/**********************************************************
LOAD BASKET
**********************************************************/
function loadBasket() {
	"use strict";
	document.getElementById("checkout-basket-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('#checkout-basket-wrapper').css({display: 'block', height: 'auto', border: '1px solid #DDD', margin: '-30px 0 15px', padding: '40px 20px 20px'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#basket-state').removeClass('fa-caret-down');
			$('#basket-state').addClass('fa-caret-up');
			document.getElementById('basket-state-message').innerHTML= 'Click here to minimise the basket.';
			document.getElementById("checkout-basket-wrapper").innerHTML=xmlhttp.response;
			document.getElementById('checkout-basket-tab').setAttribute('onclick', 'hideBasket();');
		} else if (xmlhttp.status===502) {
			loadBasket();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/basket.php?basket=refresh",true);
	xmlhttp.send();
}

/**********************************************************
QUICK LOAD BASKET
Once the basket has been loaded once it can simply be displayed again without
another call needing to be made to the server as nothing will have changed to the basket
since last time.
**********************************************************/
function quickLoadBasket() {
	"use strict";
	$('#checkout-basket-wrapper').css({height: 'auto', padding: '40px 20px 20px'});
	$('#basket-state').removeClass('fa-caret-down');
	$('#basket-state').addClass('fa-caret-up');
	document.getElementById('basket-state-message').innerHTML= 'Click here to minimise the basket.';
	document.getElementById('checkout-basket-tab').setAttribute('onclick', 'hideBasket();');
}

/**********************************************************
HIDE BASKET
**********************************************************/
function hideBasket() {
	"use strict";
	$('#checkout-basket-wrapper').css({height: 0, padding: 0});
	$('#basket-state').removeClass('fa-caret-up');
	$('#basket-state').addClass('fa-caret-down');
	document.getElementById('basket-state-message').innerHTML= 'Click here if you would like to review your basket.';
	document.getElementById('checkout-basket-tab').setAttribute('onclick', 'quickLoadBasket();');
}

/**********************************************************
UPDATE YOUR DETAILS
**********************************************************/
function updateYourDetails() {
	"use strict";
	saveYourDetails();
	document.getElementById("checkout-your-details-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center">Updating Your Details</h3>';
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("checkout-your-details-wrapper").innerHTML=xmlhttp.responseText;
		}  else if (xmlhttp.status===502) {
			updateYourDetails();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/your_details.php?checkout=refresh&reload=1",true);
	xmlhttp.send();
}

/**********************************************************
UPDATE TICKET TYPE
**********************************************************/
function updateTicketType(ticketDetails,newPriceType) {
	"use strict";
	var ticketDetailsSplit = ticketDetails.split("_");
	var ticket = ticketDetailsSplit[0];
	var oldPriceType = ticketDetailsSplit[1];
	document.getElementById("price_" + ticket).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.responseType = "json";
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			if(xmlhttp.response.line_price === 'NULL') {
				document.getElementById("price_" + ticket).innerHTML= 'ERROR';
			} else {
				document.getElementById("price_" + ticket).innerHTML=xmlhttp.response.line_price;
				document.getElementById("subtotal-price").innerHTML=xmlhttp.response.subtotal_price;
				document.getElementById("discount-price").innerHTML= '<h4><span>DISCOUNT</span> <strong>&pound;' + xmlhttp.response.discount_price +'</strong></h4>';
				document.getElementById("total-price").innerHTML= xmlhttp.response.total_price;
				if(xmlhttp.response.discount_price === '0.00') {
					$('#discount-price').css({display: 'none'});
				} else {
					$('#discount-price').css({display: 'block'});
				}
				$('#' + ticketDetails).attr('id',ticket + '_' + newPriceType);
			}
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/update_price_type.php?ticket=" + ticket + "&new_price=" + newPriceType + "&old_price=" + oldPriceType,true);
	xmlhttp.send();
}

/**********************************************************
LOAD LOGIN
**********************************************************/
function loadLoginPage() {
	"use strict";
	document.getElementById("checkout-basket-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#checkout-basket-wrapper').animate({height: 0});
			document.getElementById("checkout-basket-wrapper").innerHTML = '';
			$('#checkout-basket-tab').css({display: 'block'});
			$('.checkout-message').animate({height: 0, margin: 0, padding: 0, border: 0});
			document.getElementById('checkout-location').innerHTML = 'Login';
			document.getElementById("checkout-login-wrapper").innerHTML=xmlhttp.response;
			
			ga('set', 'page', 'checkout?s=1');
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			loadLoginPage();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/login_checkout.php",true);
	xmlhttp.send();
}

/**********************************************************
LOAD LOGGED IN
**********************************************************/
function loadLoggedIn() {
	"use strict";
	document.getElementById("checkout-login-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('#checkout-login-wrapper').animate({height: 0});
	document.getElementById("checkout-login-wrapper").innerHTML = '';
	$('#checkout-login-tab').css({display: 'block'});
	document.getElementById("checkout-your-details-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	loadYourDetails();
	
}

/**********************************************************
LOAD YOUR DETAILS
**********************************************************/
function loadYourDetails() {
	"use strict";
	document.getElementById("checkout-basket-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			checkDelivery();
			$('#checkout-basket-wrapper').animate({height: 0, margin: 0});
			document.getElementById("checkout-basket-wrapper").innerHTML = '';
			$('#checkout-basket-tab').css({display: 'block'});
			$('#checkout-login-tab').css({display: 'block'});
			$('.checkout-message').animate({height: 0, margin: 0, padding: 0, border: 0});
			document.getElementById('checkout-location').innerHTML = 'Your Details';
			$('#checkout-your-details-wrapper').animate({padding: '20px'});
			document.getElementById("checkout-your-details-wrapper").innerHTML=xmlhttp.response;
			
			var contactPermissionsBlock = document.createElement('div');
			contactPermissionsBlock.id = 'contact-permissions-wrapper';
			document.getElementById('update_profile').appendChild(contactPermissionsBlock);
			
				setTimeout(function() {
					loadDataProtection();
				}, 1000);
			
			ga('set', 'page', 'checkout?s=2');
			ga('send', 'pageview');
					
		} else if (xmlhttp.status===502) {
			loadYourDetails();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/your_details.php?checkout=refresh",true);
	xmlhttp.send();	
}

/**********************************************************
LOAD DATA PROTECTION
**********************************************************/
function loadDataProtection() {
	"use strict";
		document.getElementById("contact-permissions-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
			
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState===4 && xmlhttp.status===200) {
				document.getElementById("contact-permissions-wrapper").innerHTML=xmlhttp.responseText;
			}
		};
		xmlhttp.open("GET","/lib/ajax/tessitura/contact_permissions.php?po_email=true&sbc=true&bce=true&inline_update=false",true);
		xmlhttp.send();
}

/**********************************************************
RELOAD YOUR DETAILS
**********************************************************/
function reloadYourDetails() {
	"use strict";
	document.getElementById("checkout-your-details-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('#checkout-your-details-wrapper').css({display: 'block', height: 'auto', border: '1px solid #DDD', margin: '-15px 0 15px', padding: '20px'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#details-state').removeClass('fa-caret-down');
			$('#details-state').addClass('fa-caret-up');
			document.getElementById('details-state-message').innerHTML= 'Click here to minimise your details.';
			document.getElementById("checkout-your-details-wrapper").innerHTML=xmlhttp.response;
			document.getElementById('checkout-your-details-tab').setAttribute('onclick', 'hideYourDetails();');
			
			var contactPermissionsBlock = document.createElement('div');
			contactPermissionsBlock.id = 'contact-permissions-wrapper';
			document.getElementById('update_profile').appendChild(contactPermissionsBlock);
			
			setTimeout(function() {
				loadDataProtection();
			}, 1000);

		} else if (xmlhttp.status===502) {
			reloadYourDetails();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/your_details.php?checkout=refresh&reload=1",true);
	xmlhttp.send();
	
}

/**********************************************************
QUICK LOAD YOUR DETAILS
**********************************************************/
function quickLoadYourDetails() {
	"use strict";
	$('#checkout-your-details-wrapper').css({height: 'auto', padding: '20px'});
	$('#details-state').removeClass('fa-caret-down');
	$('#details-state').addClass('fa-caret-up');
	document.getElementById('details-state-message').innerHTML= 'Click here to minimise your details.';
	document.getElementById('checkout-your-details-tab').setAttribute('onclick', 'hideYourDetails();');
}

/**********************************************************
HIDE YOUR DETAILS
**********************************************************/
function hideYourDetails() {
	"use strict";
	$('#checkout-your-details-wrapper').css({height: 0, padding: 0});
	$('#details-state').removeClass('fa-caret-up');
	$('#details-state').addClass('fa-caret-down');
	document.getElementById('details-state-message').innerHTML= 'Click here if you would like to review your details.';
	document.getElementById('checkout-your-details-tab').setAttribute('onclick', 'quickLoadYourDetails();');
}

/**********************************************************
CHECK DELIVERY
Checks whether the order should have a delivery options step or not. Also
checks if the order value is 0, if so switch to the freeOrderProcess to avoid WorldPay but allow the customer
to confirm their order.
**********************************************************/
function checkDelivery() {
	"use strict";
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			var optionsReturned = JSON.parse(xmlhttp.response);
			var deliveryOptions = optionsReturned.delivery;
			var orderTotal = optionsReturned.total;
			if(deliveryOptions === 'no') {
				if(orderTotal === 0) {
					document.getElementById('checkout-primary-bottom').innerHTML = 'PLACE ORDER <i class="fa fa-angle-right left-margin"></i>';
					document.getElementById('checkout-primary-bottom').setAttribute('onclick', 'freeOrderProcess();');
				} else {
					document.getElementById('checkout-primary-bottom').innerHTML = 'PAY NOW <i class="fa fa-angle-right left-margin"></i>';
					document.getElementById('checkout-primary-bottom').setAttribute('onclick', 'checkoutProcess();');
				}
			} else {
				document.getElementById('checkout-primary-bottom').innerHTML = 'NEXT <i class="fa fa-angle-right left-margin"></i>';
				document.getElementById('checkout-primary-bottom').setAttribute('onclick', 'loadDeliveryOptions();');
			}
			$('#checkout-primary-bottom').css({display: 'inline-block'});
			
		} else if (xmlhttp.status===502) {
			checkDelivery();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/check_delivery.php",true);
	xmlhttp.send();
}

/**********************************************************
LOAD DELIVERY OPTIONS
**********************************************************/
function loadDeliveryOptions() {
	"use strict";
	saveYourDetails();
	document.getElementById("checkout-your-details-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#checkout-your-details-wrapper').animate({height: 0, margin: 0, padding: 0});
			document.getElementById("checkout-your-details-wrapper").innerHTML = '';
			$('#checkout-your-details-tab').css({display: 'block'});
			document.getElementById('checkout-location').innerHTML = 'Ticket Options';
			$('#checkout-delivery-wrapper').animate({padding: '20px'});
			document.getElementById("checkout-delivery-wrapper").innerHTML=xmlhttp.response;
			document.getElementById('checkout-primary-bottom').innerHTML = 'PAY NOW <i class="fa fa-angle-right left-margin"></i>';
			document.getElementById('checkout-primary-bottom').setAttribute('onclick', 'checkoutProcess();');
			$('#checkout-primary-bottom').css({display: 'inline-block'});
			$('#checkout-your-details-wrapper').css({boder: '0px'});
			
			ga('set', 'page', 'checkout?s=3');
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			loadDeliveryOptions();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/delivery_options.php?checkout=refresh",true);
	xmlhttp.send();
}

/**********************************************************
RELOAD DELIVERY OPTIONS
**********************************************************/
function reloadDeliveryOptions() {
	"use strict";
	document.getElementById("checkout-delivery-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one">';
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#checkout-delivery-wrapper').animate({padding: '20px'});
			document.getElementById("checkout-delivery-wrapper").innerHTML=xmlhttp.response;
			document.getElementById('checkout-primary-bottom').innerHTML = 'PAY NOW <i class="fa fa-angle-right left-margin"></i>';
			document.getElementById('checkout-primary-bottom').setAttribute('onclick', 'checkoutProcess();');
			$('#checkout-primary-bottom').css({display: 'inline-block'});
		} else if (xmlhttp.status===502) {
			reloadDeliveryOptions();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/delivery_options.php?checkout=refresh",true);
	xmlhttp.send();
}

/**********************************************************
SAVE YOUR DETAILS
**********************************************************/
function saveYourDetails() {
	"use strict";
	
	// SERIALIZE FORM
	var o = '';
	o = {};
	var a = $('#update_profile').serializeArray();
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	
	if(o) {
		var data = o;

		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/update_your_details.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				//console.log(response);
			}
		});
	}
	if(document.getElementById('checkout-delivery-wrapper').clientHeight > 0) {
		reloadDeliveryOptions();
	}
}

/**********************************************************
UPDATE TICKET METHOD
**********************************************************/
function updateTicketMethod(shippingMethod) {
	"use strict";
	$('#collection-option-wrapper').css({opacity: 0.2});
	$('#delivery-option-wrapper').css({opacity: 0.2});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			$('#collection-option-wrapper').animate({opacity: 1});
			$('#delivery-option-wrapper').animate({opacity: 1});
			if(shippingMethod === '9') {
				$('#collection-option-wrapper').addClass('selected-option');
				$('#delivery-option-wrapper').removeClass('selected-option');
				$('#collection-option-address').css({display: 'none'});
			} else if(shippingMethod === '8') {
				$('#delivery-option-wrapper').addClass('selected-option');
				$('#collection-option-wrapper').removeClass('selected-option');
				$('#collection-option-address').css({display: 'block'});
			} else {
				alert('Please choose one of the two options available');
			}
		} else if (xmlhttp.status===502) {
			updateTicketMethod();
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/update_delivery_option.php?delivery_option=" + shippingMethod,true);
	xmlhttp.send();
	
}
/**********************************************************
PAYMENT GATEWAY
**********************************************************/
function paymentGateway() {
	"use strict";
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("checkout-grid").innerHTML=xmlhttp.response;
			document.getElementById('checkoutForm').submit();
		} else if (xmlhttp.status===502) {
			alert('Sorry, something went wrong. Try refreshing this page to continue');
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/checkout_form.php",true);
	xmlhttp.send();
}

/**********************************************************
CHECKOUT PROCESS
**********************************************************/
function checkoutProcess() {
	"use strict";
	saveYourDetails();
	document.getElementById("checkout-grid").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center">Transferring to WorldPay for Payment</h3>';
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			paymentGateway();
			
			ga('set', 'page', 'checkout?s=4');
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			alert('Sorry, something went wrong. Try refreshing this page to continue');
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/checkout_output.php",true);
	xmlhttp.send();
}

/**********************************************************
FREE BUT TICKETED
**********************************************************/
function freeOrderProcess() {
	"use strict";
	document.getElementById("checkout-grid").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center">Confirming Transacation</h3>';
	$('.checkout-button.cta').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("checkout-grid").innerHTML=xmlhttp.response;
			document.getElementById('checkoutFreeForm').submit();
			
			ga('set', 'page', 'checkout?s=4');
			ga('send', 'pageview');
			
		} else if (xmlhttp.status===502) {
			alert('Sorry, something went wrong. Try refreshing this page to continue');
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/checkout_free.php",true);
	xmlhttp.send();
}
/**********************************************************
PROMO CODES
**********************************************************/
function applyPromoCode() {
	"use strict";
	var promoCode = document.getElementById('promo_code_value').value;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			if(xmlhttp.response === '1') {
				
				ga('send', 'event', 'action', 'promo_code_used', 'checkout');
				
				updateBasket();
				alert('Promotion Applied');
			} else {
				alert('Sorry, that code is not recognised');
			}
		} else if (xmlhttp.status===502) {
			alert('Sorry, something went wrong. Try refreshing this page to continue');
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/add_promo_code.php?promo_code=" + promoCode,true);
	xmlhttp.send();
}
/**********************************************************
ROUND UP DONATIONS
**********************************************************/
function applyDonation() {
	"use strict";
	var donationValue = document.getElementById('donation_value').value;
	$('#donation-button-apply').css({display: 'none'});
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			
			ga('send', 'event', 'action', 'round_up_donation_added', 'checkout');
			
			updateBasket();
			alert('Thank you, donation has been added to your basket');
			document.getElementById("basketBtnNo").innerHTML=xmlhttp.responseText;
			
		} else if (xmlhttp.status===502) {
			alert('Sorry, something went wrong. Try refreshing this page to continue');
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/add_donation.php?donation_value=" + donationValue,true);
	xmlhttp.send();
}
/**********************************************************
FRIEND PROMO OFFER
New promotional offer that allows a package of a Friend
membership and tickets to be added in one click.
**********************************************************/
function checkFriendPromo() {
	"use strict";
	var level = $("#membership_offer").val();
	var seatCount = $("#seat_count").val();
	
	// COVER MESSING AROUND
	if(level < 1 || level > 4) {
		alert('Sorry there is a problem with your selection. Please only choose the options provided.');
	} else if (seatCount < 1 || seatCount > 10) {
		alert('Sorry there is a problem with your selection. Please only choose the options provided.');
		
	// PROCEED
	} else {
		var data = $('#best_seat_dev').serializeArray();
		$("#bestSeatAdd").css({display: 'none'});
		document.getElementById("best_seat_dev").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Building Package</h3>';
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/friends_package.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				$("#basketBtnNo").css({display: 'block'});
				$("#basket_button").css({"padding-right": '30px'});
				document.getElementById("basketBtnNo").innerHTML= response;
				window.location.replace("/checkout");
			}
		});
	}
}

/**********************************************************
UPDATE CONTACT PERMISSIONS
**********************************************************/
function updateContactPermissions(PO,SBC,BCE,INLINE) {
	"use strict";
	event.preventDefault();
	var o = '';
	o = {};
	var a = $('#dp-form').serializeArray();
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	document.getElementById("dp-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Updating Data Protection</h3>';
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/update_contact_permissions.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				$("#contact-permissions-wrapper").load("/lib/ajax/tessitura/contact_permissions.php?update=true&po_email=" + PO + "&sbc=" + SBC + "&bce=" + BCE + "&inline_update=" + INLINE);
			}
		});
	}
}
/**********************************************************
UPDATE INTERESTS
**********************************************************/
function updateInterests() {
	"use strict";
	event.preventDefault();
	var o = '';
	o = {};
	var a = $('#interests-form').serializeArray();
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	document.getElementById("interests-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Updating Interests</h3>';
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/update_interests.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				$("#interests-wrapper").load("/lib/ajax/tessitura/interests.php?update=true");
			}
		});
	}
}
/**********************************************************
UPDATE SUBSCRIPTION
**********************************************************/
function updateSubscription() {
	"use strict";
	event.preventDefault();
	var o = '';
	o = {};
	var a = $('#signupForm').serializeArray();
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	document.getElementById("subscription-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Creating Subscription</h3>';
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/update_subscription.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				$("#subscription-wrapper").load("/lib/ajax/tessitura/subscription.php?update=true");
			}
		});
	}
}

/**********************************************************
LOAD DATA PROTECTION PROJECTS
**********************************************************/
function loadDataProtectionProjects() {
	"use strict";
	var contactPermissionsBlock = document.createElement('div');
	contactPermissionsBlock.id = 'contact-permissions-wrapper';
	document.getElementById('data-protection-block').appendChild(contactPermissionsBlock);

	document.getElementById("contact-permissions-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Loading Data Protection</h3>';

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("contact-permissions-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/contact_permissions.php?po_email=true&sbc=false&bce=false&inline_update=false",true);
	xmlhttp.send();
}

/**********************************************************
LOAD INTERESTS
**********************************************************/
function loadInterests() {
	"use strict";
	var interestsBlock = document.createElement('div');
	interestsBlock.id = 'interests-wrapper';
	document.getElementById('interests-block').appendChild(interestsBlock);
	document.getElementById("interests-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Loading Interests</h3>';

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("interests-wrapper").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","/lib/ajax/tessitura/manage_interests.php",true);
	xmlhttp.send();
}

/**********************************************************
UPDATE PREFERENCES
**********************************************************/
function updatePreferences() {
	"use strict";
	var o = '';
	o = {};
	var a = $('#signupForm').serializeArray();
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	document.getElementById("manage-preferences-wrapper").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align: center">Updating Preferences</h3>';
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tessitura/update_preferences.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				$("#manage-preferences-wrapper").load("/lib/ajax/tessitura/update_preferences.php");
			}
		});
	}
}

///var/www/philharmonia.co.uk/js/18/tvo.js
/**********************************************************
TVO BOOKINGS
**********************************************************/

/**********************************************************
PAGES - SWITCH VIEW
**********************************************************/
function switchViewTVO(pageName) {
	"use strict";

	document.getElementById("tvo-content-wrap").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading</h3>';
	
	$('.vo-scta').removeClass('vo-active');
	$('#link_' + pageName).addClass('vo-active');
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("tvo-content-wrap").innerHTML=xmlhttp.responseText;
		} else  if (xmlhttp.status===502) {
			switchViewTVO(pageName);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tvo/" + pageName + ".php",true);
	xmlhttp.send();		
}

/**********************************************************
LISTINGS - MIN & MAX
**********************************************************/
function listingMinMax(listingID,listingName) {
	"use strict";
	
	$('#content_' + listingID).animate({ height: 'toggle'});
	
		if ($('#content_' + listingID).height() <= 1) {
			$('#arrow_' + listingID).removeClass('fa-angle-right');
			$('#arrow_' + listingID).addClass('fa-angle-down');
			$('#tvo-booking-listings-wrapper-' + listingID).css({ display: 'block'});
			
			document.getElementById("tvo-booking-listings-wrapper-" + listingID).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading Workshops</h3>';
						
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState===4 && xmlhttp.status===200) {
					document.getElementById("tvo-booking-listings-wrapper-" + listingID).innerHTML=xmlhttp.responseText;
					smoothLoad(listingName,0);
				} else  if (xmlhttp.status===502 || xmlhttp.status===520) {
					listingMinMax(listingID,listingName);
				}
			};
			xmlhttp.open("GET","/lib/ajax/tvo/listings.php?listing_id=" + listingID + "&listing_region=" + listingName,true);
			xmlhttp.send();
			
		} else {
			$('#arrow_' + listingID).removeClass('fa-angle-down');
			$('#arrow_' + listingID).addClass('fa-angle-right');
			$('#tvo-booking-listings-wrapper-' + listingID).css({ display: 'none'});
		}
}

/**********************************************************
LISTINGS - EXTEND
When you click the more button this function runs
**********************************************************/
function listingExtend(listingID,listingName,listingNewPosition) {
	"use strict";
	
	$('#tvo-more-' + listingName + '-' + listingNewPosition).animate({ height: 0, border: 0},20);
	$('#tvo-extended-' + listingName + '-' + listingNewPosition).animate({ opacity: 1},700);
	
	document.getElementById("tvo-extended-" + listingName + "-" + listingNewPosition).innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading</h3>';
	
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("tvo-extended-" + listingName + "-" + listingNewPosition).innerHTML=xmlhttp.responseText;
			smoothLoad(listingName,listingNewPosition);
		} else  if (xmlhttp.status===502 || xmlhttp.status===520) {
			listingExtend(listingID,listingName,listingNewPosition);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tvo/listings.php?listing_id=" + listingID + "&listing_region=" + listingName + "&listing_start=" + listingNewPosition,true);
	xmlhttp.send();
	
}

/**********************************************************
LISTINGS - SMOOTH LOAD
**********************************************************/
function smoothLoad(listingName,listingNewPosition) {
	"use strict";
	$.each( $('.tvo-listing-' + listingName + '-' + listingNewPosition), function(i){
		$(this).stop(true, true).delay(i * 250).fadeTo(500, 1, function(){
		});
	});
}

/**********************************************************
BOOKING - Type
**********************************************************/
function bookingType(typeSelected) {
	"use strict";
	
	document.getElementById("tvo-booking-wrap").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading Workshops</h3>';
	
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("tvo-booking-wrap").innerHTML=xmlhttp.responseText;
			$('.vo-cta').removeClass('vo-active-2');
			$('#' + typeSelected).addClass('vo-active-2');
		} else if (xmlhttp.status===502) {
			bookingType(typeSelected);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tvo/booking_wrap.php?booking_type=" + typeSelected,true);
	xmlhttp.send();
}

/**********************************************************
BOOKING - Form
**********************************************************/
function bookingForm(tessitura,booking) {
	"use strict";
	
	var tessituraID = tessitura;
	var bookingID = booking;

	document.getElementById("tvo-content").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading</h3>';
	
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			setTimeout(function() {
					loadDataProtectionProjects();
				}, 1000);
			document.getElementById("tvo-content").innerHTML=xmlhttp.responseText;
		} else if (xmlhttp.status===502 || xmlhttp.status===404) {
			bookingForm(tessituraID,bookingID);
		}
	};
	xmlhttp.open("GET","/lib/ajax/tvo/booking_form.php?tessitura_id=" + tessituraID + "&booking_id=" + bookingID,true);
	xmlhttp.send();
		
	$("html, body").animate({ scrollTop: "150px" }, 500);
    return false;
	
}

/**********************************************************
BOOKING - Update Input
Check the value is not blank in the required fields.
Also add additional checks for special inputs such as number limits
**********************************************************/
function updateInput(inputID) {
	"use strict";
	
	if(document.getElementById(inputID).value === '') {
		$('#input-wrap-' + inputID).css({border: "1px solid #DF007A", padding: "10px"});
		$('#input-label-' + inputID).css({display: "block"});
		document.getElementById('input-label-' + inputID).innerHTML = "This field is required";
	} else {
		$('#input-wrap-' + inputID).css({border: "0px"});
		$('#input-label-' + inputID).css({display: "none"});
	}
	
	// IF - Number of participants is too high or low, clear the value
	if(document.getElementById('number-of-participants').value < 1 || document.getElementById('number-of-participants').value > 35) {
		document.getElementById('number-of-participants').value = "";
		$('#input-wrap-number-of-participants').css({border: "1px solid #DF007A", padding: "10px"});
		$('#input-label-number-of-participants').css({display: "block"});
		document.getElementById('input-label-number-of-participants').innerHTML = "Please enter a number between 1 and 35";
	}
	
	// IF - Recorded Media Content has an option selected or not
	if(document.getElementById('rmc_yes').checked || document.getElementById('rmc_no').checked) {
		$('#input-wrap-rmc').css({border: "0px"});
		$('#input-label-rmc').css({display: "none"});
	} else {
		$('#input-wrap-rmc').css({border: "1px solid #DF007A", padding: "10px"});
		$('#input-label-rmc').css({display: "block"});
	}
	
}

/**********************************************************
BOOKING - Step
**********************************************************/
function bookingStep(stepID,bookingType) {
	"use strict";
	
	// PREVENT DEFAULT - Avoid the form reloading the page
	event.preventDefault();
		
	// VALIDATE - Contact Details
	if(stepID === 2) {
		if(document.getElementById('first-name').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-first-name').offset().top -100 }, 'slow');
			$('#input-wrap-first-name').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-first-name').css({display: "block"});
			return false;
		}
		if(document.getElementById('last-name').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-last-name').offset().top -100 }, 'slow');
			$('#input-wrap-last-name').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-last-name').css({display: "block"});
			return false;
		}
		if(document.getElementById('email-address').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-email-address').offset().top -100 }, 'slow');
			$('#input-wrap-email-address').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-email-address').css({display: "block"});
			return false;
		}
		if(document.getElementById('phone').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-phone').offset().top -100 }, 'slow');
			$('#input-wrap-phone').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-phone').css({display: "block"});
			return false;
		}
		if(document.getElementById('address-1').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-address-1').offset().top -100 }, 'slow');
			$('#input-wrap-address-1').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-address-1').css({display: "block"});
			return false;
		}
		if(document.getElementById('city').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-city').offset().top -100 }, 'slow');
			$('#input-wrap-city').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-city').css({display: "block"});
			return false;
		}
		if(document.getElementById('county').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-country').offset().top -100 }, 'slow');
			$('#input-wrap-country').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-country').css({display: "block"});
			return false;
		}
		if(document.getElementById('postcode').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-postcode').offset().top -100 }, 'slow');
			$('#input-wrap-postcode').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-postcode').css({display: "block"});
			return false;
		}
		if(document.getElementById('postcode-valid').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-postcode').offset().top -100 }, 'slow');
			$('#input-wrap-postcode').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-postcode').css({display: "block"});
			return false;
		}
		if(bookingType === 'school' || bookingType === 'community') {
			if(document.getElementById('organisation').value === '') {
				$("html, body").animate({scrollTop: $('#input-wrap-organisation').offset().top -100 }, 'slow');
				$('#input-wrap-organisation').css({"border-bottom": "1px solid #DF007A"});
				$('#input-label-organisation').css({display: "block"});
				return false;
			}
		}
	}
	
	// VALIDATE - Activity
	if(stepID === 1 || stepID === 3) {
		if(document.getElementById('number-of-participants').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-number-of-participants').offset().top -100 }, 'slow');
			$('#input-wrap-number-of-participants').css({border: "1px solid #DF007A", padding: "10px"});
			$('#input-label-number-of-participants').css({display: "block"});
			return false;
		}
		if(document.getElementById('rmc_yes').checked || document.getElementById('rmc_no').checked) {
		} else {
			$('#input-wrap-rmc').css({"border-bottom": "1px solid #DF007A"});
			$('#input-label-rmc').css({display: "block"});
			return false;
		}
	}	
	
	$('.step-title').removeClass('active');
	$('#tvo-step-' + stepID + '-title').addClass('active');
	
	// REVIEW STEP
	if(stepID === 3) {
		$('.tvo-step-wrap').css({display: "block"});
		$('.vo-form-button-next').css({display: "none"});
		$('#tvo-step-2-wrap').css({margin: "20px 0 0"});
		//$('input').attr("disabled", true);
		//$('select').attr("disabled", true);
		//$('textarea').attr("disabled", true);
		$('#tvo-review-header').css({display: "block"});
		
	// ALL OTHER STEPS
	} else {
		$('.tvo-step-wrap').css({display: "none"});
		$('#tvo-step-' + stepID + '-wrap').animate({height: "toggle"});
	}
		
	$("html, body").animate({ scrollTop: '500px' }, 500);
	return false;
}

/**********************************************************
BOOKING - Format Input
Format all input fields to clean the formatting of words, capitalize and remove other
unintended sized characters.
**********************************************************/
function formatInput(inputID) {
  "use strict";
	var outputText = document.getElementById(inputID).value.toLowerCase();
	outputText = outputText.toLowerCase().replace(/\b[a-z]/g, function(letter) {
		return letter.toUpperCase();
	});
	document.getElementById(inputID).value = outputText;
}

/**********************************************************
BOOKING - Postcode Check
**********************************************************/
function checkPostCode(toCheck) {
  "use strict";
		
  // Permitted letters depend upon their position in the postcode.
  var alpha1 = "[abcdefghijklmnoprstuwyz]";                       // Character 1
  var alpha2 = "[abcdefghklmnopqrstuvwxy]";                       // Character 2
  var alpha3 = "[abcdefghjkpmnrstuvwxy]";                         // Character 3
  var alpha4 = "[abehmnprvwxy]";                                  // Character 4
  var alpha5 = "[abdefghjlnpqrstuwxyz]";                          // Character 5
  var BFPOa5 = "[abdefghjlnpqrst]";                               // BFPO alpha5
  var BFPOa6 = "[abdefghjlnpqrstuwzyz]";                          // BFPO alpha6
  
  // Array holds the regular expressions for the valid postcodes
  var pcexp = new Array ();
  
  // BFPO postcodes
  pcexp.push (new RegExp ("^(bf1)(\\s*)([0-6]{1}" + BFPOa5 + "{1}" + BFPOa6 + "{1})$","i"));

  // Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA
  pcexp.push (new RegExp ("^(" + alpha1 + "{1}" + alpha2 + "?[0-9]{1,2})(\\s*)([0-9]{1}" + alpha5 + "{2})$","i"));
  
  // Expression for postcodes: ANA NAA
  pcexp.push (new RegExp ("^(" + alpha1 + "{1}[0-9]{1}" + alpha3 + "{1})(\\s*)([0-9]{1}" + alpha5 + "{2})$","i"));

  // Expression for postcodes: AANA  NAA
  pcexp.push (new RegExp ("^(" + alpha1 + "{1}" + alpha2 + "{1}" + "?[0-9]{1}" + alpha4 +"{1})(\\s*)([0-9]{1}" + alpha5 + "{2})$","i"));
  
  // Exception for the special postcode GIR 0AA
  pcexp.push (/^(GIR)(\s*)(0AA)$/i);
  
  // Standard BFPO numbers
  pcexp.push (/^(bfpo)(\s*)([0-9]{1,4})$/i);
  
  // c/o BFPO numbers
  pcexp.push (/^(bfpo)(\s*)(c\/o\s*[0-9]{1,3})$/i);
  
  // Overseas Territories
  pcexp.push (/^([A-Z]{4})(\s*)(1ZZ)$/i);  
  
  // Anguilla
  pcexp.push (/^(ai-2640)$/i);

  // Load up the string to check
  var postCode = toCheck;

  // Assume we're not going to find a valid postcode
  var valid = false;
  
  // Check the string against the types of post codes
  for ( var i=0; i<pcexp.length; i++) {
  
    if (pcexp[i].test(postCode)) {
    
      // The post code is valid - split the post code into component parts
      pcexp[i].exec(postCode);
      
      // Copy it back into the original string, converting it to uppercase and inserting a space 
      // between the inward and outward codes
      postCode = RegExp.$1.toUpperCase() + " " + RegExp.$3.toUpperCase();
      
      // If it is a BFPO c/o type postcode, tidy up the "c/o" part
      postCode = postCode.replace (/C\/O\s*/,"c/o ");
      
      // If it is the Anguilla overseas territory postcode, we need to treat it specially
      if (toCheck.toUpperCase() == 'AI-2640') {postCode = 'AI-2640'};
      
      // Load new postcode back into the form element
      valid = true;
      
      // Remember that we have found that the code is valid and break from loop
      break;
    }
  }
	  
  // Return with either the reformatted valid postcode or the original invalid postcode
  if (valid) {
  	document.getElementById("postcode").value=postCode;
	document.getElementById("postcode-valid").value=postCode;
	$('#postcode-warning').css({display: "none"});
	$('#input-wrap-postcode').css({background: "#FFF", border: "0px"});
  } else {
 	$('#input-wrap-postcode').css({background: "#F9F9F9", border: "1px solid #DF007A"});
	$('#postcode-warning').css({display: "block"});
	document.getElementById("postcode-valid").value='';
  }
}

/**********************************************************
BOOKING - Submit Form
**********************************************************/
function postBookingTVOForm() {
	"use strict";
	
	// SERIALIZE FORM
	var o = '';
	o = {};
	var a = $('#tvo-booking-form').serializeArray();
	
	document.getElementById("tvo-content").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/tvo/loadtvo.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Submitting Form</h3>';
	
	setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'This process can take around 30 seconds';
				}, 3000);

				setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'Perhaps hum some Sibelius while you wait';
				}, 6000);
	
				setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'Almost there...';
				}, 10000);
	
	// DATA TRANSLATE
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/tvo/submit_form.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				document.getElementById('tvo-content').innerHTML = response;
			}
		});
	}
}

///var/www/philharmonia.co.uk/js/18/audience_vote.js
/**********************************************************
AUDIENCE VOTE
**********************************************************/

/**********************************************************
VOTE - Load Form
**********************************************************/
function audienceVote(venueChosen) {
	"use strict";
	
	document.getElementById("av-form-wrap").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading Form</h3>';
	
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("av-form-wrap").innerHTML=xmlhttp.responseText;
		} else if (xmlhttp.status===502) {
			audienceVote(venueChosen);
		}
	};
	xmlhttp.open("GET","/lib/ajax/audience_vote/voting_form.php?voting_venue=" + venueChosen,true);
	xmlhttp.send();
}

/**********************************************************
VOTE - Load Form
**********************************************************/
function submitVote() {
	"use strict";
	
	$("html, body").animate({ scrollTop: 150 }, 500);
	
	// SERIALIZE FORM
	var o = '';
	o = {};
	var a = $('#av-form').serializeArray();
	
	document.getElementById("av-form-wrap").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Submitting Form</h3>';
	
	// DATA TRANSLATE
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/audience_vote/submit_vote.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				document.getElementById('av-form-wrap').innerHTML = response;
			}
		});
	}
	
	return false;
}

/**********************************************************
BOOKING - Check Form
**********************************************************/
function checkFormAV() {
	"use strict";
	
	// PREVENT DEFAULT - Avoid the form reloading the page
	event.preventDefault();
		
		if($('input:radio[name=voting_preference]').is(':checked')){
			$('#preference_wrap').css({background: "#F9F9F9", border: "1px solid #DDD"});
			$('#piece-field').css({display: "none"});
		} else {
			$("html, body").animate({scrollTop: $('#preference_wrap').offset().top -100 }, 'slow');
			$('#preference_wrap').css({background: "#FFF", border: "1px solid #E83754"});
			$('#piece-field').css({display: "block"});
			return false;
		}
	
		if(document.getElementById('firstname').value === '') {
			$("html, body").animate({scrollTop: $('#av-firstname-wrap').offset().top -100 }, 'slow');
			$('#av-firstname-wrap').css({background: "#FFF", border: "1px solid #E83754"});
			$('#firstname-field').css({display: "block"});
			return false;
		} else {
			$('#av-firstname-wrap').css({background: "#F9F9F9", border: "0"});
			$('#firstname-field').css({display: "none"});
		}
	
		if(document.getElementById('lastname').value === '') {
			$("html, body").animate({scrollTop: $('#av-lastname-wrap').offset().top -100 }, 'slow');
			$('#av-lastname-wrap').css({background: "#FFF", border: "1px solid #E83754"});
			$('#lastname-field').css({display: "block"});
			return false;
		} else {
			$('#av-lastname-wrap').css({background: "#F9F9F9", border: "0"});
			$('#lastname-field').css({display: "none"});
		}
	
		if(document.getElementById('email').value === '') {
			$("html, body").animate({scrollTop: $('#av-email-wrap').offset().top -100 }, 'slow');
			$('#av-email-wrap').css({background: "#FFF", border: "1px solid #E83754"});
			$('#email-field').css({display: "block"});
			return false;
		} else {
			$('#av-email-wrap').css({background: "#F9F9F9", border: "0"});
			$('#email-field').css({display: "none"});
		}
	
	submitVote();
}

///var/www/philharmonia.co.uk/js/18/ou.js
/**********************************************************
OU BOOKINGS
**********************************************************/

/**********************************************************
BOOKING - Form
**********************************************************/
function bookingFormOU(tessituraConcert,tessituraInset,booking) {
	"use strict";
	
	event.preventDefault();
	
	var tessituraConcertID = tessituraConcert;
	var tessituraInsetID = tessituraInset;
	var bookingID = booking;

	document.getElementById("educationContentHolder").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Loading</h3>';
	
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState===4 && xmlhttp.status===200) {
			document.getElementById("educationContentHolder").innerHTML=xmlhttp.responseText;
				setTimeout(function() {
					loadDataProtectionProjects();
				}, 1000);
		} else if (xmlhttp.status===502 || xmlhttp.status===404) {
			bookingForm(tessituraConcertID,tessituraInsetID,bookingID);
		}
	};
	xmlhttp.open("GET","/lib/ajax/ou/booking_form.php?tessitura_concert=" + tessituraConcertID + "&tessitura_inset=" + tessituraInsetID + "&booking_id=" + bookingID,true);
	xmlhttp.send();
		
	$("html, body").animate({ scrollTop: "100px" }, 500);
}

/**********************************************************
BOOKING - Update Input
Check the value is not blank in the required fields.
Also add additional checks for special inputs such as number limits
**********************************************************/
function updateInputOU(inputID) {
	"use strict";
	
	if(document.getElementById(inputID).value === '') {
		$('#input-wrap-' + inputID).css({border: "1px solid #E83754", padding: "10px"});
		$('#input-label-' + inputID).css({display: "block"});
		document.getElementById('input-label-' + inputID).innerHTML = "This field is required";
	} else {
		$('#input-wrap-' + inputID).css({border: "0px"});
		$('#input-label-' + inputID).css({display: "none"});
	}
	
	// IF - Recorded Media Content has an option selected or not
	if(document.getElementById('rmc_yes').checked || document.getElementById('rmc_no').checked) {
		$('#input-wrap-rmc').css({border: "0px"});
		$('#input-label-rmc').css({display: "none"});
	} else {
		$('#input-wrap-rmc').css({border: "1px solid #E83754", padding: "10px"});
		$('#input-label-rmc').css({display: "block"});
	}
	
}

/**********************************************************
BOOKING - Step
**********************************************************/
function bookingStepOU(stepID,bookingType) {
	"use strict";
	
	// PREVENT DEFAULT - Avoid the form reloading the page
	event.preventDefault();
		
	// VALIDATE - Contact Details
	if(stepID === 2) {
		if(bookingType === 'school' || bookingType === 'community') {
			if(document.getElementById('organisation').value === '') {
				$("html, body").animate({scrollTop: $('#input-wrap-organisation').offset().top -100 }, 'slow');
				$('#input-wrap-organisation').css({"border-bottom": "1px solid #E83754"});
				$('#input-label-organisation').css({display: "block"});
				return false;
			}
		}
		if(document.getElementById('address-1').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-address-1').offset().top -100 }, 'slow');
			$('#input-wrap-address-1').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-address-1').css({display: "block"});
			return false;
		}
		if(document.getElementById('city').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-city').offset().top -100 }, 'slow');
			$('#input-wrap-city').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-city').css({display: "block"});
			return false;
		}
		if(document.getElementById('county').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-country').offset().top -100 }, 'slow');
			$('#input-wrap-country').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-country').css({display: "block"});
			return false;
		}
		if(document.getElementById('postcode').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-postcode').offset().top -100 }, 'slow');
			$('#input-wrap-postcode').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-postcode').css({display: "block"});
			return false;
		}
		if(document.getElementById('postcode-valid').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-postcode').offset().top -100 }, 'slow');
			$('#input-wrap-postcode').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-postcode').css({display: "block"});
			return false;
		}
		if(document.getElementById('first-name').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-first-name').offset().top -100 }, 'slow');
			$('#input-wrap-first-name').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-first-name').css({display: "block"});
			return false;
		}
		if(document.getElementById('last-name').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-last-name').offset().top -100 }, 'slow');
			$('#input-wrap-last-name').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-last-name').css({display: "block"});
			return false;
		}
		if(document.getElementById('email-address').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-email-address').offset().top -100 }, 'slow');
			$('#input-wrap-email-address').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-email-address').css({display: "block"});
			return false;
		}
		if(document.getElementById('phone').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-phone').offset().top -100 }, 'slow');
			$('#input-wrap-phone').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-phone').css({display: "block"});
			return false;
		}
	}
	
	// VALIDATE - Activity
	if(stepID === 1 || stepID === 3) {
		if(document.getElementById('keystage-2-tickets').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-keystage-2-tickets').offset().top -100 }, 'slow');
			$('#input-wrap-keystage-2-tickets').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-keystage-2-tickets').css({display: "block"});
			return false;
		}
		if(document.getElementById('sen-tickets').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-sen-tickets').offset().top -100 }, 'slow');
			$('#input-wrap-sen-tickets').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-sen-tickets').css({display: "block"});
			return false;
		}
		if(document.getElementById('adult-tickets').value === '') {
			$("html, body").animate({scrollTop: $('#input-wrap-adult-tickets').offset().top -100 }, 'slow');
			$('#input-wrap-adult-tickets').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-adult-tickets').css({display: "block"});
			return false;
		}
		if(document.getElementById('rmc_yes').checked || document.getElementById('rmc_no').checked) {
		} else {
			$('#input-wrap-rmc').css({"border-bottom": "1px solid #E83754"});
			$('#input-label-rmc').css({display: "block"});
			return false;
		}
	}	
	
	$('.step-title').removeClass('active');
	$('#ou-step-' + stepID + '-title').addClass('active');
	
	// REVIEW STEP
	if(stepID === 3) {
		$('.ou-step-wrap').css({display: "block"});
		$('.ou-form-button-next').css({display: "none"});
		$('#ou-step-2-wrap').css({margin: "20px 0 0"});
		//$('input').attr("disabled", true);
		//$('select').attr("disabled", true);
		//$('textarea').attr("disabled", true);
		$('#ou-review-header').css({display: "block"});
		
	// ALL OTHER STEPS
	} else {
		$('.ou-step-wrap').css({display: "none"});
		$('#ou-step-' + stepID + '-wrap').animate({height: "toggle"});
	}
		
	$("html, body").animate({ scrollTop: '500px' }, 500);
	return false;
}

/**********************************************************
BOOKING - Submit Form
**********************************************************/
function postBookingOUForm() {
	"use strict";
	
	// SERIALIZE FORM
	var o = '';
	o = {};
	var a = $('#ou-booking-form').serializeArray();
	
	document.getElementById("ou-booking-form-wrap").innerHTML = '<img src="https://www.philharmonia.co.uk/assets/18/load1.svg" id="loading-rotate-one"><h3 style="text-align:center" id="loading-message-text">Submitting Form</h3>';
	
	setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'This process can take around 30 seconds';
				}, 3000);

				setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'Perhaps hum some Sibelius while you wait';
				}, 6000);
	
				setTimeout(function() {
					document.getElementById("loading-message-text").innerHTML = 'Almost there...';
				}, 10000);
	
	// DATA TRANSLATE
	$.each(a, function() {
	   if (o[this.name]) {
		   if (!o[this.name].push) {
			   o[this.name] = [o[this.name]];
		   }
		   o[this.name].push(this.value || '');
	   } else {
		   o[this.name] = this.value || '';
	   }
	});
	if(o) {
		var data = o;
		$.ajax({
			type:'POST',
			url:'/lib/ajax/ou/submit_form.php',
			data:{
				method: 'POST',
				data: data
			},
			success:function(response) {
				console.log(data);
				console.log(response);
				document.getElementById('ou-booking-form-wrap').innerHTML = response;
				$('#ou-booking-form-wrap').css({border: "0px"});
			}
		});
	}
}

///var/www/philharmonia.co.uk/core/blocks/gallery/gallery.js
var disable_gallery = false;
var current_image = 0;

function gallery(id, dir, carousel_id) {
	"use strict";
	if (!disable_gallery) {
		disable_gallery = true;
		
		if (carousel_id!==null) {
			carousel(carousel_id, dir);
		}

		if (dir==='right' && current_image<(gallery_images.length-1)) {
			current_image++;
		} else if (dir==='left' && current_image>0) {
			current_image--; 
		} else if (dir==='left') {
			current_image=gallery_images.length-1; 
		} else if (dir==='right') {
			current_image=0; 
		} else {
			disable_gallery = false;
			return false;
		}
		
		$('.gallery_img img.page_image').fadeOut(function() {
			$('.gallery_img').html('');
			$('.gallery_img').html(gallery_images[current_image]);
			setTimeout(function() {
				$('.gallery_img img.page_image').fadeIn(function() {
					disable_gallery = false;
					// reset all images for editing 
					if (typeof is_admin !== 'undefined'){
						images.setForEdit($(this));
					}
				});
			}, 500);
		});
		
		$(".page_image").lazyload({
			effect : "fadeIn"
		});
	}
}

function galleryJump(img) {
	"use strict";
	if (!disable_gallery) {
		disable_gallery = true;
		current_image = img;
		$('.gallery_img img').fadeOut(function() {
			$('.gallery_img').html(gallery_images[img]);
			setTimeout(function() {
				$('.gallery_img img').fadeIn(function() {
					disable_gallery = false;
				});
			}, 500);
		});
	}
	$('html, body').animate({scrollTop: $(".gallery_img").offset().top}, 1000);
}

///var/www/philharmonia.co.uk/blocks/calendar/calendar.js
/* new loader change e */
function doLoading() { 
    try { clearInterval(loadInterval); }
    catch(err) {}
    
    loadInterval = setInterval(nextStep, 100); 
    TweenLite.to($("#CalLoading"), 0.6, {opacity: 1, ease:Power1.easeInOut});
};

function nextStep() {
    loadStep += 1;
    if (loadStep == loadTotalsteps) { loadStep = 0; }  
    
    var leftPos = (loadStep % loadRows) * loadWidth * -1,
        topPos = (Math.floor(loadStep / loadRows)) * loadWidth * -1;
    
    $("#loadingIMG").css({ left:leftPos, top:topPos });
}

var loadInterval,
    loadRows = 2,
    loadTotalsteps = 8,
    loadWidth = 40,
    loadStep = 0;
/* new loader change e */

var calendar = {
	
	draw: function(year, month, type, title) {
		"use strict";
		
		type = (type==null) ? 'concerts' : type;
		
		$('#calendar').height($('#calendar').innerHeight()-20);
		
		/* new loader change e */
		$('#calendar').html('<div class="loading" id="CalLoading"><img id="loadingIMG" src="'+BASE+'/core/images/loaderNew.png" /></div>');
        doLoading();
		
		$.ajax({
			type:'GET',
			url:BASE+'/blocks/calendar/view.php',
			data:{
				month:month,
				year:year,
				type:type,
				title:title
			},
			success: function(data) {
				$('#calendar').replaceWith(data);
				calendar.linkify();
				$('#calendar').height('auto');
			}
		});
	},
	
	linkify: function() {
		"use strict";
		$('#calendar .caldate').each(function() {
			$(this).bind('mouseover', function() {
				var offset = $('#calendar').offset();
				
				/******************
				* Fix for drop down concert on Concerts Listing page
				*******************/
				if ($('#concerts_calendar').length) {
					var parent_offset = $('#concerts_calendar').offset();
					//console.log(parent_offset.left, Math.round(mouseX));
					var left = (Math.round(mouseX) - parent_offset.left)-($('#calendar_pop').width()/2) ;
				} else {
					var left = Math.round(mouseX)-($('#calendar_pop').width()/2) ;
				}
				if ($(this).hasClass('past')) {
					$('#calendar_pop').html('<p>PAST CONCERT<br/><strong>'+$(this).data('title')+"</strong><br />"+$(this).data('date')+"<br />"+$(this).data('venue')+"</p>");
				} else {
					$('#calendar_pop').html('<p><strong>'+$(this).data('title')+"</strong><br />"+$(this).data('date')+"<br />"+$(this).data('venue')+"</p>");
				}
				$('#calendar_pop').css({
					left:0,
					top:Math.round(mouseY)-180
				});
				$('#calendar_pop').show();
			});
			$('#calendar .caldate').bind('mouseleave', function() {
				$('#calendar_pop').hide();
			});
		});
	}
};

$(document).ready(function() {
	"use strict";
	calendar.linkify();
});

