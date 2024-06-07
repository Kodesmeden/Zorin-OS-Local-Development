import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.querySelector('#app-type');
    const hiddenDiv = document.querySelector('.show-if-git');
    const inputElement = hiddenDiv.querySelector('input');

    selectElement.addEventListener('change', function() {
        if (this.value === 'git') {
            hiddenDiv.style.display = 'block';
            inputElement.required = true;
        } else {
            hiddenDiv.style.display = 'none';
            inputElement.required = false;
        }
    });
});
