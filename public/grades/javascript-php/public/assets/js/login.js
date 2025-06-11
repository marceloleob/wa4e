document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const fields = {
    email: document.getElementById('email'),
    password: document.getElementById('password'),
  };

  form.addEventListener('submit', function (e) {
    clearErrors();
    let hasErrors = false;

    if (fields.email.value.trim() === '') {
      showError(fields.email, 'Email is required.');
      hasErrors = true;
    } else if (!validateEmail(fields.email.value)) {
      showError(fields.email, 'Invalid email format.');
      hasErrors = true;
    }

    if (fields.password.value.trim() === '') {
      showError(fields.password, 'Password is required.');
      hasErrors = true;
    } else if (fields.password.value.trim().length < 6) {
      showError(fields.password, 'The password must have at least 6 characters.');
      hasErrors = true;
    }

    if (hasErrors) {
      e.preventDefault();
      // only to follow the instructions and open a popup validation
      return doValidate();
    }

    return true;
  });

  function doValidate() {
    console.log('Validating...');
    try {
      addr = document.getElementById('email').value;
      pw = document.getElementById('password').value;
      console.log('Validating addr = ' + addr + ' pw=' + pw);
      if (addr == null || pw == '') {
        alert('Both fields must be filled out');
        return false;
      }
      return true;
    } catch (e) {
      return false;
    }
  }

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
      if (field.name == 'password' && field.value.trim().length < 6) {
        showError(fields.password, 'The password must have at least 6 characters.');
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
