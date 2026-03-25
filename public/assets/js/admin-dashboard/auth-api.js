import { AUTH_LOGOUT_ENDPOINT, AUTH_ME_ENDPOINT } from "./constants.js";

function parseJsonResponseSafe(response) {
  return response.json().catch(() => null);
}

function normalizeApiMessage(payload, fallbackMessage) {
  if (!payload || typeof payload !== "object") {
    return fallbackMessage;
  }

  if (typeof payload.message !== "string" || payload.message.trim() === "") {
    return fallbackMessage;
  }

  return payload.message;
}

export async function fetchAuthenticatedUser() {
  const response = await fetch(AUTH_ME_ENDPOINT, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  const payload = await parseJsonResponseSafe(response);
  if (!response.ok) {
    return {
      ok: false,
      message: normalizeApiMessage(payload, "No se pudo obtener la sesion activa."),
      data: null,
    };
  }

  return {
    ok: true,
    message: "",
    data: payload,
  };
}

export async function requestLogout() {
  const response = await fetch(AUTH_LOGOUT_ENDPOINT, {
    method: "POST",
    headers: {
      Accept: "application/json",
    },
  });

  const payload = await parseJsonResponseSafe(response);
  if (!response.ok) {
    return {
      ok: false,
      message: normalizeApiMessage(payload, "No se pudo cerrar sesion."),
    };
  }

  return {
    ok: true,
    message: normalizeApiMessage(payload, "Sesion cerrada correctamente."),
  };
}
