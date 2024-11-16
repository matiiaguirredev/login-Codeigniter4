<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="card shadow-lg form-signin">
    <div class="card-body p-5">
        <h1 class="fs-4 card-title fw-bold mb-4"><?= $title; ?></h1> <!-- esto viene de la funcion que creamos showMessage -->

        <p><?= $message; ?></p> <!-- esto viene de la funcion que creamos showMessage -->

        <div class="d-flex align-items-center">
            <a href="<?= base_url(); ?>" class="btn btn-primary ms-auto">
                Iniciar sesion
            </a>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>