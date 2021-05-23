grecaptcha.ready(function() {
    grecaptcha.execute('6LerDnsUAAAAAJmknKLJnojWX6f6BiiUlUtyYaUk', {action: 'submit'}).then(function(token) {
        document.getElementsByName('token')[0].value = token;
    });
});