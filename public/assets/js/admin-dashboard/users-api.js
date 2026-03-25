import { USERS_ENDPOINT } from "./constants.js";

function parseJsonResponseSafe(response) {
  return response.json().catch(() => null);
}

function normalizeMessage(payload, fallbackMessage) {
  if (!payload || typeof payload !== "object") {
    return fallbackMessage;
  }

  if (typeof payload.message === "string" && payload.message.trim() !== "") {
    return payload.message;
  }

  return fallbackMessage;
}

async function sendJsonRequest(url, method, payload = null) {
  const fetchOptions = {
    method,
    headers: {
      Accept: "application/json",
    },
  };

  if (payload !== null) {
    fetchOptions.headers["Content-Type"] = "application/json";
    fetchOptions.body = JSON.stringify(payload);
  }

  const response = await fetch(url, fetchOptions);
  const body = await parseJsonResponseSafe(response);

  return {
    ok: response.ok,
    status: response.status,
    message: normalizeMessage(body, "Operacion no completada."),
    data: body,
  };
}

export function fetchUsers() {
  return sendJsonRequest(USERS_ENDPOINT, "GET");
}

export function createUser(payload) {
  return sendJsonRequest(USERS_ENDPOINT, "POST", payload);
}

export function updateUser(userId, payload) {
  return sendJsonRequest(
    `${USERS_ENDPOINT}/${encodeURIComponent(userId)}`,
    "PUT",
    payload,
  );
}

export function deleteUser(userId) {
  return sendJsonRequest(
    `${USERS_ENDPOINT}/${encodeURIComponent(userId)}`,
    "DELETE",
  );
}
