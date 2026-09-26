document.addEventListener('DOMContentLoaded', () => {
  const roleSelect = document.getElementById('role');
  const subjectField = document.getElementById('subject-field');
  const subjectPasswordRow = document.getElementById('subject-password-row');

  // Show/hide subject field based on role
  roleSelect.addEventListener('change', () => {
    if (roleSelect.value === 'teacher') {
      subjectField.style.display = 'block';
      subjectPasswordRow.style.justifyContent = 'space-between';
    } else {
      subjectField.style.display = 'none';
      subjectPasswordRow.style.justifyContent = 'flex-end';
    }
  });

  // Password toggle
  document.getElementById('togglePassword').addEventListener('click', () => {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    document.getElementById('togglePassword').textContent = type === 'password' ? '👁' : '👁‍🗨';
  });
});