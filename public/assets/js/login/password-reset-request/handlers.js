import { requestPasswordReset } from "./api.js";
import { USER_NOT_FOUND_MESSAGE } from "./constants.js";
import {
  clearMessage,
  renderEmailError,
  setSubmittingState,
  showErrorMessage,
  showSuccessMessage,
} from "./ui.js";
import { validateEmail } from "./validators.js";

function validateEmailField(dom) {
  const emailValue = dom.emailInput?.value ?? "";
  const message = validateEmail(emailValue);
  renderEmailError(dom.emailInput, dom.emailError, message);

  return message === "";
}

function normalizeErrorMessage(status, message) {
  if (status === 404) {
    return USER_NOT_FOUND_MESSAGE;
  }

  if (message !== "") {
    return message;
  }

  return "No se pudo procesar la solicitud. Intenta nuevamente.";
}

export function createBlurHandler(dom) {
  return function onBlur(event) {
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) {
      return;
    }

    if (target.name !== "email") {
      return;
    }

    validateEmailField(dom);
  };
}

export function createSubmitHandler(dom) {
  return async function onSubmit(event) {
    event.preventDefault();
    clearMessage(dom.message);

    const isValid = validateEmailField(dom);
    if (!isValid) {
      return;
    }

    setSubmittingState(dom.loader, dom.submitButton, true);

    try {
      const email = dom.emailInput?.value.trim() ?? "";
      const result = await requestPasswordReset(email);

      if (!result.ok) {
        showErrorMessage(dom.message, normalizeErrorMessage(result.status, result.message));
        return;
      }

      showSuccessMessage(dom.message, result.message || "Se envio un enlace de restablecimiento a tu correo.");
      dom.form?.reset();
      renderEmailError(dom.emailInput, dom.emailError, "");
    } catch {
      showErrorMessage(dom.message, "No se pudo conectar con el servidor. Verifica tu conexion.");
    } finally {
      setSubmittingState(dom.loader, dom.submitButton, false);
    }
  };
}
