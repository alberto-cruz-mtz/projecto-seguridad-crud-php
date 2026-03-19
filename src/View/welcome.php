<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/pages/welcome.css">
    <title>Bienvenido</title>
</head>
<body>

<header id="topbar" class="welcome-topbar">
    <span id="topbar-brand" class="welcome-topbar__brand">Panel de usuario</span>
    <form method="POST" action="/auth/logout">
        <button type="submit" id="btn-logout" class="btn btn-outline-danger">Cerrar sesión</button>
    </form>
</header>

<main id="dashboard" class="welcome-dashboard">

    <section id="welcome-heading" class="welcome-heading">
        <h1>¡Bienvenido, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p>Aquí puedes ver la información asociada a tu cuenta.</p>
    </section>

    <article id="user-card" class="card profile-card card-elevated">
        <div id="user-card-avatar" class="profile-card__avatar">
            <?= htmlspecialchars(mb_strtoupper(mb_substr($displayName, 0, 1), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div id="user-card-body" class="profile-card__body">
            <h2 id="user-card-name" class="profile-card__name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></h2>

            <ul id="user-card-details" class="profile-details">
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
