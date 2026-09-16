<?php
/** @var array $user */
$avatar = avatar_url($user['avatar'] ?? null);
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Cuenta</p>
        <h1 class="page-title">Configuración</h1>
        <p class="lede">Perfil, foto y seguridad de tu cuenta.</p>
    </div>
</section>

<div class="settings-grid">
    <section class="panel">
        <h2>Perfil</h2>
        <form class="stack-form" method="post" action="<?= e(url('/configuracion')) ?>">
            <?= csrf_field() ?>
            <label class="field"><span>Nombre</span><input type="text" name="name" required value="<?= e($user['name'] ?? '') ?>"></label>
            <label class="field"><span>Correo</span><input type="email" value="<?= e($user['email'] ?? '') ?>" disabled></label>
            <label class="field"><span>Tema</span>
                <select name="theme">
                    <?php foreach (['system' => 'Sistema', 'light' => 'Claro', 'dark' => 'Oscuro'] as $k => $l): ?>
                        <option value="<?= e($k) ?>" <?= ($user['theme'] ?? 'system') === $k ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-accent" type="submit">Guardar perfil</button>
        </form>
    </section>

    <section class="panel">
        <h2>Foto de perfil</h2>
        <div class="avatar-editor">
            <div class="avatar-preview avatar-lg" aria-hidden="true">
                <?php if ($avatar): ?>
                    <img src="<?= e($avatar) ?>" alt="">
                <?php else: ?>
                    <span><?= e(user_initials($user['name'] ?? '')) ?></span>
                <?php endif; ?>
            </div>
            <div class="avatar-editor-actions">
                <form class="stack-form" method="post" action="<?= e(url('/configuracion/avatar')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label class="field">
                        <span>Imagen (JPG, PNG, WEBP o GIF · máx. 2 MB)</span>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" required>
                    </label>
                    <button class="btn btn-accent" type="submit">Subir foto</button>
                </form>
                <?php if ($avatar): ?>
                    <form method="post" action="<?= e(url('/configuracion/avatar/eliminar')) ?>" onsubmit="return confirm('¿Quitar la foto de perfil?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost" type="submit">Quitar foto</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel settings-span">
        <h2>Cambiar contraseña</h2>
        <form class="stack-form" method="post" action="<?= e(url('/configuracion/password')) ?>">
            <?= csrf_field() ?>
            <div class="form-grid-2">
                <label class="field">
                    <span>Contraseña actual</span>
                    <input type="password" name="current_password" required autocomplete="current-password">
                </label>
                <div></div>
                <label class="field">
                    <span>Nueva contraseña</span>
                    <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
                </label>
                <label class="field">
                    <span>Confirmar nueva</span>
                    <input type="password" name="new_password_confirm" required minlength="8" autocomplete="new-password">
                </label>
            </div>
            <button class="btn btn-accent" type="submit">Actualizar contraseña</button>
        </form>
    </section>
</div>
