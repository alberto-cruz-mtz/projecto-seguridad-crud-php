import { fetchAuthenticatedUser, requestLogout } from "../admin-dashboard/auth-api.js";
import { bindDashboardNavigation } from "../admin-dashboard/navigation.js";
import { createPaymentsService } from "../admin-dashboard/payments-service.js";
import { PANELS } from "../admin-dashboard/constants.js";
import { setSidebarUserEmail, setSummaryInfo, showFeedback } from "../admin-dashboard/ui.js";
import { getDashboardDom } from "./dom.js";

function redirectToLogin() {
  window.location.assign("/login");
}

function normalizeCurrentUser(data) {
  if (!data || typeof data !== "object") {
    return null;
  }

  if (!data.user || typeof data.user !== "object") {
    return null;
  }

  return data.user;
}

function bindLogout(logoutButton, feedbackElement) {
  if (!logoutButton) {
    return;
  }

  logoutButton.addEventListener("click", async () => {
    const result = await requestLogout();
    if (!result.ok) {
      showFeedback(feedbackElement, result.message, "error");
      return;
    }

    redirectToLogin();
  });
}

async function initializeDashboard() {
  const dom = getDashboardDom();
  if (!dom.panels.length || !dom.sidebarButtons.length) {
    return;
  }

  const authResult = await fetchAuthenticatedUser();
  if (!authResult.ok) {
    redirectToLogin();
    return;
  }

  const currentUser = normalizeCurrentUser(authResult.data);
  if (currentUser === null) {
    redirectToLogin();
    return;
  }

  setSummaryInfo(dom.summary, currentUser);
  setSidebarUserEmail(dom.sidebarUserEmail, currentUser.email ?? "");

  bindDashboardNavigation(dom.sidebarButtons, dom.panels, PANELS.pagos);
  bindLogout(dom.logoutButton, dom.feedback);

  const paymentsService = createPaymentsService(dom.payments, dom.payments.tableBody, dom.feedback, showFeedback);
  paymentsService.init();
}

document.addEventListener("DOMContentLoaded", () => {
  initializeDashboard().catch(() => {
    window.location.assign("/login");
  });
});
