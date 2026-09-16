<section class="page-head">
    <div>
        <p class="eyebrow">Error</p>
        <h1 class="page-title">404</h1>
        <p class="lede">No encontramos esa ruta en la forja.</p>
    </div>
    <a class="btn btn-accent" href="<?= e(url(Auth::check() ? '/panel' : '/')) ?>">Volver</a>
</section>
