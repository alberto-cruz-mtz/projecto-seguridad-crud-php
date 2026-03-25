const RESET_TOKEN_STORAGE_KEY = "reset_password_token";

function getSearchParams() {
  return new URLSearchParams(window.location.search);
}

function readTokenFromQuery(params) {
  return params.get("token") || "";
}

function readEmailFromQuery(params) {
  return params.get("email") || "";
}

function saveResetToken(token) {
  if (token === "") {
    return;
  }

  window.localStorage.setItem(RESET_TOKEN_STORAGE_KEY, token);
}

function loadResetToken() {
  return window.localStorage.getItem(RESET_TOKEN_STORAGE_KEY) || "";
}

function clearStoredResetToken() {
  window.localStorage.removeItem(RESET_TOKEN_STORAGE_KEY);
}

function setInitialEmail(email) {
  const emailInput = document.getElementById("email");
  if (!emailInput) {
    return;
  }

  emailInput.value = email;
}

function buildPayload() {
  return {
    email: document.getElementById("email")?.value || "",
    token: loadResetToken(),
    new_password: document.getElementById("new_password")?.value || "",
  };
}

function getMessageElement() {
  return document.getElementById("message");
}

function resetMessageState(messageElement) {
  if (!messageElement) {
    return;
  }

  messageElement.classList.remove("error", "success", "has-content");
  messageElement.textContent = "";
}

function showError(messageElement, message) {
  if (!messageElement) {
    return;
  }

  messageElement.classList.remove("success");
  messageElement.classList.add("error", "has-content");
  messageElement.textContent = message;
}

function showSuccess(messageElement, message) {
  if (!messageElement) {
    return;
  }

  messageElement.classList.remove("error");
  messageElement.classList.add("success", "has-content");
  messageElement.textContent = message;
}

async function submitPasswordReset(payload) {
  const response = await fetch("/api/auth/password-reset/confirm", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  });

  let data = null;
  try {
    data = await response.json();
  } catch {
    data = null;
  }

  return { response, data };
}

function resolveErrorMessage(data) {
  if (data && typeof data.message === "string" && data.message !== "") {
    return data.message;
  }

  return "No fue posible restablecer la contrasena.";
}

function initializeResetSession() {
  const params = getSearchParams();
  const email = readEmailFromQuery(params);
  const tokenFromQuery = readTokenFromQuery(params);

  setInitialEmail(email);
  saveResetToken(tokenFromQuery);

  if (tokenFromQuery === "") {
    return;
  }

  const cleanUrl = new URL(window.location.href);
  cleanUrl.searchParams.delete("token");
  window.history.replaceState({}, "", cleanUrl.toString());
}

function createSubmitHandler() {
  return async function onSubmit(event) {
    event.preventDefault();

    const messageElement = getMessageElement();
    resetMessageState(messageElement);

    const token = loadResetToken();
    if (token === "") {
      showError(messageElement, "No existe un token de restablecimiento valido. Solicita un nuevo enlace.");
      return;
    }

    try {
      const payload = buildPayload();
      const { response, data } = await submitPasswordReset(payload);
      if (!response.ok) {
        showError(messageElement, resolveErrorMessage(data));
        return;
      }

      clearStoredResetToken();
      showSuccess(messageElement, data?.message || "Contrasena actualizada correctamente.");
    } catch {
      showError(messageElement, "No se pudo conectar con el servidor. Intenta nuevamente.");
    }
  };
}

function initPasswordResetView() {
  initializeResetSession();

  const form = document.getElementById("reset-form");
  if (!form) {
    return;
  }

  form.addEventListener("submit", createSubmitHandler());
}

document.addEventListener("DOMContentLoaded", initPasswordResetView);
