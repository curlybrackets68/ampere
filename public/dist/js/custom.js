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
