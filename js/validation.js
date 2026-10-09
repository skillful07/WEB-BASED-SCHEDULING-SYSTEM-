/**
 * js/validation.js
 * Lightweight, dependency-free client-side validation helpers used by
 * the login form and the various add/edit forms across the system.
 * These are UX conveniences only - all real validation happens again
 * server-side in PHP (see includes/auth_check.php and each module's PHP).
 */

document.addEventListener('DOMContentLoaded', function () {

  // Generic Bootstrap "was-validated" trigger for any form with class 'needs-validation'
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // Login form specific checks
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      const username = document.getElementById('username');
      const password = document.getElementById('password');
      let valid = true;
      if (username.value.trim().length < 3) { valid = false; }
      if (password.value.length < 4) { valid = false; }
      if (!valid) {
        e.preventDefault();
        alert('Please enter a valid username and password.');
      }
    });
  }

  // Schedule form: prevent submit if start/end resource or slot not selected
  const scheduleForm = document.getElementById('scheduleForm');
  if (scheduleForm) {
    scheduleForm.addEventListener('submit', function (e) {
      const resource = document.getElementById('resource_id');
      const slot = document.getElementById('slot_id');
      const user = document.getElementById('user_id');
      if (!resource.value || !slot.value || !user.value) {
        e.preventDefault();
        alert('Please select a resource, time slot, and lecturer/user before submitting.');
      }
    });
  }

  // Timeslot form: ensure end time is after start time
  const timeslotForm = document.getElementById('timeslotForm');
  if (timeslotForm) {
    timeslotForm.addEventListener('submit', function (e) {
      const start = document.getElementById('start_time');
      const end = document.getElementById('end_time');
      if (start && end && start.value && end.value && start.value >= end.value) {
        e.preventDefault();
        alert('End time must be later than start time.');
      }
    });
  }

  // Auto-dismiss alerts after 4 seconds
  document.querySelectorAll('.alert-auto-dismiss').forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.classList.remove('show');
      alertEl.classList.add('fade');
      setTimeout(() => alertEl.remove(), 300);
    }, 4000);
  });
});
