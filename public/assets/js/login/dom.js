import { FIELD_NAMES } from "./constants.js";

export function getLoginDom() {
  return {
    form: document.getElementById("login-form"),
    submitButton: document.getElementById("submit-login"),
    loader: document.getElementById("login-loader"),
    authAlert: document.getElementById("auth-alert"),
    fields: {
      [FIELD_NAMES.email]: document.getElementById("email"),
      [FIELD_NAMES.password]: document.getElementById("password"),
    },
    fieldErrors: {
      [FIELD_NAMES.email]: document.getElementById("email-error"),
      [FIELD_NAMES.password]: document.getElementById("password-error"),
    },
  };
}
