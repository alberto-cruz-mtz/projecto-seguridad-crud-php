export function renderEmailError(emailInput, emailError, message) {
  const hasError = message !== "";

  if (emailInput) {
    emailInput.classList.toggle("input-error", hasError);
    emailInput.setAttribute("aria-invalid", String(hasError));
  }

  if (emailError) {
    emailError.textContent = message;
  }
}

export function clearMessage(messageElement) {
  if (!messageElement) {
    return;
  }

  messageElement.textContent = "";
  messageElement.classList.remove("is-visible", "is-success", "is-error");
}

export function showSuccessMessage(messageElement, message) {
  if (!messageElement) {
    return;
  }

  messageElement.textContent = message;
  messageElement.classList.add("is-visible", "is-success");
  messageElement.classList.remove("is-error");
}

export function showErrorMessage(messageElement, message) {
  if (!messageElement) {
    return;
  }

  messageElement.textContent = message;
  messageElement.classList.add("is-visible", "is-error");
  messageElement.classList.remove("is-success");
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
