export function getPasswordResetRequestDom() {
  return {
    form: document.getElementById("reset-request-form"),
    emailInput: document.getElementById("reset-email"),
    emailError: document.getElementById("reset-email-error"),
    submitButton: document.getElementById("reset-submit"),
    loader: document.getElementById("reset-request-loader"),
    message: document.getElementById("reset-request-message"),
  };
}
