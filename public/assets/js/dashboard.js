const TAB_ACTIVE_CLASS = "tab-menu__tab--active";
const PANEL_ACTIVE_CLASS = "tab-menu__panel--active";
const SIDEBAR_COLLAPSED_CLASS = "is-collapsed";

const updateSidebarToggleLabel = (toggleButton, isCollapsed) => {
    const toggleText = toggleButton.querySelector(".sidebar__toggle-text");

    toggleButton.setAttribute("aria-expanded", String(!isCollapsed));

    if (toggleText) {
        toggleText.textContent = isCollapsed ? "Expandir menu" : "Contraer menu";
    }
};

const initializeSidebarToggle = (dashboardLayout, sidebarToggleButton) => {
    if (!dashboardLayout || !sidebarToggleButton) {
        return;
    }

    sidebarToggleButton.addEventListener("click", () => {
        const isSidebarCollapsed = dashboardLayout.classList.toggle(SIDEBAR_COLLAPSED_CLASS);
        updateSidebarToggleLabel(sidebarToggleButton, isSidebarCollapsed);
    });
};

const deactivateAllTabs = (tabButtons) => {
    tabButtons.forEach((tabButton) => {
        tabButton.classList.remove(TAB_ACTIVE_CLASS);
        tabButton.setAttribute("aria-selected", "false");
    });
};

const hideAllTabPanels = (tabPanels) => {
    tabPanels.forEach((tabPanel) => {
        tabPanel.classList.remove(PANEL_ACTIVE_CLASS);
    });
};

const activateTabAndPanel = (selectedTabButton, tabButtons, tabPanels) => {
    deactivateAllTabs(tabButtons);
    hideAllTabPanels(tabPanels);

    selectedTabButton.classList.add(TAB_ACTIVE_CLASS);
    selectedTabButton.setAttribute("aria-selected", "true");

    const targetPanelId = selectedTabButton.getAttribute("data-target");
    if (!targetPanelId) {
        return;
    }

    const targetPanel = document.getElementById(targetPanelId);
    if (!targetPanel) {
        return;
    }

    targetPanel.classList.add(PANEL_ACTIVE_CLASS);
};

const initializeTabs = (tabButtons, tabPanels) => {
    if (!tabButtons.length || !tabPanels.length) {
        return;
    }

    tabButtons.forEach((tabButton) => {
        tabButton.addEventListener("click", () => {
            activateTabAndPanel(tabButton, tabButtons, tabPanels);
        });
    });
};

const initializeDashboard = () => {
    const dashboardLayout = document.getElementById("dashboardLayout");
    const sidebarToggleButton = document.getElementById("sidebarToggle");
    const tabButtons = document.querySelectorAll(".tab-menu__tab");
    const tabPanels = document.querySelectorAll(".tab-menu__panel");

    initializeSidebarToggle(dashboardLayout, sidebarToggleButton);
    initializeTabs(tabButtons, tabPanels);
};

document.addEventListener("DOMContentLoaded", initializeDashboard);
