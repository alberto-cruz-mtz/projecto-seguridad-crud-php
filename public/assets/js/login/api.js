import { LOGIN_ENDPOINT } from "./constants.js";

function buildPayload(values) {
  return {
    username: values.email,
    password: values.password,
  };
}

function readErrorMessage(data) {
  if (!data || typeof data !== "object") {
    return "";
  }

  if (typeof data.message !== "string") {
    return "";
  }

  return data.message;
}

export async function requestLogin(values) {
  const response = await fetch(LOGIN_ENDPOINT, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(buildPayload(values)),
  });

  let data = null;
  try {
    data = await response.json();
  } catch {
    data = null;
  }

  return {
    ok: response.ok,
    status: response.status,
    message: readErrorMessage(data),
    data,
  };
}
