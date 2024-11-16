<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<h1>BIENVENIDO</h1>

<a href="<?= base_url('logout') ;?>" class="btn btn-primary btn-sm">Cerrar sesion</a>

<?= $this->endSection('content'); ?>