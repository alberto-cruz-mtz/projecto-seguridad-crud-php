export function getDashboardDom() {
  return {
    sidebarButtons: Array.from(document.querySelectorAll("[data-panel-target]")),
    panels: Array.from(document.querySelectorAll("[data-panel]")),
    logoutButton: document.getElementById("logout-button"),
    feedback: document.getElementById("dashboard-feedback"),
    sidebarUserEmail: document.getElementById("sidebar-user-email"),
    summary: {
      email: document.getElementById("summary-email"),
      role: document.getElementById("summary-role"),
      id: document.getElementById("summary-id"),
    },
    payments: {
      form: document.getElementById("payment-form"),
      id: document.getElementById("payment-id"),
      studentId: document.getElementById("payment-student-id"),
      weekNumber: document.getElementById("payment-week-number"),
      amount: document.getElementById("payment-amount"),
      paymentDate: document.getElementById("payment-date"),
      submitButton: document.getElementById("payment-submit"),
      cancelButton: document.getElementById("payment-cancel"),
      tableBody: document.getElementById("payments-table-body"),
    },
  };
}
