<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Care System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link href="<?= $base_url ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <a class="navbar-brand" href="<?= $base_url ?>/index.php">
            City Care Complaint Management System
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto">

                <a class="nav-link" href="<?= $base_url ?>/index.php">
                    Home
                </a>

                <a class="nav-link" href="<?= $base_url ?>/track.php">
                    Track Complaint
                </a>

                <a class="nav-link" href="<?= $base_url ?>/user/register.php">
                    Register
                </a>

                <a class="nav-link" href="<?= $base_url ?>/user/login.php">
                    Citizen Login
                </a>
            </div>
        </div>

    </div>
</nav>

<div class="container">