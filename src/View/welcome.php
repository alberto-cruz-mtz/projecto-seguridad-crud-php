<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/welcome.css">
    <title>Bienvenido</title>
</head>
<body>

<header id="topbar">
    <span id="topbar-brand">Panel de usuario</span>
    <form method="POST" action="/auth/logout">
        <button type="submit" id="btn-logout">Cerrar sesión</button>
    </form>
</header>

<main id="dashboard">

    <section id="welcome-heading">
        <h1>¡Bienvenido, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p>Aquí puedes ver la información asociada a tu cuenta.</p>
    </section>

    <article id="user-card">
        <div id="user-card-avatar">
            <?= htmlspecialchars(mb_strtoupper(mb_substr($displayName, 0, 1), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div id="user-card-body">
            <h2 id="user-card-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></h2>

            <ul id="user-card-details">
                <li class="detail-item">
                    <span class="detail-label">Correo electrónico</span>
                    <span class="detail-value"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
                </li>
                <li class="detail-item">
                    <span class="detail-label">Rol</span>
                    <span class="detail-value detail-badge"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            </ul>
        </div>
    </article>

</main>

</body>
</html>
