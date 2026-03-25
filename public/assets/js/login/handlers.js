import { getAttemptGuardState, getRemainingAttempts, isFormLocked, registerFailedAttempt, resetAttemptGuardState } from "./attempt-guard.js";
import { AUTH_ERROR_MESSAGE, FIELD_NAMES, LOGIN_SECURITY } from "./constants.js";
import { requestLogin } from "./api.js";
import { clearAuthAlert, renderFieldError, setSubmittingState, showAuthAlert } from "./ui.js";
import { validateByName } from "./validators.js";

function collectFieldValues(fields) {
  return {
    email: fields[FIELD_NAMES.email]?.value ?? "",
    password: fields[FIELD_NAMES.password]?.value ?? "",
  };
}

function validateField(fields, fieldErrors, fieldName) {
  const input = fields[fieldName];
  const errorElement = fieldErrors[fieldName];
  const value = input?.value ?? "";
  const errorMessage = validateByName(fieldName, value);
  renderFieldError(input, errorElement, errorMessage);

  return errorMessage === "";
}

function validateForm(fields, fieldErrors) {
  const isEmailValid = validateField(fields, fieldErrors, FIELD_NAMES.email);
  const isPasswordValid = validateField(fields, fieldErrors, FIELD_NAMES.password);

  if (!isEmailValid) {
    return false;
  }

  if (!isPasswordValid) {
    return false;
  }

  return true;
}

function normalizeAuthenticationMessage(status, message) {
  if (status === 401) {
    return AUTH_ERROR_MESSAGE;
  }

  if (message !== "") {
    return message;
  }

  return "Ocurrio un error inesperado. Intenta nuevamente.";
}

function getWarningMessage(state) {
  const remainingAttempts = getRemainingAttempts(state, LOGIN_SECURITY.maxAttempts);
  if (remainingAttempts === 2) {
    return "Credenciales incorrectas. Te quedan 2 intentos.";
  }

  if (remainingAttempts === 1) {
    return "Credenciales incorrectas. Te queda 1 intento.";
  }

  return "";
}

function getLockMessage() {
  return "Inicio de sesion bloqueado temporalmente por 5 intentos fallidos. Intenta nuevamente en 15 minutos.";
}

function setFormLockedUi(dom, locked) {
  if (dom.loader) {
    dom.loader.classList.remove("is-active");
    dom.loader.setAttribute("aria-hidden", "true");
  }

  if (dom.submitButton) {
    dom.submitButton.disabled = locked;
  }
}

function applyLockState(dom, state) {
  const locked = isFormLocked(state);
  setFormLockedUi(dom, locked);

  if (locked) {
    showAuthAlert(dom.authAlert, getLockMessage());
  }
}

function resolveRedirectByRole(user) {
  const roleName = String(user?.role?.name ?? "").toLowerCase();

  if (roleName === "admin") {
    return "/admin/dashboard";
  }

  if (roleName === "treasurer") {
    return "/treasurer/dashboard";
  }

  if (roleName === "student") {
    return "/student/dashboard";
  }

  return "/login";
}

function redirectAfterLogin(user) {
  const redirectPath = resolveRedirectByRole(user);
  window.location.assign(redirectPath);
}

export function createBlurHandler(dom) {
  return function onBlur(event) {
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) {
      return;
    }

    if (!Object.hasOwn(dom.fields, target.name)) {
      return;
    }

    validateField(dom.fields, dom.fieldErrors, target.name);
  };
}

export function createSubmitHandler(dom) {
  applyLockState(dom, getAttemptGuardState());

  return async function onSubmit(event) {
    event.preventDefault();
    clearAuthAlert(dom.authAlert);

    const currentGuardState = getAttemptGuardState();
    if (isFormLocked(currentGuardState)) {
      setFormLockedUi(dom, true);
      showAuthAlert(dom.authAlert, getLockMessage());
      return;
    }

    const isValid = validateForm(dom.fields, dom.fieldErrors);
    if (!isValid) {
      return;
    }

    setSubmittingState(dom.loader, dom.submitButton, true);
    try {
      const values = collectFieldValues(dom.fields);
      const result = await requestLogin(values);

      if (!result.ok) {
        if (result.status === 401) {
          const attemptState = registerFailedAttempt(LOGIN_SECURITY.maxAttempts, LOGIN_SECURITY.lockDurationMs);
          if (isFormLocked(attemptState)) {
            setFormLockedUi(dom, true);
            showAuthAlert(dom.authAlert, getLockMessage());
            return;
          }

          if (attemptState.failedAttempts >= LOGIN_SECURITY.warningThreshold) {
            const warningMessage = getWarningMessage(attemptState);
            if (warningMessage !== "") {
              showAuthAlert(dom.authAlert, warningMessage);
              return;
            }
          }
        }

        showAuthAlert(dom.authAlert, normalizeAuthenticationMessage(result.status, result.message));
        return;
      }

      resetAttemptGuardState();

      redirectAfterLogin(result.data?.user ?? null);
    } catch {
      showAuthAlert(dom.authAlert, "No se pudo conectar con el servidor. Verifica tu conexion.");
    } finally {
      const latestGuardState = getAttemptGuardState();
      if (isFormLocked(latestGuardState)) {
        setFormLockedUi(dom, true);
        return;
      }

      setSubmittingState(dom.loader, dom.submitButton, false);
    }
  };
}
