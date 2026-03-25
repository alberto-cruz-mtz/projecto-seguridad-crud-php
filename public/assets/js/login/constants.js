export const LOGIN_ENDPOINT = "/api/auth/login";

export const FIELD_NAMES = {
  email: "email",
  password: "password",
};

export const AUTH_ERROR_MESSAGE = "Credenciales incorrectas o usuario no registrado.";

export const LOGIN_SECURITY = {
  maxAttempts: 5,
  warningThreshold: 3,
  lockDurationMs: 15 * 60 * 1000,
};
