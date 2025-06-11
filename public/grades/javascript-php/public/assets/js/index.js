setTimeout(function () {
  const alertBox = document.getElementById('alert');
  if (alertBox) {
    alertBox.style.display = 'none';
  }
}, 3000);

document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const search = document.getElementById('search');

  form.addEventListener('submit', function (e) {
    if (search.value.trim() === '') {
      e.preventDefault();
      return false;
    }

    return true;
  });
});
