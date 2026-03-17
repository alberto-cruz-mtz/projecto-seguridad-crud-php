const EMAIL_REGEX = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

/**
 * @param {string} value - El valor del nombre de usuario a validar.
 * */
export function validateUsername(value) {
  if (!value) return "La dirección de correo electrónico es obligatoria.";

  if (!EMAIL_REGEX.test(value)) {
    return "Ingresa una dirección de correo electrónico válida.";
  }

  if (value.length > 150)
    return "La dirección de correo electrónico no puede tener más de 150 caracteres.";

  return null;
}

/**
 * @param {string} value - El valor de la contraseña a validar.
 * */
export function validatePassword(value) {
  if (!value) return "La contraseña es obligatoria.";

  if (value.length < 8 || value.length > 18) {
    return "La contraseña debe tener entre 8 y 18 caracteres.";
  }

  return null;
}
