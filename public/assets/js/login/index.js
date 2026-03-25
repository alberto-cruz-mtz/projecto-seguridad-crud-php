import { getLoginDom } from "./dom.js";
import { createBlurHandler, createSubmitHandler } from "./handlers.js";

function initLoginView() {
  const dom = getLoginDom();
  if (!dom.form) {
    return;
  }

  dom.form.addEventListener("blur", createBlurHandler(dom), true);
  dom.form.addEventListener("submit", createSubmitHandler(dom));
}

document.addEventListener("DOMContentLoaded", initLoginView);
