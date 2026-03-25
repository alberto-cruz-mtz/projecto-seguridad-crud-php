import { fetchAuthenticatedUser, requestLogout } from "../admin-dashboard/auth-api.js";
import { setSidebarUserEmail, setSummaryInfo, showFeedback } from "../admin-dashboard/ui.js";

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

async function initializeStudentDashboard() {
  const logoutButton = document.getElementById("logout-button");
  const feedbackElement = document.getElementById("dashboard-feedback");
  const sidebarUserEmail = document.getElementById("sidebar-user-email");

  const summary = {
    email: document.getElementById("summary-email"),
    role: document.getElementById("summary-role"),
    id: document.getElementById("summary-id"),
  };

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

  setSummaryInfo(summary, currentUser);
  setSidebarUserEmail(sidebarUserEmail, currentUser.email ?? "");
  bindLogout(logoutButton, feedbackElement);
}

document.addEventListener("DOMContentLoaded", () => {
  initializeStudentDashboard().catch(() => {
    redirectToLogin();
  });
});
