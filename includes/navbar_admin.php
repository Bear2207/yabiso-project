<?php

/**
 * =====================================================
 * Fichier : includes/navbar_admin.php
 * =====================================================
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/reforme-center/public/admin/dashboard.php">🏋️‍♂️ ReformeCenter Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/abonnes/liste.php">👥 Abonnés</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/abonnements/liste.php">📅 Abonnements</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/paiements/liste.php">💰 Paiements</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/progressions/liste.php">📈 Progression</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/notifications/liste.php">🔔 Notifications</a></li>
                <li class="nav-item"><a class="nav-link" href="/reforme-center/public/admin/objectifs/liste.php">🎯 Objectifs</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-danger" href="/reforme-center/public/logout.php">🚪 Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>