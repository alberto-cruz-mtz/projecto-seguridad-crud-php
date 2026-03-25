const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function validateEmail(value) {
  const trimmedValue = value.trim();
  if (trimmedValue === "") {
    return "El correo es obligatorio.";
  }

  if (!EMAIL_REGEX.test(trimmedValue)) {
    return "Ingresa un correo electronico valido.";
  }

  return "";
}
