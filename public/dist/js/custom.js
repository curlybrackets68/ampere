function loaderButton(buttonId, isLoading, loadingText = 'Saving...', defaultText = 'Submit') {
    const button = $(`#${buttonId}`);

    if (button.find('.spinner-border').length === 0) {
        button.html(`
            <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
            <span class="button-text">${defaultText}</span>
        `);
    }

    const spinner = button.find('.spinner-border');
    const buttonText = button.find('.button-text');

    if (isLoading) {
        button.prop('disabled', true);
        spinner.removeClass('d-none');
        buttonText.text(loadingText);
    } else {
        button.prop('disabled', false);
        spinner.addClass('d-none');
        buttonText.text(defaultText);
    }
}

function showToast(type, message, title = '') {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-center",
        "timeOut": "4000",
    };

    switch (type) {
        case 'success':
            toastr.success(message, title || 'Success');
            break;
        case 'error':
            toastr.error(message, title || 'Error');
            break;
        case 'warning':
            toastr.warning(message, title || 'Warning');
            break;
        case 'info':
            toastr.info(message, title || 'Info');
            break;
        default:
            toastr.info(message, title);
            break;
    }
}

async function apiCallPost(url, data, headers = {}) {
    let response = await fetch(url, {
        method: "POST",
        headers: headers,
        body: data,
    });
    if (response.ok) {
        let result = await response.json();
        return result;
    } else {
        if (response.status == "419") {
            // window.location.reload();
        } else if (response.status == "422") {
            let result = await response.json();
            return result;
        } else {
            dangerToast("Something went wrong!");
        }
        return false;
    }
}