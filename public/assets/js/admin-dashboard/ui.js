import { FEEDBACK_VARIANTS } from "./constants.js";
import { safeText } from "./utils.js";

let feedbackTimeoutId = null;

function clearFeedbackClasses(feedbackElement) {
  feedbackElement.classList.remove("is-success", "is-error", "is-visible");
}

export function showFeedback(feedbackElement, message, variant = FEEDBACK_VARIANTS.success) {
  if (!feedbackElement) {
    return;
  }

  if (feedbackTimeoutId !== null) {
    window.clearTimeout(feedbackTimeoutId);
    feedbackTimeoutId = null;
  }

  clearFeedbackClasses(feedbackElement);
  feedbackElement.textContent = safeText(message, "Operacion completada.");

  if (variant === FEEDBACK_VARIANTS.error) {
    feedbackElement.classList.add("is-error");
  } else {
    feedbackElement.classList.add("is-success");
  }

  feedbackElement.classList.add("is-visible");

  feedbackTimeoutId = window.setTimeout(() => {
    clearFeedbackClasses(feedbackElement);
    feedbackElement.textContent = "";
  }, 2800);
}

export function setSummaryInfo(summaryDom, user) {
  if (!summaryDom || !user || typeof user !== "object") {
    return;
  }

  const roleName = safeText(user.role?.name ?? "", "Sin rol");

  summaryDom.email.textContent = safeText(user.email ?? "", "-");
  summaryDom.role.textContent = roleName;
  summaryDom.id.textContent = safeText(user.id ?? "", "-");
}

export function setSidebarUserEmail(emailElement, value) {
  if (!emailElement) {
    return;
  }

  emailElement.textContent = safeText(value, "admin@dashboard.local");
}

export function setActivePanel(sidebarButtons, panels, panelName) {
  if (!Array.isArray(sidebarButtons) || !Array.isArray(panels)) {
    return;
  }

  sidebarButtons.forEach((button) => {
    const targetPanel = button.dataset.panelTarget;
    button.classList.toggle("is-active", targetPanel === panelName);
  });

  panels.forEach((panelElement) => {
    const panelKey = panelElement.dataset.panel;
    panelElement.classList.toggle("is-hidden", panelKey !== panelName);
  });
}

export function createActionButton(label, className, onClickHandler) {
  const button = document.createElement("button");
  button.type = "button";
  button.className = className;
  button.textContent = label;
  button.addEventListener("click", onClickHandler);

  return button;
}

export function renderEmptyTableRow(tableBody, message, colSpan) {
  if (!tableBody) {
    return;
  }

  tableBody.innerHTML = "";

  const row = document.createElement("tr");
  const cell = document.createElement("td");
  cell.colSpan = colSpan;
  cell.textContent = message;
  row.appendChild(cell);
  tableBody.appendChild(row);
}
