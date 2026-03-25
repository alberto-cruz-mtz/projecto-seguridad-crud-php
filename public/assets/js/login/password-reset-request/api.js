import { PASSWORD_RESET_REQUEST_ENDPOINT } from "./constants.js";

function getMessage(data) {
  if (!data || typeof data !== "object") {
    return "";
  }

  if (typeof data.message !== "string") {
    return "";
  }

  return data.message;
}

export async function requestPasswordReset(email) {
  const response = await fetch(PASSWORD_RESET_REQUEST_ENDPOINT, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email }),
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
    message: getMessage(data),
  };
}
