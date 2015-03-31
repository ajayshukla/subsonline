EditFormPopupAjax = Class.create();
EditFormPopupAjax.prototype = new VarienForm();

EditFormPopupAjax.prototype.initialize = (function(superConstructor) {
    return function(formId, firstFieldFocus) {
        superConstructor.call(this, formId, firstFieldFocus);
        // if we have form element
        if (this.form) {
            this.responseBlock = $('msgPopup');
            this.loadingBlock  = $(this.form.id + '-ajax');
            this.form.observe('submit', this.submit.bindAsEventListener(this))
        }
    };
})(VarienForm.prototype.initialize);

EditFormPopupAjax.prototype.submit = function(e) {
    if(this.validator && this.validator.validate()) {
        this._submit(this.form.getAttribute('action')+'?isAjax=1');
    }
    Event.stop(e);
    return false;
};

EditFormPopupAjax.prototype._submit = function(url) {
    if (this.loadingBlock) {
        this.loadingBlock.show();
	}
	$('btnEditFormPopup').setAttribute('disabled', 'disabled');
    new Ajax.Request(url, {
        method: this.form.getAttribute('method') || 'get',
        parameters: this.form.serialize(),
        onComplete: this._processResult.bind(this),
        onFailure: function() {
			//location.href = BASE_URL;
			$('btnEditFormPopup').removeAttribute('disabled');
			if (this.loadingBlock) {
				this.loadingBlock.hide();
			}
        }
    });
};

EditFormPopupAjax.prototype.setResponseMessage = function(type, msg) {
    this.responseBlock.update(msg.join ? msg.join("<br />") : msg);
    this.responseBlock.className = type;
    //this.responseBlock.show();
	new Effect.Appear(this.responseBlock, {duration:1, from:0, to:1});
    return this;
};

EditFormPopupAjax.prototype._success = function(msg) {
	$('btnEditFormPopup').removeAttribute('disabled');
	$('dialog_overlay').hide();
	$('change_details').hide();
	this.responseBlock.update('');
	$('info_company').update($F('company'));
	$('info_cvr').update($F('cvr'));
	$('info_firstname').update($F('firstname'));
	$('info_address').update($F('address'));
	$('info_zip').update($F('zip'));
	$('info_city').update($F('city'));
	$('info_phone').update($F('phone'));
	$('info_mphone').update($F('mphone'));
	$('info_email').update($F('email_'));
	$('infoMsg').update(msg);
	new Effect.Appear($('infoMsg'), {duration:1, from:0, to:1});
}

EditFormPopupAjax.prototype._processResult = function(transport){
    if (this.loadingBlock) {
        this.loadingBlock.hide();
    }
    var response = '';
    try {
        response = transport.responseText.evalJSON();
    } catch (e) {
        response = transport.responseText;
    }
    if (response.error) {
        $('btnEditFormPopup').removeAttribute('disabled');
		this.setResponseMessage('error', response.error);
    } else if(response.success) {
		//this.setResponseMessage('success', response.success);
		this._success(response.success);
    }
};

$(document).observe('dom:loaded', function() {
    var changeInfoBtn = $$('div.userbox a[href$="#"]')[0];
	if (changeInfoBtn) {
        changeInfoBtn.observe('click', function(e) {
            var popup = $('change_details');
            var dialog = $('dialog_overlay');
            if (popup.offsetWidth) {
                dialog.hide();
                popup.hide()
            } else {
                popup.show();
                dialog.show();
            }
            Event.stop(e);
        });
//        $('popup-close').observe('click', function(e) {
//            $(this.parentNode).hide();
//            Event.stop(e);
//        });
        $('dialog_overlay').observe('click', function(e) {
            $('dialog_overlay').hide();
            $('change_details').hide();
            Event.stop(e);
        });
    }
	
	var btnEditFormPopup = $('btnEditFormPopup');
	if (btnEditFormPopup) {
		btnEditFormPopup.observe('click', function(e) {
			if ($('current_password').getValue() != '') {
				$('current_password').addClassName('required-entry');
				$('password').addClassName('required-entry');
				$('confirmation').addClassName('required-entry');
			} else {
				$('current_password').removeClassName('required-entry')
					.removeClassName('validation-advice')
					.removeClassName('validation-passed');
				$('password').removeClassName('required-entry')
					.removeClassName('validation-advice')
					.removeClassName('validation-passed');
				$('confirmation').removeClassName('required-entry')
					.removeClassName('validation-advice')
					.removeClassName('validation-passed');
				if ($('advice-required-entry-password')) $('advice-required-entry-password').hide();
				if ($('advice-required-entry-confirmation')) $('advice-required-entry-confirmation').hide();
			}
			//Event.stop(e);
		})
	}
	var cancelBtnEditFormPopup = $('cancelBtnEditFormPopup');
	if (cancelBtnEditFormPopup) {
		cancelBtnEditFormPopup.observe('click', function(e) {
			$('dialog_overlay').hide();
            $('change_details').hide();
		})
	}
})
