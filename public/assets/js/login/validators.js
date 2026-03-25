const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function getRequiredMessage(label) {
  return `El campo ${label} es obligatorio.`;
}

function validateEmail(value) {
  const trimmedValue = value.trim();
  if (trimmedValue === "") {
    return getRequiredMessage("correo");
  }

  if (!EMAIL_REGEX.test(trimmedValue)) {
    return "Ingresa un correo electronico valido.";
  }

  return "";
}

function validatePassword(value) {
  if (value === "") {
    return getRequiredMessage("contrasena");
  }

  if (value.length < 8) {
    return "La contrasena debe tener al menos 8 caracteres.";
  }

  return "";
}

export function validateByName(name, value) {
  if (name === "email") {
    return validateEmail(value);
  }

  if (name === "password") {
    return validatePassword(value);
  }

  return "";
}
