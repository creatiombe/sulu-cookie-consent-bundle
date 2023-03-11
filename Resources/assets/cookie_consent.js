import './cookie_consent.scss';

document.addEventListener("DOMContentLoaded", function() {
    var cookieConsent = document.querySelector('.cookie-consent');
    var cookieConsentForm = document.querySelector('.cookie-consent__form');
    var cookieConsentFormBtn = document.querySelectorAll('.cookie-consent__btn');
    var cookieConsentCategoryDetails = document.querySelector('.cookie-consent__category-group');
    var cookieConsentCategoryDetailsToggle = document.querySelector('.cookie-consent__toggle-details');

    if (cookieConsentFormBtn.length > 0 && cookieConsentForm) {
        // Submit form via ajax
        for (var i = 0; i < cookieConsentFormBtn.length; i++) {
            var btn = cookieConsentFormBtn[i];
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                var xhr = new XMLHttpRequest();
                if(event.target.getAttribute('id') === 'cookie_consent_only_necessary') {

                    var checkboxes = cookieConsentForm.querySelectorAll('input[type="checkbox"]');
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = false;
                    }
                }
                cookieConsent.classList.add('cookie-consent__hide');
                setTimeout(function () {
                    cookieConsent.classList.add('cookie-consent__hidden');
                }, 300);
                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var buttonEvent = new CustomEvent('cookie-consent-submitted', {
                            detail: event.target
                        });
                        document.dispatchEvent(buttonEvent);
                    }
                };
                xhr.open('POST', cookieConsentForm.getAttribute('action'));
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(serializeForm(cookieConsentForm, event.target));
            }, false);
        }
    }

    var checkXhr = new XMLHttpRequest();
    checkXhr.onload = function () {
        if (xhr.status >= 200) {
            var response = JSON.parse(checkXhr.responseText);
            if (response.cookie_consent === 'true') {
                alert('Cookie consent already set');
            }
        }
    };
    checkXhr.open('GET', '/_cookie_consent/check');
    checkXhr.send();
});

function serializeForm(form, clickedButton) {
    var serialized = [];

    for (var i = 0; i < form.elements.length; i++) {
        var field = form.elements[i];

        if ((field.type !== 'checkbox' && field.type !== 'radio' && field.type !== 'button') || field.checked) {
            serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value));
        }
    }

    serialized.push(encodeURIComponent(clickedButton.getAttribute('name')) + "=");

    return serialized.join('&');
}

if ( typeof window.CustomEvent !== "function" ) {
    function CustomEvent(event, params) {
        params = params || {bubbles: false, cancelable: false, detail: undefined};
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;

    window.CustomEvent = CustomEvent;
}
