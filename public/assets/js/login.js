import { validateUsername, validatePassword } from "./login/validation.js";

const authenticationForm = document.getElementById("login-form");
const submitButton = document.getElementById("btn-submit");
const usernameInput = document.getElementById("username");
const usernameError = document.getElementById("username-error");
const passwordInput = document.getElementById("password");
const passwordError = document.getElementById("password-error");

authenticationForm.addEventListener("submit", (event) => {
  event.preventDefault();

  const { username, password } = event.target;
  const credentials = { username: username.value, password: password.value };

  const isUsernameValid = displayValidationError(
    usernameInput,
    usernameError,
    validateUsername(username.value),
  );
  const isPasswordValid = displayValidationError(
    passwordInput,
    passwordError,
    validatePassword(password.value),
  );

  if (!isUsernameValid || !isPasswordValid) return;

  authenticateUser(credentials);
});

async function authenticateUser(credentials) {
  setFormLoadingState(true);

  try {
    const response = await fetch("/auth/login", {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(credentials),
    });

    const data = await response.json();
    console.debug("Respuesta del servidor:", data);

    if (!response.ok) {
      displayAuthenticationError(data.error ?? "Error al iniciar sesión.");
      return;
    }

    window.location.href = "/dashboard";
  } catch {
    displayAuthenticationError(
      "No se pudo conectar con el servidor. Intenta de nuevo.",
    );
  } finally {
    setFormLoadingState(false);
  }
}

function displayAuthenticationError(message) {
  passwordError.textContent = message;
}

function setFormLoadingState(isLoading) {
  submitButton.disabled = isLoading;
  submitButton.textContent = isLoading ? "Verificando..." : "Iniciar sesión";
}

/**
 * @param {HtmlInputElement} inputElement - El elemento de entrada que se validó.
 * @param {HtmlSpanElement} errorElement - El elemento donde se mostrará el mensaje de error.
 * @param {string} message - El mensaje de error a mostrar.
 * */
function displayValidationError(inputElement, errorElement, message) {
  if (message) {
    inputElement.classList.add("input-error");
    errorElement.textContent = message;
    return false;
  }

  errorElement.textContent = "";
  inputElement.classList.remove("input-error");
  return true;
}

usernameInput.addEventListener("blur", (event) => {
  const errorMessage = validateUsername(event.target.value);
  displayValidationError(usernameInput, usernameError, errorMessage);
});

passwordInput.addEventListener("blur", (event) => {
  const errorMessage = validatePassword(event.target.value);
  displayValidationError(passwordInput, passwordError, errorMessage);
});
