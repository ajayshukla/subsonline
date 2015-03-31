
jQuery(document).ready(function() {
    jQuery(".bundles").hide();
    jQuery(".expand").live("click", function(){
        openList(jQuery(this).parent().next());
    });
    loadTabs();

//    jQuery(document).keyup(function(e) {
//        if (e.which == 9) {
//            var div_form_composition = jQuery('#product-options-wrapper');
//            jQuery(div_form_composition).find('div:first').css('border', '1px dashed #B0CF3A');
//        }
//    });

//    jQuery("#product-options-wrapper .subs").each(function(e){
//        jQuery(this).attr("tabindex",e+1);
//        jQuery(this).keyup(function(e) {
//            if (e.which == 9) {
//                alert(1);
//            }
//        });


});


function changeFilling(selection) {
    if (selection.value == 2) {
        jQuery('.checklist li').addClass("selected");
        jQuery('.checklist li').parent().find(":checkbox").attr("checked","checked");
        bundle.changeSelection(jQuery('.checklist li').find(":checkbox")[0]);
    } else {
        jQuery('.checklist li').removeClass("selected");
        jQuery('.checklist li').parent().find(":checkbox").removeAttr("checked");
        bundle.changeSelection(jQuery('.checklist li').find(":checkbox")[0]);
    }
}

function changeSub(option_id, type, skipAutoSelectSize) {
    var type_category = 'sub_category_' + type;
    skipAutoSelectSize = skipAutoSelectSize || false;

    jQuery('.type-of-sub').css('display', 'none');
    jQuery('.sub-category-' + type).css('display', 'block');
    jQuery('#ul_bundles-0').find('li').removeClass('selected');

    var product = jQuery('#product_addtocart_form .div-subs').find(':radio:checked');
    if (product.length > 0) {
        // attr class looks like "vegetarian-sub_category_22 subs", where 22 is subcategory id, and vegetarian is last part product's sku
        var parts = product.parent().attr('class').split('-');
        selector = parts[0]+'-'+type_category;
        jQuery('#product_addtocart_form .div-subs').find(':radio:checked').removeAttr('checked');
        jQuery('#product_addtocart_form .div-subs').find('.' + selector).find(':radio').attr("checked","checked");

        // id looks like "bundle-option-161-871" where 161 is optionId, 871 selectionId
        var id_radio = jQuery('#product_addtocart_form .div-subs').find(':radio:checked');
        var title_parts = id_radio[0].id.split('-');
        var id_title = 'li_bundle-option-' + title_parts[2] + '-' + title_parts[3];
        var title = jQuery('#' + id_title).text();

        jQuery('#ul_bundles-' + title_parts[2]).find('.selected').removeClass('selected');
        jQuery('#' + id_title).addClass('selected');

        jQuery('.bundle_title_wide').find('.expand').html('<span class="fill_name">' + title + '</span>');
		bundle.changeSelection(jQuery("#" + id_radio[0].id)[0]);

		jQuery('.filling_green').css('display', 'none');
		jQuery('#lnk_custom_filling').find('span').html('Vis salatbaren');
    }

	//change fillings
	var product_fillings = jQuery('#product_addtocart_form');
	if (product_fillings.length > 0) {
		product_fillings.find('.bundle_product_xtra').css('display', 'none');
		product_fillings.find('.bundle_product_xtra.sub_category_' + type).css('display', 'block');

		var checked_xtra = product_fillings.find('.product-xtra :checkbox:checked');
		if (checked_xtra.length > 0) {
			jQuery(checked_xtra).each(function() {
				jQuery(this).removeAttr('checked');
				var relationid = jQuery(this).parent().attr('relationid');
				jQuery(product_fillings).find('.' + relationid + '.product-xtra.sub_category_' + type).find(':checkbox').attr('checked', 'checked');
			});
			var re_checked_xtra = product_fillings.find('.product-xtra :checkbox:checked');
			jQuery(re_checked_xtra).each(function() {
				bundle.changeSelection(jQuery(this)[0]);
			});
		}
	}

    var bundle_option_product_types = ['bread', 'cheese'];
    for (i = 0; i < bundle_option_product_types.length; i++)
    {
        var product_type = bundle_option_product_types[i];
        var $ul_product  = jQuery('#product_addtocart_form .ul-' + product_type);
        if ($ul_product.length > 0)
        {
            var $ul_product_items = $ul_product.find('li.' + product_type);
            $ul_product_items
                .removeClass('selected').css('display', 'none')
                .filter('.sub-category-' + type).css('display', 'block');

            var $checked_product = jQuery('#product_addtocart_form .div-' + product_type + ' .' + product_type).find(':radio:checked');

            if ($checked_product.length > 0)
            {
                $checked_product.removeAttr('checked');
                var parts             = $checked_product.parent().attr('class').split('-');
                var $liWithInputRadio = jQuery('#product_addtocart_form .div-' + product_type).find('.' + parts[0] + '-sub_category_' + type);
                if ($liWithInputRadio.find(':radio').length > 0)
                {
                    $liWithInputRadio.find(':radio').attr('checked', 'checked');
                    bundle.changeSelection($liWithInputRadio.find(':radio:checked')[0]);
                    // looks like "bundle-option-165-925"
                    var radio_id          = $liWithInputRadio.find(':radio:checked')[0].id;
                    var product_option_id = radio_id.split('-')[2];
                    jQuery('#li_' + radio_id).addClass('selected');
                    jQuery('#bundle_title_' + product_option_id).html('<span class="fill_name">' + jQuery('#li_' + radio_id).text() + '</span>');
                }
            }
        }
    }

	var product_extras = jQuery('#product_addtocart_form ul.checkbox');
	if (product_extras.length > 0) {
		jQuery(product_extras).find('li').css('display', 'none');
		jQuery(product_extras).find('li.sub-category-' + type).css('display', 'block');

		var checked_extras = jQuery('#product_addtocart_form .div-extras').find(':checkbox:checked');
		if (checked_extras.length > 0) {
			jQuery('#product_addtocart_form .div-extras').find(':checkbox:checked').removeAttr('checked');
			jQuery(checked_extras).each(function(i){
				var parts = jQuery(this).parent().attr('class').split('-');
				selector = parts[0]+'-'+type_category;

				jQuery('#product_addtocart_form .div-extras').find('.' + selector).find(':checkbox').attr("checked","checked");
				var id_checkbox = jQuery('#product_addtocart_form .div-extras').find(':checkbox:checked');
				var title_parts = id_checkbox[0].id.split('-');
				var prev_parts = jQuery(this).attr('id').split('-');
				jQuery('#li_bundle-option-' + prev_parts[2] + '-' + prev_parts[3]).removeClass('selected');
				var id_title = 'li_bundle-option-' + title_parts[2] + '-' + title_parts[3];
				jQuery('#' + id_title).addClass('selected');
				bundle.changeSelection(jQuery(id_checkbox)[0]);
			});
		}
	}

    closeLists();
    var selectedSubText = 'Vælg';
    if (!skipAutoSelectSize) {
        var $liBundleOptionCategory = jQuery('#li_bundle-option-' + option_id + '-' + type);
        $liBundleOptionCategory.addClass('selected');
        selectedSubText = '<span class="fill_name">' + $liBundleOptionCategory.text() + '</span>';
    }
    jQuery('.type-of-sub-size').prev().children().html(selectedSubText);
    jQuery('#hidden_input_bundle_title_0').val(skipAutoSelectSize ? 0 : 1);
}

function setProduct(product_id, css_sku) {
    var title = jQuery('.product_' + product_id).html();
    jQuery('.bundle_title_wide').find('.expand').html(title);

    var obj_radio = jQuery('.div-' + css_sku).find('.product_' + product_id);
    jQuery('.bundles').find('.product_' + product_id).addClass('selected');
    if (obj_radio.length > 0) {
        obj_radio.attr("checked","checked");
        parts = obj_radio[0].id.split('-');
        jQuery('#hidden_input_bundle_title_' + parts[2]).val(parts[3]);
        bundle.changeSelection(jQuery("#" + obj_radio[0].id)[0]);
        jQuery('.div-' + css_sku).appendTo('#product_addtocart_form');
    } else {
        jQuery('.bundle_title_wide').find('.expand').html('Vælg');
    }
}

function setDefaultSubSize(type) {
    jQuery('.type-of-sub-size').prev().children().html('<span class="fill_name">' + type + '</span>');
    jQuery('.type-of-sub-size').find('li:first').css('background', '#BCD757');
}

function showFilling(filling_id) {
    jQuery('#'+filling_id).css("display", "block");
}

function addToForm(option_id, selection_id) {

    var id = 'div_bundle_radio_' + option_id;
    var update = jQuery('#li_bundle-option-' + option_id + '-' + selection_id).text();

	jQuery('#ul_bundles-' + option_id).find('li').css('background', '');
    jQuery('#ul_bundles-' + option_id).find('li').removeClass('selected');
    jQuery('#li_bundle-option-' + option_id + '-' + selection_id).addClass('selected');

    jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');
	jQuery('#' + id).find(':checked').removeAttr('checked');
    jQuery('#bundle-option-' + option_id + '-' + selection_id).attr("checked","checked");
    closeLists();

    jQuery('#hidden_input_bundle_title_' + option_id).val(selection_id);

	//Change price for Sandwich for cheese and for Kidsmenu
	if (jQuery('#ul_bundles-' + option_id).hasClass('ul-sandwich') || jQuery('#ul_bundles-' + option_id).hasClass('ul-kidsmenu')) {
		if (jQuery('#li_bundle-option-' + option_id + '-' + selection_id).attr('sku') == 'sandwich-4inch-cheese' || jQuery('#li_bundle-option-' + option_id + '-' + selection_id).attr('sku') == "kidsmenu-4Ham&Cheese") {

			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'none');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'block');

			if (jQuery('.div-cheese').find('li[sku="cheese-withcheese"]').find(':radio:checked').length > 0) {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').removeClass('selected');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').addClass('selected');

				jQuery('.div-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').find(':radio').attr("checked","checked");
				bundle.changeSelection(jQuery('.div-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').find(':radio')[0]);

				var update = jQuery('.bundles.ul-cheese').find('.selected .fill_name').text();
				var parts = jQuery('.bundles.ul-cheese').attr('id').split('-');
				jQuery('#bundle_title_' + parts[1]).find('.fill_name').text(update);
			}
		} else {
			if (jQuery('.div-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').find(':radio:checked').length > 0) {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').removeClass('selected');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').addClass('selected');

				jQuery('.div-cheese').find('li[sku="cheese-withcheese"]').find(':radio').attr("checked","checked");
				bundle.changeSelection(jQuery('.div-cheese').find('li[sku="cheese-withcheese"]').find(':radio')[0]);

				var update = jQuery('.bundles.ul-cheese').find('.selected .fill_name').text();
				var parts = jQuery('.bundles.ul-cheese').attr('id').split('-');
				jQuery('#bundle_title_' + parts[1]).find('.fill_name').text(update);
			}

			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'block');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'none');
		}
	}

	//Change price For Kids menu and 4" Cheese(4" Sandwich) for Double Cheese
	if (jQuery('#ul_bundles-' + option_id).hasClass('ul-kidsmenu') || jQuery('#ul_bundles-' + option_id).hasClass('ul-sandwich')) {
		if (jQuery('#li_bundle-option-' + option_id + '-' + selection_id).attr('sku') == 'sandwich-4inch-cheese' || jQuery('#li_bundle-option-' + option_id + '-' + selection_id).attr('sku') == "kidsmenu-4Ham&Cheese") {
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'none');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'block');

			if (jQuery('.div-cheese').find('li[sku="cheese-double"]').find(':radio:checked').length > 0) {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').removeClass('selected');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').addClass('selected');

				jQuery('.div-cheese').find('li[sku="cheese-double-4inchcheese"]').find(':radio').attr("checked","checked");
				bundle.changeSelection(jQuery('.div-cheese').find('li[sku="cheese-double-4inchcheese"]').find(':radio')[0]);

				var update = jQuery('.bundles.ul-cheese').find('.selected .fill_name').text();
				var parts = jQuery('.bundles.ul-cheese').attr('id').split('-');
				jQuery('#bundle_title_' + parts[1]).find('.fill_name').text(update);
			}
		} else {
			if (jQuery('.div-cheese').find('li[sku="cheese-double-4inchcheese"]').find(':radio:checked').length > 0) {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').removeClass('selected');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').addClass('selected');

				jQuery('.div-cheese').find('li[sku="cheese-double"]').find(':radio').attr("checked","checked");
				bundle.changeSelection(jQuery('.div-cheese').find('li[sku="cheese-double"]').find(':radio')[0]);

				var update = jQuery('.bundles.ul-cheese').find('.selected .fill_name').text();
				var parts = jQuery('.bundles.ul-cheese').attr('id').split('-');
				jQuery('#bundle_title_' + parts[1]).find('.fill_name').text(update);
			}

			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'block');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'none');
		}
	}

    bundle.changeSelection(jQuery('#bundle-option-' + option_id + '-' + selection_id)[0]);
}

function addBundleProduct(option_id, selection_id, type) {
    var id = 'div_bundle_option_' + selection_id;
    var oProductForm = jQuery('#product_addtocart_form .' + type);
    if (oProductForm.length > 0) {
        jQuery(oProductForm).find(":checkbox:checked").removeAttr("checked");
    }

    jQuery('#' + id).find(":checkbox").attr("checked","checked");

    jQuery('.bundle-product-none-' + type).css('background-color', '');
    jQuery('.bundle-product-xtra-' + type).css('background-color', '');
    bundle.changeSelection(jQuery('#bundle-option-' + option_id + '-' + selection_id)[0]);

    jQuery('#image-' + option_id + '-' + type).removeClass('bundle-product-opacity');
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
}

function addBundleProductXtra(option_id, selection_id, type) {
    var id = 'div_bundle_option_' + selection_id;
    var oProductForm = jQuery('#product_addtocart_form .' + type);
    if (oProductForm.length > 0) {
        jQuery(oProductForm).find(":checkbox:checked").removeAttr("checked");
    }
    jQuery('#' + id).find(":checkbox").attr("checked","checked");
    jQuery('.bundle-product-none-' + type).css('background-color', '');
    jQuery('.bundle-product-xtra-' + type).css('background-color', '#ffffff');
    bundle.changeSelection(jQuery('#bundle-option-' + option_id + '-' + selection_id)[0]);

    jQuery('#image-' + option_id + '-' + type).removeClass('bundle-product-opacity');

    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
}

function removeBundleProduct(option_id, type) {
    var oProductForm = jQuery('#product_addtocart_form .' + type);
    if (oProductForm.length > 0) {
        jQuery(oProductForm).find(":checkbox:checked").removeAttr("checked");
    }

    var id_bundle_product = jQuery('#div_bundle_filling_' + option_id).find(":checkbox")[0].id;
    bundle.changeSelection(jQuery('#' + id_bundle_product)[0]);

    jQuery('.bundle-product-none-' + type).css('background-color', '#bcd757');
    jQuery('.bundle-product-xtra-' + type).css('background-color', '');

    jQuery('#image-' + option_id + '-' + type).attr('class', 'bundle-product-opacity');
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
}

function loadDefaultFilling(option_id) {
    var filling_products = jQuery('.product-yes');
    for (var i = 0; i <= filling_products.length - 1; i++) {
        jQuery('#' + filling_products[i].id).find(":checkbox").attr("checked","checked");
        jQuery('#' + filling_products[i].id).appendTo('#product_addtocart_form');
        bundle.changeSelection(jQuery('#' + filling_products[i].id).find(":checkbox")[0]);
    }
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
    jQuery('#li-filling-option-' + option_id + '-0').css('background', '#BCD757');
}

function setDivFillingState(option_id, is_open)
{
    var $div_bundle_filling = jQuery('#div_bundle_filling_' + option_id);
    if (!$div_bundle_filling) {
        return false;
    }
    var $lnk_custom_filling = jQuery('#lnk_custom_filling');
    var $img_src            = $lnk_custom_filling.find('img');
    var img_src             = $img_src.attr('src');
    var text_state = '';
    
    if (is_open) {
        text_state = Translator.translate('Hide custom fillings');
        img_src    = img_src.replace("images/white_arrow_up.png", "images/white_arrow_down.png");
    } else {
        text_state = Translator.translate('Show Custom fillings');
        img_src    = img_src.replace("images/white_arrow_down.png", "images/white_arrow_up.png");
    }

    $lnk_custom_filling.css('display', is_open === null ? 'none' : 'block');
    $lnk_custom_filling.find('span').html(text_state);
    $lnk_custom_filling.find('img').attr('src', img_src);
    $div_bundle_filling.css('display', is_open ? 'block' : 'none');
}

function customFilling(option_id, selection_id) {

    jQuery('#ul_bundles-' + option_id + ' > li').css('background', '');
    jQuery('#li-filling-option-' + option_id + '-' + selection_id).css('background', '#BCD757');

    var filling_products_form = jQuery('#product_addtocart_form .product-xtra');
    if (filling_products_form.length > 0) {
        for (var i=0; i <= filling_products_form.length - 1; i++) {
            jQuery('#' + filling_products_form[i].id).appendTo('#div_bundle_filling_' + option_id);
            jQuery('#div_bundle_filling_' + option_id).find(":checkbox").removeAttr("checked");
            bundle.changeSelection(jQuery('#' + filling_products_form[i].id).find(":checkbox")[0]);

            jQuery('.bundle_product_xtra').css('background-color', '');
            jQuery('.bundle_product_none').css('background-color', '');
        }
    }

    closeLists();
    jQuery('#div_bundle_filling_' + option_id).css('display', 'block');

    var update = jQuery('#li-filling-option-' + option_id + '-' + selection_id).text();

    jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');

    var filling_products = jQuery('.product-yes');
    for (var i = 0; i <= filling_products.length - 1; i++) {
        jQuery('#' + filling_products[i].id).find(":checkbox").attr("checked","checked");
        jQuery('#' + filling_products[i].id).appendTo('#product_addtocart_form');
        bundle.changeSelection(jQuery('#' + filling_products[i].id).find(":checkbox")[0]);
    }

    jQuery('#hidden_input_bundle_title_' + option_id).val(1);

    //jQuery('#lnk_custom_filling').css('display', 'block');
    setDivFillingState(option_id, true);
}

function everythingFilling(option_id, selection_id) {

    jQuery('#ul_bundles-' + option_id + ' > li').css('background', '');
    jQuery('#li-filling-option-' + option_id + '-' + selection_id).css('background', '#BCD757');

    var update = jQuery('#li-filling-option-' + option_id + '-' + selection_id).text();
    jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');

    var filling_products = jQuery('.product-yes');
    for (var i = 0; i <= filling_products.length - 1; i++) {
        jQuery('#' + filling_products[i].id).find(":checkbox").attr("checked","checked");
        jQuery('#' + filling_products[i].id).appendTo('#product_addtocart_form');
        bundle.changeSelection(jQuery('#' + filling_products[i].id).find(":checkbox")[0]);
    }

    var oProductForm = jQuery('#product_addtocart_form .product-xtra');
    if (oProductForm.length > 0) {
        jQuery('#product_addtocart_form .product-xtra').appendTo('#div_bundle_filling_' + option_id);
    }
    jQuery('#div_bundle_filling_' + option_id).find(":checkbox").removeAttr("checked");

    /*var id_bundle_product = jQuery('#div_bundle_filling_' + option_id).find(":checkbox")[0].id;
    bundle.changeSelection(jQuery('#' + id_bundle_product)[0]);*/

    closeLists();
    jQuery('#div_bundle_filling_' + option_id).css('display', 'none');

    jQuery('.bundle-product-main > img').removeAttr('class', 'bundle-product-opacity');
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);

    jQuery('#lnk_custom_filling').css('display', 'none');
}


function favoriteFilling(option_id, selection_id) {

    if (favouriteFillings) {
        jQuery('#ul_bundles-' + option_id + ' > li').css('background', '');
        jQuery('#li-filling-option-' + option_id + '-' + selection_id).css('background', '#BCD757');
        jQuery(favouriteFillings).each(function(i) {
            if (favouriteFillings[i]['fillingid'] == selection_id) {
                var update = jQuery('#li-filling-option-' + option_id + '-' + selection_id).text();
                jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');

                var oProductFormXtra = jQuery('#product_addtocart_form .product-xtra');
                if (oProductFormXtra.length > 0) {
                    jQuery('#product_addtocart_form .product-xtra').appendTo('#div_bundle_filling_' + option_id);
                }
                var oProductFormYes = jQuery('#product_addtocart_form .product-yes');
                if (oProductFormYes.length > 0) {
                    jQuery('#product_addtocart_form .product-yes').appendTo('#div_bundle_filling_' + option_id);
                }

                jQuery('#div_bundle_filling_' + option_id).find(":checkbox").removeAttr("checked");

                for (var io=0; io <= favouriteFillings[i]['optionsids'].length-1; io++) {
                    var div_filling = jQuery('div[productid="'+favouriteFillings[i]['optionsids'][io]['productid']+'"]');
                    jQuery('#' + div_filling.attr('id')).appendTo('#product_addtocart_form');
                    div_filling.find(':checkbox').attr("checked","checked");
                }
                bundle.changeSelection(jQuery('#product_addtocart_form').find(":checkbox")[0]);
            }
        });
		showCheckedFillings(option_id);
        closeLists();
		jQuery('#lnk_custom_filling').css('display', 'block');
		//jQuery('#div_bundle_filling_' + option_id).css('display', 'none');
		//jQuery('#lnk_custom_filling').find('span').html('Show Custom Filling');
    }
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
}


function setItems() {

    //find all checked items
    jQuery('.fillings').find(':checked').each(function(i) {

        var bundle_item = jQuery(this);
        var parts = bundle_item.attr('id').split('-');
        var update = jQuery('#li_' + bundle_item.attr('id') + '').find('.fill_name').text();
        //jQuery('#li_' + bundle_item.attr('id') + '').css('background', '#BCD757');
        jQuery('#li_' + bundle_item.attr('id') + '').addClass('selected');
        if (update != '') {
            jQuery('#bundle_title_' + parts[2]).html('<span class="fill_name">' + update + '</span>');
            jQuery('#hidden_input_bundle_title_' + parts[2]).val(parts[3]);
        }

        if (jQuery('#div_bundle_radio_' + parts[2]).attr('class') == 'div-subs') {
            var parts_subs = bundle_item.parent().attr('class').split('-');
            var parts_category = parts_subs[1].split('_');
			showSubSizeItems(parts_category)
        }

		if (jQuery('#ul_bundles-' + parts[2]).hasClass('ul-sandwich') || jQuery('#ul_bundles-' + parts[2]).hasClass('ul-kidsmenu')) {
			if (jQuery('#li_bundle-option-' + parts[2] + '-' + parts[3]).attr('sku') == 'sandwich-4inch-cheese' || jQuery('#li_bundle-option-' + parts[2] + '-' + parts[3]).attr('sku') == 'kidsmenu-4Ham&Cheese') {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'none');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'block');

				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'none');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'block');

			} else {
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'block');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'none');

				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'block');
				jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'none');
			}
		}

        if (jQuery('#div_bundle_checkbox_' + parts[2]).length > 0) {
            var count = jQuery('#div_bundle_checkbox_' + parts[2]).find(":checkbox:checked").length;
            if (count > 0) {
                update = '<span class="fill_name">' + count + ' ekstra er valgt' + '</span>';
            } else {
                update = 'Vælg';
            }

            jQuery('#bundle_title_' + parts[2]).html(update);
        }
    });
    bundle.reloadPrice();
}

jQuery.fn.compare = function(t) {
    if (this.length != t.length) {return false;}
    var a = this.sort(),
        b = t.sort();
    for (var i = 0; t[i]; i++) {
        if (a[i] !== b[i]) {
                return false;
        }
    }
    return true;
};

function editOrderFavoriteFilling(option_id) {

	jQuery('#lnk_custom_filling').css('display', 'block');
    var isFavoriteFilling = false;
    if (favouriteFillings) {
        var loadedFavoriteFilling = new Array();
        var editOrderFavoriteFilling = new Array();
        // from favorite array
        jQuery(favouriteFillings).each(function(i) {
            for (var io=0; io <= favouriteFillings[i]['optionsids'].length-1; io++) {
                if (typeof (loadedFavoriteFilling[favouriteFillings[i]['fillingid']]) == 'undefined') {
                    loadedFavoriteFilling[favouriteFillings[i]['fillingid']] = new Array();
                }
                loadedFavoriteFilling[favouriteFillings[i]['fillingid']][io] = favouriteFillings[i]['optionsids'][io]['productid'];
            }
        });

        //from user favorite array
        var oProductForm = jQuery('#product_addtocart_form .fillings .filling_green').find(':checkbox:checked');
        jQuery(oProductForm).each(function(i) {
            editOrderFavoriteFilling[i] = jQuery(this).parent().attr('productid');
        });

        //compare products
        jQuery(loadedFavoriteFilling).each(function(i) {
            if (jQuery(loadedFavoriteFilling[i]).compare(editOrderFavoriteFilling)) {
                jQuery(favouriteFillings).each(function() {
                    if (this['fillingid'] == i) {
                        isFavoriteFilling = true;
                        var update = this['description'];
                        jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');

                        jQuery('#li-filling-option-' + option_id + '-' + this['fillingid']).css('background', '#BCD757');
                    }
                });
            }
        });
    }
    //if not found favorite filling, select custom option or everything
    if (!isFavoriteFilling) {
        var everything = (jQuery('#product_addtocart_form .product-yes').find(':checkbox').length);
        if (everything != jQuery('#product_addtocart_form .product-yes').find(':checkbox:checked').length) {
            //custom option
            var update = 'Vælg selv fra salatbaren';
            jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');
            jQuery('#li-filling-option-' + option_id + '-' + 1).css('background', '#BCD757');
        } else {
            var update = 'ALT fra salatbaren';
            jQuery('#bundle_title_' + option_id).html('<span class="fill_name">' + update + '</span>');
            jQuery('#li-filling-option-' + option_id + '-' + 0).css('background', '#BCD757');
        }
    }

    if (wishlist) {
        var loadedWishlist = new Array();
        var editOrderWishlist = new Array();
        var isWishlist = false;

        //load from wishlist
        jQuery(wishlist).each(function(i) {
            for (var io=0; io <= wishlist[i]['optionsids'].length-1; io++) {
                for (var iv=0; iv<=wishlist[i]['optionsids'][io]['value'].length-1; iv++) {
                    if (typeof (loadedWishlist[wishlist[i]['id']]) == 'undefined') {
                        loadedWishlist[wishlist[i]['id']] = new Array();
                    }
                    loadedWishlist[wishlist[i]['id']].push(wishlist[i]['optionsids'][io]['value'][iv]['productid']);
                }
            }
        });

        //load from edit form
        var oProductForm = jQuery('#product_addtocart_form').find('.options-list [value!=""]:checked');
        jQuery(oProductForm).each(function(i) {
            editOrderWishlist[i] = jQuery(this).parent().attr('productid');
        });

        //compare products
        jQuery(loadedWishlist).each(function(i) {
            if (jQuery(loadedWishlist[i]).compare(editOrderWishlist)) {
                jQuery(wishlist).each(function() {
                    if (this['id'] == i) {
                        isWishlist = true;
                        var update = this['description'];
                        jQuery('#favorite_product > .expand').html('<span class="fill_name" style="text-transform: none; font-weight: normal;">' + update + '</span>');
                        jQuery('#li_bundle-wishlist-' + this['id']).css('background', '#BCD757');

                    }
                });
            }
        });
    }
    jQuery('#hidden_input_bundle_title_' + option_id).val(1);
}


function addSomeItemsToForm(option_id, selection_id) {
    var id = 'div_bundle_checkbox_' + option_id;

    if (jQuery('#bundle-option-' + option_id + '-' + selection_id + ':checked').length > 0) {
        jQuery('#bundle-option-' + option_id + '-' + selection_id).removeAttr("checked");
        //jQuery('#li_bundle-option-'+option_id+'-'+selection_id).css('background', '');
		jQuery('#li_bundle-option-'+option_id+'-'+selection_id).removeClass('selected');
    } else {
        jQuery('#bundle-option-' + option_id + '-' + selection_id).attr("checked","checked");
        //jQuery('#li_bundle-option-'+option_id+'-'+selection_id).css('background', '#BCD757');
		jQuery('#li_bundle-option-'+option_id+'-'+selection_id).addClass('selected');
    }

    bundle.changeSelection(jQuery('#bundle-option-' + option_id + '-' + selection_id)[0]);

    var count = jQuery('#'+id).find(":checkbox:checked").length;
    if (count > 0) {
        update = '<span class="fill_name">' + count + ' ekstra er valgt' + '</span>';
    } else {
        update = 'Vælg';
    }

    jQuery('#bundle_title_' + option_id).html(update);
    jQuery('#hidden_input_bundle_title_' + option_id).val(count);
}


function loadWishList(wishlist_id, product_id) {
    if (wishlist) {
        //removed all attributes checked for Wishlist
        jQuery('.options-list').find(':checked').removeAttr('checked');
        jQuery('.type-of-sub').css('display', 'none');
        jQuery('.box.subs > .bundle_title .expand').text('Vælg');
		jQuery('#ul-favorite > li').removeClass('selected');
		jQuery('#ul-favorite > li').css('background', '');
		jQuery('#li_bundle-wishlist-' + wishlist_id + '-' + product_id).addClass('selected');
        jQuery(wishlist).each(function() {
           if (this.id == wishlist_id) {

               jQuery('#favorite_product > .expand').html('<span class="fill_name" style="text-transform: none; font-weight: normal;">' + this['description'] + '</span>');

               jQuery(this['optionsids']).each(function() {
                   for (var i=0; i<=this['value'].length-1; i++) {

                       var obj_input = jQuery('input.product_'+this['value'][i]['productid']);
                        if (obj_input.length > 0) {
                                //checked products from wishlist
                                obj_input.attr('checked', 'checked');

                                //set product from wishlist
                                var parts = obj_input.attr('id').split('-');
                                var update = jQuery('#li_bundle-option-'+parts[2]+'-'+parts[3]+' .fill_name').text();
                                jQuery('#bundle_title_'+parts[2]+'').html('<span class="fill_name">' + update + '</span>');

                                jQuery('#hidden_input_bundle_title_' + parts[2]).val(parts[3]);

                                //Size for Sub
                                if (jQuery('#div_bundle_radio_' + parts[2]).attr('class') == 'div-subs') {
                                    var parts_subs = obj_input.parent().attr('class').split('-');
                                    var parts_category = parts_subs[1].split('_');
                                    showSubSizeItems(parts_category);
                                }

								if (this['type'] == 'filling') {
									//jQuery('.box.filling .expand').find('.fill_name').html('Selected');

								}

								//Price formation for Sandwich and Kids menu
								if (jQuery('#ul_bundles-' + parts[2]).hasClass('ul-sandwich') || jQuery('#ul_bundles-' + parts[2]).hasClass('ul-kidsmenu')) {
									if (jQuery('#li_bundle-option-' + parts[2] + '-' + parts[3]).attr('sku') == 'sandwich-4inch-cheese' || jQuery('#li_bundle-option-' + parts[2] + '-' + parts[3]).attr('sku') == 'kidsmenu-4Ham&Cheese') {
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'block');
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'none');

										jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'block');
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'none');

									} else {
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'none');
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'block');

										jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'none');
										jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'block');
									}
								}

                                if (jQuery('#div_bundle_checkbox_' + parts[2]).length > 0) {
                                    var count = jQuery('#div_bundle_checkbox_' + parts[2]).find(":checkbox:checked").length;
                                    if (count > 0) {
                                        update = '<span class="fill_name">' + count + ' selected' + '</span>';
                                    } else {
                                        update = 'Vælg';
                                    }
                                    jQuery('#bundle_title_' + parts[2]).html(update);
                                }

								jQuery('#ul_bundles-' + parts[2]).find('li').removeClass('selected');
								jQuery('#li_bundle-option-'+parts[2]+'-' + parts[3] ).addClass('selected');
                        }
                   }
               });
			   var parts = jQuery('.bundles.filling').attr('id').split('-');
			   editOrderFavoriteFilling(parts[1]);
           }
        });

        //change price for wishlist
        bundle.resetPrice();
        var oInput = jQuery('#product_addtocart_form').find(':checked');
        for (var i=0; i<oInput.length; i++) {
            bundle.changeSelection(oInput[i]);
        }
    }
    closeLists();
}

function closeLists() {
   jQuery(document).unbind('keyup');
   jQuery(jQuery('ul.bundles')).each(function() {
       if (jQuery(this).css('display') == 'block') {
           jQuery(this).slideToggle();
       }
   });
}

function openList(element) {
   if (jQuery(element).css('display') == 'none') {
       closeLists();

       dropDownByArrows(element);

       jQuery(element).slideToggle();
   } else {
       closeLists();
   }
}

function loadComposition(url, product_id) {
    jQuery.get(url,
        function(data){
            //$('dialog_overlay').hide();
            jQuery('.content.two_columns').empty();
            jQuery('.content.two_columns').append(data);
            loadProductById(product_id);
        }, "html");
}

function setLocationAjax(url,id){
    url += 'isAjax/1';
    url = url.replace("checkout/cart","ajax/checkout_cart");
    jQuery('#ajax_loader'+id).show();
    try {
            jQuery.ajax( {
                    url : url,
                    dataType : 'json',
                    success : function(data) {
                        jQuery('#ajax_loader'+id).hide();
                        if(data.status == 'ERROR') {
                                alert(data.message);
                        }else{
                            if(jQuery('#sidebar')){
                                jQuery('#sidebar').html(data.sidebar);
                                var localeBasket = data.locale['Basket'] || 'Basket';
                                jQuery('.toplinks').find('a[href="/checkout/cart"]').html(localeBasket + ' (' + data.cart_items_count + ')');
                            }
                        }
                    }
            });
    } catch (e) {
    }
}

function loadFavoriteProduct(data, url) {

    if (jQuery('#li_bundle-wishlist-' + data.sidebar['id']).length == 0) {
        jQuery('#favorite_product').removeClass('disabled');
        jQuery('#favorite_product').html('<div class="expand"><div style="width: 120px; overflow: hidden; white-space: nowrap; color: #000">' + data.sidebar['description'] + '</div></div>');
        jQuery('#favorite_product').parent().append('<ul class="bundles" id="ul-favorite" style="display: none;">');

        jQuery.ajax({
            url : url,
            dataType : 'json',
            type : 'post',
            data : data,
            success : function(data) {
                wishlist = data;
            }
        });

        var html = '<li id="li_bundle-wishlist-'+data.sidebar['id']+'-'+data.sidebar['product_id']+'" onclick="loadWishList('+data.sidebar['id']+', '+data.sidebar['product_id']+')" style="height: 30px; line-height: 30px;"><span class="fill_name" style="padding-left: 8px; float: left; font-weight: normal; text-transform: none; color: #000">'+data.sidebar['description']+'</span></li>';
        jQuery('#ul-favorite').append(html);
        jQuery('#ul-favorite > li').removeClass('selected');
		jQuery('#li_bundle-wishlist-' + data.sidebar['id'] + '-' + data.sidebar['product_id']).addClass('selected');
    }
}

function loadFavoriteFilling(data, url) {
    var parts = jQuery('.box.filling > ul').attr('id').split('-');
    jQuery('#ul_bundles-' + parts[1] + ' > li').css('background', '');
    jQuery('#bundle_title_' + parts[1]).find('span').html(data.sidebar['description']);

    jQuery.ajax({
            url : url,
            dataType : 'json',
            type : 'post',
            data : data,
            success : function(data) {
                favouriteFillings = data;
            }
        });
    var html = '<li id="li-filling-option-'+parts[1]+'-'+data.sidebar['id']+'" onclick="favoriteFilling('+parts[1]+', '+data.sidebar['id']+')"><span style="padding-left: 8px;">'+data.sidebar['description']+'</span></li>';
    jQuery('.box.filling > ul').append(html);

    jQuery('#li-filling-option-' + parts[1] + '-' + data.sidebar['id']).css('background', '#BCD757');

    closeLists();
    jQuery('#div_bundle_filling_' + parts[1]).css('display', 'none');
}

function showLoginPopup() {
    $('login-popup').show();
    $('dialog_overlay').show();
}

function loadDrinks() {
    var cnt_drinks = jQuery(".ul-drinks .drinks").length;
    var categorys = new Object();
    var name;
    if (cnt_drinks > 0) {

        jQuery(".ul-drinks .drinks").each(function(i){
            name = jQuery(this).attr('rel');
            if (typeof (categorys[name]) == 'undefined') {
                categorys[name] = new Object();
            }
            categorys[name][i] = new Object();
            categorys[name][i]['id'] = jQuery(this).attr('id');
            categorys[name][i]['onclick'] = jQuery(this).attr('onclick');
            categorys[name][i]['name'] = jQuery(this).find('.fill_name').text();
            categorys[name][i]['img'] = jQuery(this).find('span').html();
        });

        var root_chield = jQuery(".ul-drinks .drinks").parent();
        jQuery(".ul-drinks .drinks").parent().empty();

        var html = '';

        for (var property in categorys) {
                html += '<li class="dir"><span class="fill_name" style="float: left; padding-left: 10px;">' + property + '</span><ul>';
                for (var subcategory in categorys[property]) {
                    html += '<li id="' + categorys[property][subcategory]['id'] + '" onclick="' + categorys[property][subcategory]['onclick'] + '"><span>' + categorys[property][subcategory]['img'] + '</span><span class="fill_name">' + categorys[property][subcategory]['name'] + '</span></li>';
                }
                html += '</ul></li>';
            }
            root_chield.append(html);
    }
}

function showHideCustomFilling(option_id) {
    var img_src = jQuery('#lnk_custom_filling').find('img').attr('src');
    if (jQuery('#div_bundle_filling_' + option_id).css('display') == "block") {
        jQuery('#lnk_custom_filling').find('span').html('Vis salatbaren');
        jQuery('#div_bundle_filling_' + option_id).css('display', 'none');
        img_src = img_src.replace("images/white_arrow_down.png", "images/white_arrow_up.png");
        jQuery('#lnk_custom_filling').find('img').attr('src', img_src);
    } else {
        jQuery('#lnk_custom_filling').find('span').html('Skjul salatbaren');
        img_src = img_src.replace("images/white_arrow_up.png", "images/white_arrow_down.png");
        jQuery('#lnk_custom_filling').find('img').attr('src', img_src);
        jQuery('#div_bundle_filling_' + option_id).css('display', 'block');

    }
}

function showHideCreateOrderForm() {
	//jQuery('#lnk_custom_filling').css('display', 'none');
	jQuery('.filling_green').css('display', 'none');
	jQuery('.filling_green').find('img').removeClass('bundle-product-opacity');
	jQuery('.filling_green').find('.bundle_product_none').css('background', '');
	jQuery('.filling_green').find('.bundle_product_xtra').css('background', '');
	jQuery('.bundles').css('display', 'none');


    jQuery('#composition_form').show();
    jQuery('.create-new-order').hide();
}

function dropDownByArrows(element) {
    jQuery(document).keyup(function(e) {
           if (e.which == 40) {
               //Down
               var objectUl = jQuery(element).find('li:visible').parent();
               if ((objectUl).find('.selected').nextAll('li:visible:first').length > 0) {
                   jQuery(objectUl).find('.selected').removeClass('selected').nextAll('li:visible:first').addClass('selected');
               } else {
                   jQuery(objectUl).find('.selected').removeClass('selected');
                   jQuery(objectUl).find('li:first').addClass('selected');
               }
//               var update = jQuery(objectUl).find('.selected > .fill_name').html();
//               jQuery(element).parent().find('.expand > .fill_name').html(update);
           } else if (e.which == 38) {
               //Up
               var objectUl = jQuery(element).find('li:visible').parent();
               if (jQuery(objectUl).find('.selected').prevAll('li:visible:first').length > 0) {
                   jQuery(objectUl).find('.selected').removeClass('selected').prevAll('li:visible:first').addClass('selected');
               } else {
                   jQuery(objectUl).find('.selected').removeClass('selected');
                   jQuery(objectUl).find('li:last').addClass('selected');
               }
//               var update = jQuery(objectUl).find('.selected > .fill_name').html();
//               jQuery(element).parent().find('.expand > .fill_name').html(update);
           } else if (e.which == 13) {
                //Enter
                var $selected = jQuery(element).find('.selected');
                if ($selected.length == 1 && $selected.attr('id')) {
                    var parts = $selected.attr('id').split('-');
                    //For Extra
                    if (jQuery(element).hasClass('checkbox')) {
                        addSomeItemsToForm(parts[2], parts[3]);
                    } else if (jQuery(element).hasClass('filling')) {
                        //For Fillings
                        if (parts[4] == 0) {
                            everythingFilling(parts[3], parts[4]);
                        } else if (parts[4] == 1) {
                            customFilling(parts[3], parts[4]);
                        } else {
                            favoriteFilling(parts[3], parts[4]);
                        }
                    } else if (jQuery(element).hasClass('favorite_product')) {
                        loadWishList(parts[2], parts[3]);
                    } else {
                        if (jQuery(element).hasClass('type-of-sub-size')) {
                            changeSub(parts[2], parts[3]);
                        } else {
                            addToForm(parts[2], parts[3]);
                        }
                    }
                }
           } else if (e.which == 27) {
               //Esc
               closeLists();
           }
       });
}

function loadTabs() {
    jQuery('.expand').each(function(e) {
        jQuery(this).parent().attr("tabindex",e+1);
        if (jQuery(this).parent().next().css('display') == 'none') {
            jQuery(this).parent().keyup(function(e) {
                    if (e.which == 40) {
                        if (jQuery(this).parent().find('.bundles').css('display') == 'none') {
                            openList(jQuery(this).parent().find('.bundles'));
                        }
                    }
                });
            }
    });
}

function loadProductById(id) {
    //Find product
    clearForm();
    jQuery('#product_list_main').hide(0, function(){
        jQuery('#composition_form_main').show(0);
    });
    if (jQuery('.create-new-order').css('display') == "block") {
        jQuery('#composition_form').show(0);
        jQuery('.create-new-order').hide(0);
    }

    var $firstBoxBundlesProduct = jQuery('.first_box').find('.bundles').find('.product_' + id);
    if ($firstBoxBundlesProduct.length > 0) {
        var parts = $firstBoxBundlesProduct.attr('id').split('-');
        var sku   = $firstBoxBundlesProduct.attr('sku');

        if ($firstBoxBundlesProduct.hasClass('type-of-sub'))
        {
            var classes            = $firstBoxBundlesProduct.attr('class').split(' ');
            var patternSubCategory = /^sub-category-(\d+)$/;
            for (i = 0; i < classes.length; i++)
            {
                var classname = classes[i];
                if (patternSubCategory.test(classname))
                {
                    var category_id = patternSubCategory.exec(classname)[1];
                    if (category_id > 0) {
                        changeSub(0, category_id, true);
                    }
                }
            }
        }

		if (sku == 'sandwich-4inch-cheese' || sku == 'kidsmenu-4Ham&Cheese') {
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'none');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'block');

			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'none');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'block');
		} else {
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese"]').css('display', 'block');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-withcheese-4inchcheese"]').css('display', 'none');

			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double"]').css('display', 'block');
			jQuery('.bundles.ul-cheese').find('li[sku="cheese-double-4inchcheese"]').css('display', 'none');
		}
        addToForm(parts[2], parts[3]);
    }
}

function clearForm() {

	closeLists();

	jQuery(".first_box, .box").each(function(e){
		jQuery(this).find('.expand').html('Vælg');
		jQuery(this).find('ul > li').removeClass('selected').css('background', '');
		jQuery(this).find('input[type="hidden"]').val(0);
		jQuery(this).find(':radio:checked').removeAttr('checked');
		jQuery(this).find(':checkbox:checked').removeAttr('checked');
        jQuery("#qty").val(1);
	});

    if (jQuery(".filling_green").css('display') == "block") {
        var lnk_custom_filling = jQuery('#lnk_custom_filling').css('display', 'none');

        jQuery('.product-yes').find(":checkbox").attr("checked","checked");
        jQuery('.bundle_product_filling > div').css('background', '');
        jQuery('.bundle-product-main > img').removeClass('bundle-product-opacity');
        jQuery('.filling_green').css('display', 'none');
    }

	bundle.resetPrice();
}

function showSubSizeItems(parts_category) {
	var regexp = new RegExp('([0-9]+)', 'ig');
	var idCategory = parseInt(regexp.exec(parts_category[2])[1]);
	var update_size = jQuery('#li_bundle-option-0-' + idCategory).text();

	jQuery('.type-of-sub.sub-category-' + idCategory).css('display', 'block');


	jQuery('#li_bundle-option-0-' + idCategory).addClass('selected');
	jQuery('#bundle_title_0').html('<span class="fill_name">' + update_size + '</span>');
	jQuery('#hidden_input_bundle_title_0').val(1);

	//Switcher
	//cheese
	jQuery('.bundles.ul-cheese .cheese').css('display', 'none');
	jQuery('.bundles.ul-cheese').find('.sub-category-' + idCategory).css('display', 'block');

	//bread
	jQuery('.bundles.ul-bread .bread').css('display', 'none');
	jQuery('.bundles.ul-bread').find('.sub-category-' + idCategory).css('display', 'block');

	//extras
	jQuery('.bundles.checkbox > li').css('display', 'none');
	jQuery('.bundles.checkbox').find('.sub-category-' + idCategory).css('display', 'block');

	//fillings
	jQuery('.filling_green .bundle_product_xtra').css('display', 'none');
	jQuery('.filling_green .bundle_product_xtra..sub_category_' + idCategory).css('display', 'block');
}

function showCheckedFillings(option_id) {
   // switch state: if block -> none, otherwise none -> block;
   var is_open = (jQuery('#div_bundle_filling_' + option_id).css('display') == "block");
   setDivFillingState(option_id, !is_open);

	//jQuery('.filling_green').css('display', 'block');
	jQuery('.bundle-product-main > img').addClass('bundle-product-opacity');
	jQuery('.bundle_product_none').css('background', '#BCD757');
	jQuery('.bundle_product_xtra').css('background', '');

	var checked_fillings = jQuery('.product-xtra, .product-yes').find(':checkbox:checked');

	if (jQuery('#ul_bundles-0').length > 0) {
		var product_fillings = jQuery('#product_addtocart_form');
		if (product_fillings.length > 0) {

			if (jQuery('#ul_bundles-0').find('.selected').length > 0) {
				var parts = jQuery('#ul_bundles-0').find('.selected').attr('id').split('-');
			} else {
				var parts = jQuery('#ul_bundles-0').find('li:first').attr('id').split('-');
			}

			product_fillings.find('.bundle_product_xtra').css('display', 'none');
			product_fillings.find('.bundle_product_xtra.sub_category_' + parts[3]).css('display', 'block');

			var checked_xtra = product_fillings.find('.product-xtra :checkbox:checked');
			if (checked_xtra.length > 0) {
				jQuery(checked_xtra).each(function() {
					jQuery(this).removeAttr('checked');
					var relationid = jQuery(this).parent().attr('relationid');
					jQuery(product_fillings).find('.' + relationid + '.product-xtra.sub_category_' + parts[3]).find(':checkbox').attr('checked', 'checked');
				});
				var re_checked_xtra = product_fillings.find('.product-xtra :checkbox:checked');
				jQuery(re_checked_xtra).each(function() {
					bundle.changeSelection(jQuery(this)[0]);
				});
			}
		}
	}

	jQuery(checked_fillings).each(function() {
		var parts = jQuery(this).attr('id').split('-');

		var selection = jQuery('#selection_' + parts[3]);
		if (selection.length > 0) {
			if (selection.hasClass('bundle_product_xtra')) {

				selection.parent().find('.bundle_product_xtra').css('display', 'none');
				if (jQuery('#ul_bundles-0').length > 0) {
					if (jQuery('#ul_bundles-0').find('.selected').length > 0) {
						var size_parts = jQuery('#ul_bundles-0').find('.selected').attr('id').split('-');
					} else {
						var size_parts = jQuery('#ul_bundles-0').find('li:first').attr('id').split('-');
					}
					selection.parent().find('.bundle_product_xtra.sub_category_' + size_parts[3]).css('display', 'block');
					selection.parent().find('.bundle_product_xtra.sub_category_' + size_parts[3]).css('background', '#fff');
				} else {
					selection.css('display', 'block');
					selection.css('background', '#fff');
				}

				selection.parent().parent().find('img').removeClass('bundle-product-opacity');
				selection.parent().find('.bundle_product_none').css('background', '');

			} else if (selection.hasClass('bundle-product-main')) {
				selection.find('img').removeClass('bundle-product-opacity');
				selection.parent().find('.bundle_product_none').css('background', '');
			}
		}
	});
}