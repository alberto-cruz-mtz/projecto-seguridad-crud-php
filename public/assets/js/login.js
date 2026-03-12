const authenticationForm = document.getElementById("login-form");
const submitButton       = document.getElementById("btn-submit");
const usernameError      = document.getElementById("username-error");
const passwordError      = document.getElementById("password-error");

authenticationForm.addEventListener("submit", (event) => {
    event.preventDefault();

    clearValidationErrors();

    const { username, password } = event.target;
    const credentials = { username: username.value, password: password.value };

    authenticateUser(credentials);
});

async function authenticateUser(credentials) {
    setFormLoadingState(true);

    try {
        const response = await fetch("/auth/login", {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(credentials),
        });

        const data = await response.json();

        if (!response.ok) {
            displayAuthenticationError(data.error ?? "Error al iniciar sesión.");
            return;
        }

        window.location.href = "/dashboard";

    } catch {
        displayAuthenticationError("No se pudo conectar con el servidor. Intenta de nuevo.");
    } finally {
        setFormLoadingState(false);
    }
}

function displayAuthenticationError(message) {
    passwordError.textContent = message;
}

function clearValidationErrors() {
    usernameError.textContent = "";
    passwordError.textContent = "";
}

function setFormLoadingState(isLoading) {
    submitButton.disabled    = isLoading;
    submitButton.textContent = isLoading ? "Verificando..." : "Iniciar sesión";
}
