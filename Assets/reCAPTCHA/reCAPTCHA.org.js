grecaptcha.ready(function() {
    grecaptcha.execute('{site_key}', {action: 'submit'}).then(function(token) {
        document.getElementsByName('token')[0].value = token;
    });
});