function setInputErrorState(input, hasError) {
  if (!input) {
    return;
  }

  input.classList.toggle("input-error", hasError);
  input.setAttribute("aria-invalid", String(hasError));
}

export function renderFieldError(input, errorElement, message) {
  const hasError = message !== "";
  setInputErrorState(input, hasError);

  if (!errorElement) {
    return;
  }

  errorElement.textContent = message;
}

export function clearAuthAlert(authAlert) {
  if (!authAlert) {
    return;
  }

  authAlert.textContent = "";
  authAlert.classList.remove("is-visible");
}

export function showAuthAlert(authAlert, message) {
  if (!authAlert) {
    return;
  }

  authAlert.textContent = message;
  authAlert.classList.add("is-visible");
}

export function setSubmittingState(loader, submitButton, isSubmitting) {
  if (loader) {
    loader.classList.toggle("is-active", isSubmitting);
    loader.setAttribute("aria-hidden", String(!isSubmitting));
  }

  if (submitButton) {
    submitButton.disabled = isSubmitting;
  }
}
