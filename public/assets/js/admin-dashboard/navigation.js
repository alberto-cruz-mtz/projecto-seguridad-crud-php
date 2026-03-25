import { PANELS } from "./constants.js";
import { setActivePanel } from "./ui.js";

function resolvePanel(panelName) {
  if (!Object.values(PANELS).includes(panelName)) {
    return PANELS.usuarios;
  }

  return panelName;
}

export function bindDashboardNavigation(sidebarButtons, panels, defaultPanel = PANELS.usuarios) {
  const activePanel = resolvePanel(defaultPanel);
  setActivePanel(sidebarButtons, panels, activePanel);

  sidebarButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetPanel = resolvePanel(button.dataset.panelTarget ?? "");
      setActivePanel(sidebarButtons, panels, targetPanel);
    });
  });
}
