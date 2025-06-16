let countPos = parseInt($('#count_position_fields').val());
const maxPos = 9;

$(document).ready(function () {
  $('#addPos').click(function (event) {
    event.preventDefault();

    let totalPositions = $('#position_fields > div.position').length;

    if (totalPositions >= maxPos) {
      alert('Maximum of ' + maxPos + ' position entries exceeded!');
      return;
    }

    countPos++;

    $('#position_fields').append(
      `<div id="position${countPos}" class="position">
            <div class="row">
                <div class="col-md-2 form-group mb-3">
                    <label for="year${countPos}" class="form-label">Year</label>
                    <input type="text" class="form-control" id="year${countPos}" name="year${countPos}" size="4" maxlength="4">
                </div>
                <div class="col-md-2 form-group mb-3 align-self-end">
                    <button type="button" class="btn btn-outline-dark px-3 removePos" value="${countPos}">-</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group mb-3">
                    <textarea class="form-control" name="desc${countPos}" rows="5"></textarea>
                </div>
            </div>
        </div>`,
    );

    $('#count_position_fields').val(countPos);
    $('#total_position_fields').val(parseInt(totalPositions + 1));
  });

  $('#position_fields').on('click', '.removePos', function (event) {
    event.preventDefault();

    let totalPositions = $('#position_fields > div.position').length;

    if (totalPositions == 0) {
      return;
    }

    let position = $(this).val();

    $('#position' + position).remove();
    $('#total_position_fields').val(parseInt(totalPositions - 1));
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const fields = {
    firstname: document.getElementById('firstname'),
    lastname: document.getElementById('lastname'),
    email: document.getElementById('email'),
    headline: document.getElementById('headline'),
    summary: document.getElementById('summary'),
    totalPositions: document.getElementById('total_position_fields'),
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

    if (parseInt(fields.totalPositions.value) === 0) {
      showError(fields.totalPositions, 'At least one Position is required.');
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
