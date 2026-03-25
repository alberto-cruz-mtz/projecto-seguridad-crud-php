import { getPasswordResetRequestDom } from "./dom.js";
import { createBlurHandler, createSubmitHandler } from "./handlers.js";

function initPasswordResetRequestView() {
  const dom = getPasswordResetRequestDom();
  if (!dom.form) {
    return;
  }

  dom.form.addEventListener("blur", createBlurHandler(dom), true);
  dom.form.addEventListener("submit", createSubmitHandler(dom));
}

document.addEventListener("DOMContentLoaded", initPasswordResetRequestView);
