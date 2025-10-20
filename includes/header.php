<?php

/**
 * =====================================================
 * Fichier : includes/header.php
 * Description : En-tête HTML + lien vers CSS
 * =====================================================
 */
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reforme Center - <?= $page_title ?? 'Tableau de bord' ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Styles personnalisés -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>

<body class="app-container">