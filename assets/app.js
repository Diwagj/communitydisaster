document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    button.addEventListener("click", () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const show = input.type === "password";
      input.type = show ? "text" : "password";
      button.textContent = show ? "HIDE" : "SHOW";
    });
  });

  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const message = document.getElementById("loginMessage");
      message.className = "form-message show info";
      message.textContent = "This is a front-end prototype. Login will work after PHP authentication and database integration are implemented.";
    });
  }

  const createForm = document.getElementById("createUserForm");
  if (createForm) {
    createForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const message = document.getElementById("createUserMessage");
      const password = document.getElementById("password");
      const confirm = document.getElementById("confirmPassword");
      if (password.value.length < 8) {
        message.className = "form-message show error";
        message.textContent = "Use a temporary password with at least 8 characters.";
        password.focus();
        return;
      }
      if (password.value !== confirm.value) {
        message.className = "form-message show error";
        message.textContent = "The password and confirmation do not match.";
        confirm.focus();
        return;
      }
      message.className = "form-message show info";
      message.textContent = "Form validation passed. The account has not been saved because PHP and database integration are not connected yet.";
    });
    createForm.addEventListener("reset", () => {
      const message = document.getElementById("createUserMessage");
      message.className = "form-message";
      message.textContent = "";
    });
  }
});
