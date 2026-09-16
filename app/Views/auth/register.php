<div class="auth-split">
    <section class="auth-brand" aria-label="ResearchForge">
        <div class="auth-brand-inner">
            <?= icon('forge', 48) ?>
            <h1 class="auth-brand-title">ResearchForge</h1>
            <p class="auth-brand-tagline">Tu mesa de investigación</p>
            <p class="auth-brand-copy">Creá una cuenta para organizar evidencia y exportar dossiers.</p>
        </div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-card">
            <h2>Crear cuenta</h2>
            <form method="post" action="<?= e(url('/registro')) ?>" class="stack-form">
                <?= csrf_field() ?>
                <label class="field">
                    <span>Nombre</span>
                    <input type="text" name="name" required autocomplete="name" placeholder="Tu nombre" minlength="2">
                </label>
                <label class="field">
                    <span>Correo</span>
                    <input type="email" name="email" required autocomplete="email" placeholder="tu@correo.com">
                </label>
                <label class="field">
                    <span>Contraseña</span>
                    <input type="password" name="password" required autocomplete="new-password" minlength="8" placeholder="Mínimo 8 caracteres">
                </label>
                <label class="field">
                    <span>Confirmar contraseña</span>
                    <input type="password" name="password_confirm" required autocomplete="new-password" minlength="8">
                </label>
                <button type="submit" class="btn btn-accent btn-block">Registrarme</button>
            </form>
            <p class="auth-switch">¿Ya tenés cuenta? <a href="<?= e(url('/login')) ?>">Iniciá sesión</a></p>
        </div>
    </section>
</div>
