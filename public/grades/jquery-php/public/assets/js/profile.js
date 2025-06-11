document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const fields = {
    firstname: document.getElementById('firstname'),
    lastname: document.getElementById('lastname'),
    email: document.getElementById('email'),
    headline: document.getElementById('headline'),
    summary: document.getElementById('summary'),
  };

  form.addEventListener('submit', function (e) {
    clearErrors();
    let hasErrors = false;

    if (fields.firstname.value.trim() === '') {
      showError(fields.firstname, 'First Name is required.');
      hasErrors = true;
    }

    if (fields.lastname.value.trim() === '') {
      showError(fields.lastname, 'Last Name is required.');
      hasErrors = true;
    }

    if (fields.email.value.trim() === '') {
      showError(fields.email, 'Email is required.');
      hasErrors = true;
    } else if (!validateEmail(fields.email.value)) {
      showError(fields.email, 'Invalid email format.');
      hasErrors = true;
    }

    if (fields.headline.value.trim() === '') {
      showError(fields.headline, 'Headline is required.');
      hasErrors = true;
    }

    if (fields.summary.value.trim() === '') {
      showError(fields.summary, 'Summary is required.');
      hasErrors = true;
    }

    if (hasErrors) {
      e.preventDefault();
      return false;
    }

    return true;
  });

  Object.values(fields).forEach((field) => {
    field.addEventListener('focusout', function () {
      if (field.value.trim() === '') {
        return false;
      }
      const error = field.nextElementSibling;
      if (error && error.classList.contains('text-danger')) {
        error.remove();
      }
      if (field.name == 'email' && !validateEmail(field.value)) {
        showError(fields.email, 'Invalid email format.');
        hasErrors = true;
      }
    });
  });

  function showError(inputElement, message) {
    const error = document.createElement('div');
    error.className = 'form-text text-danger mt-1';
    error.textContent = message;
    inputElement.insertAdjacentElement('afterend', error);
  }

  function clearErrors() {
    const errors = form.querySelectorAll('.text-danger');
    errors.forEach((el) => el.remove());
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
});
