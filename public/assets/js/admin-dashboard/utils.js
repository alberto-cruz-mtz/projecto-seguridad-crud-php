export function safeText(value, fallback = "-") {
  if (typeof value !== "string") {
    return fallback;
  }

  const trimmedValue = value.trim();
  if (trimmedValue === "") {
    return fallback;
  }

  return trimmedValue;
}

export function formatDateForInput(date = new Date()) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

export function generateLocalEntityId(prefix) {
  const timestamp = Date.now();
  const random = Math.floor(Math.random() * 1000000);

  return `${prefix}_${timestamp}_${random}`;
}

export function parseJsonSafe(rawValue, fallbackValue) {
  if (typeof rawValue !== "string" || rawValue.trim() === "") {
    return fallbackValue;
  }

  try {
    return JSON.parse(rawValue);
  } catch {
    return fallbackValue;
  }
}

export function toNumber(value, fallback = 0) {
  const parsed = Number(value);
  if (Number.isNaN(parsed)) {
    return fallback;
  }

  return parsed;
}
