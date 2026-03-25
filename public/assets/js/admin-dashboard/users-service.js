import { createUser, deleteUser, fetchUsers, updateUser } from "./users-api.js";
import { ROLE_OPTIONS } from "./constants.js";
import { createActionButton, renderEmptyTableRow } from "./ui.js";
import { toNumber } from "./utils.js";

function resolveRoleLabel(user) {
  const roleNameFromApi = user.role?.name;
  if (typeof roleNameFromApi === "string" && roleNameFromApi.trim() !== "") {
    const normalizedRoleName = roleNameFromApi.toLowerCase();
    if (normalizedRoleName === "admin") {
      return ROLE_OPTIONS[1];
    }

    if (normalizedRoleName === "treasurer") {
      return ROLE_OPTIONS[2];
    }

    if (normalizedRoleName === "student") {
      return ROLE_OPTIONS[3];
    }
  }

  const roleId = toNumber(user.role_id, 0);
  if (roleId > 0 && Object.hasOwn(ROLE_OPTIONS, roleId)) {
    return ROLE_OPTIONS[roleId];
  }

  return "Sin rol";
}

function buildUserPayloadFromForm(userFormDom, includePassword = true) {
  const roleId = toNumber(userFormDom.roleId.value, 0);
  const payload = {
    email: userFormDom.email.value.trim(),
    role_id: roleId,
    first_name: userFormDom.firstName.value.trim(),
    last_name: userFormDom.lastName.value.trim(),
    age: toNumber(userFormDom.age.value, -1),
    address: userFormDom.address.value.trim(),
    phone_number: userFormDom.phoneNumber.value.trim(),
    gender: userFormDom.gender.value,
  };

  const passwordValue = userFormDom.password.value;
  if (!includePassword) {
    if (passwordValue.trim() === "") {
      return payload;
    }

    payload.password = passwordValue;
    return payload;
  }

  payload.password = passwordValue;

  return payload;
}

function resetUserForm(userFormDom) {
  userFormDom.form.reset();
  userFormDom.id.value = "";
  userFormDom.submitButton.textContent = "Guardar usuario";
}

function mapUserToEdition(userFormDom, user) {
  userFormDom.id.value = user.id ?? "";
  userFormDom.email.value = user.email ?? "";
  userFormDom.password.value = "";
  userFormDom.roleId.value = String(user.role_id ?? "");
  userFormDom.firstName.value = user.person?.first_name ?? "";
  userFormDom.lastName.value = user.person?.last_name ?? "";
  userFormDom.age.value = String(user.person?.age ?? "");
  userFormDom.address.value = user.person?.address ?? "";
  userFormDom.phoneNumber.value = user.person?.phone_number ?? "";
  userFormDom.gender.value = user.person?.gender ?? "";
  userFormDom.submitButton.textContent = "Actualizar usuario";
}

function renderUserRow(tableBody, user, onEdit, onDelete) {
  const row = document.createElement("tr");

  const emailCell = document.createElement("td");
  emailCell.textContent = user.email ?? "-";
  row.appendChild(emailCell);

  const fullNameCell = document.createElement("td");
  const firstName = user.person?.first_name ?? "";
  const lastName = user.person?.last_name ?? "";
  fullNameCell.textContent = `${firstName} ${lastName}`.trim() || "-";
  row.appendChild(fullNameCell);

  const roleCell = document.createElement("td");
  roleCell.textContent = resolveRoleLabel(user);
  row.appendChild(roleCell);

  const phoneCell = document.createElement("td");
  phoneCell.textContent = user.person?.phone_number ?? "-";
  row.appendChild(phoneCell);

  const actionsCell = document.createElement("td");
  const actionsWrap = document.createElement("div");
  actionsWrap.className = "table-actions";

  const editButton = createActionButton(
    "Editar",
    "btn btn-outline-neutral",
    () => onEdit(user),
  );
  const deleteButton = createActionButton(
    "Eliminar",
    "btn btn-outline-danger",
    () => onDelete(user),
  );

  actionsWrap.append(editButton, deleteButton);
  actionsCell.appendChild(actionsWrap);
  row.appendChild(actionsCell);

  tableBody.appendChild(row);
}

function renderUserTable(tableBody, users, onEdit, onDelete) {
  if (!tableBody) {
    return;
  }

  if (!Array.isArray(users) || users.length === 0) {
    renderEmptyTableRow(tableBody, "No hay usuarios registrados.", 5);
    return;
  }

  tableBody.innerHTML = "";
  users.forEach((user) => renderUserRow(tableBody, user, onEdit, onDelete));
}

export function createUsersService(
  userFormDom,
  tableBody,
  feedbackElement,
  showFeedback,
) {
  let usersCache = [];

  async function loadUsers() {
    const result = await fetchUsers();
    if (!result.ok) {
      showFeedback(feedbackElement, result.message, "error");
      renderEmptyTableRow(tableBody, "No fue posible cargar usuarios.", 5);
      return;
    }

    usersCache = Array.isArray(result.data) ? result.data : [];
    renderUserTable(tableBody, usersCache, startEdition, removeUser);
  }

  function startEdition(user) {
    mapUserToEdition(userFormDom, user);
  }

  function cancelEdition() {
    resetUserForm(userFormDom);
  }

  async function submitUser(event) {
    event.preventDefault();

    const isEditing = userFormDom.id.value.trim() !== "";
    const payload = buildUserPayloadFromForm(userFormDom, !isEditing);

    const result = isEditing
      ? await updateUser(userFormDom.id.value.trim(), payload)
      : await createUser(payload);

    if (!result.ok) {
      showFeedback(feedbackElement, result.message, "error");
      return;
    }

    resetUserForm(userFormDom);
    showFeedback(
      feedbackElement,
      isEditing ? "Usuario actualizado." : "Usuario creado.",
      "success",
    );
    await loadUsers();
  }

  async function removeUser(user) {
    const confirmed = window.confirm(
      `Se eliminara el usuario ${user.email ?? "seleccionado"}.`,
    );
    if (!confirmed) {
      return;
    }

    const userId = user.id ?? "";
    if (userId === "") {
      showFeedback(
        feedbackElement,
        "No se pudo identificar el usuario.",
        "error",
      );
      return;
    }

    const result = await deleteUser(userId);
    if (!result.ok) {
      showFeedback(feedbackElement, result.message, "error");
      return;
    }

    showFeedback(
      feedbackElement,
      "Usuario eliminado correctamente.",
      "success",
    );
    await loadUsers();
  }

  function bindEvents() {
    if (userFormDom.form) {
      userFormDom.form.addEventListener("submit", submitUser);
    }

    if (userFormDom.cancelButton) {
      userFormDom.cancelButton.addEventListener("click", cancelEdition);
    }
  }

  return {
    init: async () => {
      bindEvents();
      await loadUsers();
    },
  };
}
