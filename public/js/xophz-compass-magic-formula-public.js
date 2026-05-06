(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	$(document).ready(function() {
		// Listen for Forminator success events
		$(document).on('forminator:form:submit:success', function(e, response) {
			handleMagicGateSuccess(e.target);
		});

		// Fallback: Listen to all AJAX successes
		$(document).ajaxSuccess(function(event, xhr, settings) {
			if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('action=forminator_submit_form') !== -1) {
				try {
					var res = JSON.parse(xhr.responseText);
					if (res.success) {
						// Wait a brief moment for DOM updates, then look for success message
						setTimeout(function() {
							$('.magic-gate-default .forminator-response-message.forminator-success').each(function() {
								handleMagicGateSuccess(this);
							});
						}, 100);
					}
				} catch (e) {}
			}
		});

		function handleMagicGateSuccess(target) {
			var $defaultGate = $(target).closest('.magic-gate-default');
			if ($defaultGate.length > 0) {
				// The default form (e.g. login form) was submitted successfully.
				// We reload the page so the server can evaluate `is_user_logged_in()`
				// and display the gated form, effectively bypassing the cached login view.
				setTimeout(function() {
					window.location.reload(true);
				}, 1000); // Small delay to let the user see the success message
			}
		}
	});

})( jQuery );
