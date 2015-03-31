SubmarineForm = Class.create();
SubmarineForm.prototype = new VarienForm();

SubmarineForm.prototype.initialize = (function(superConstructor) {
    return function(formId, firstFieldFocus) {
        superConstructor.call(this, formId, firstFieldFocus);
        // if we have form element
        if (this.form) {
			this.jRedirectUrl = null;
			this.responseBlock = null;
            this.loadingBlock  = $(this.form.id + '-ajax');
            this.form.observe('submit', this.submit.bindAsEventListener(this))
        }
    };
})(VarienForm.prototype.initialize);

SubmarineForm.prototype.submit = function(e) {

    //new Effect.Fade($('status_error'), {duration : 1, afterFinishInternal : function() {$('status_error').hide();}});
    $('status_error').hide();
    if(this.validator && this.validator.validate()) {
        this._submit(this.form.getAttribute('action'));
    }
    Event.stop(e);
    return false;
};

SubmarineForm.prototype._submit = function(url) {
    if (this.loadingBlock) {
        this.loadingBlock.show();
    }
    new Ajax.Request(url, {
        method: this.form.getAttribute('method') || 'get',
        parameters: this.form.serialize(),
        onComplete: this._processResult.bind(this),
        onFailure: function() {
            location.href = BASE_URL;
        }
    });
};

SubmarineForm.prototype.setResponseMessage = function(type, msg) {

    $('status_error').update(msg);
    new Effect.Appear($('status_error'), {duration : 1 });
    
//    if (!this.responseBlock) {
//        Element.insert(this.form, { before: '<div></div>' });
//        this.responseBlock = this.form.previous('div');
//    }
//    this.responseBlock.update(msg.join ? msg.join("<br />") : msg);
//    this.responseBlock.className = 'validation-advice';
//    return this;
};

SubmarineForm.prototype._processResult = function(transport){
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
        this.setResponseMessage('error', response.error);
    } else if(response.success) {
        this.setResponseMessage('success', response.success);
        if (response.formVisibility == 'hide') {
            this.form.hide();
        }
    } else {
        var url = response.redirect ? response.redirect : location.href;
		if (this.jRedirectUrl) location.href = this.jRedirectUrl;
		else location.href = url;
    }
};

function showLoginForm() {
	if (jQuery('#composition_form').filter(":visible").length) {
		loginForm.jRedirectUrl = '?form';
	}
	var popup = $('login-popup');
	var dialog = $('dialog_overlay');
	if (popup.offsetWidth) {
		dialog.hide();
		popup.hide()
	} else {
		popup.show();
		dialog.show();
	}
}

$(document).observe('dom:loaded', function() {
    var loginBtn = $$('div.userbox a[href$="/login/"]')[0];
    if (loginBtn) {
        loginBtn.observe('click', function(e) {
			showLoginForm();
            Event.stop(e);
        });
//        $('popup-close').observe('click', function(e) {
//            $(this.parentNode).hide();
//            Event.stop(e);
//        });
        $('dialog_overlay').observe('click', function(e) {
            $('dialog_overlay').hide();
            $('login-popup').hide();
            Event.stop(e);
        });
    }
})

