<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Reservasi Badminton' ?></title>
    <link rel="stylesheet" href="<?= $base_url ?>assets/style.css">
</head>
<body>
<header>
    <h1>🏸 Sistem Reservasi Lapangan Badminton</h1>
</header>

<nav>
    <a href="<?= $base_url ?>?c=reservation&a=index">Reservasi</a>
    <a href="<?= $base_url ?>?c=court&a=index">Lapangan</a>
    <a href="<?= $base_url ?>?c=recyclebin&a=index">Recycle Bin</a>
</nav>

<div class="container">
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>