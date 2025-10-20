<?php

/**
 * =====================================================
 * Fichier : includes/navbar_abonne.php
 * =====================================================
 */
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/reforme-center/public/abonne/dashboard.php">🏋️‍♀️ Mon Espace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAbonne">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAbonne">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/abonne/progression.php">📈 Progression</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/abonne/objectifs.php">🎯 Objectifs</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/abonne/seances.php">🏃‍♂️ Séances</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/abonne/notifications.php">🔔 Notifications</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/abonne/profil.php">👤 Mon Profil</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-danger" href="/reforme-center/public/logout.php">🚪 Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>