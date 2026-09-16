<section class="landing-hero">
    <nav class="landing-nav">
        <a class="brand brand-lg" href="<?= e(url('/')) ?>">
            <?= icon('forge', 28) ?>
            <span class="brand-text">ResearchForge</span>
        </a>
        <div class="landing-nav-actions">
            <a class="btn btn-ghost" href="<?= e(url('/login')) ?>">Entrar</a>
            <a class="btn btn-accent" href="<?= e(url('/registro')) ?>">Registrarse</a>
        </div>
    </nav>

    <div class="landing-stage">
        <p class="eyebrow landing-eyebrow">Laboratorio de evidencia</p>
        <h1 class="landing-brand">ResearchForge</h1>
        <p class="landing-lede">Organizá preguntas, fuentes, afirmaciones, citas y evidencias. Forjá conclusiones trazables y exportá dossiers listos para compartir.</p>
        <div class="landing-cta">
            <a class="btn btn-accent btn-lg" href="<?= e(url('/registro')) ?>">Abrir la forja</a>
            <a class="btn btn-lg" href="<?= e(url('/login')) ?>">Ya tengo cuenta</a>
        </div>
    </div>

    <div class="landing-visual" aria-hidden="true">
        <svg class="forge-scene" viewBox="0 0 960 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="40" y="220" width="880" height="18" fill="#1A2332"/>
            <path d="M180 220 V120 H320 V220" stroke="#3A5F8A" stroke-width="10"/>
            <path d="M360 220 V80 H520 V220" stroke="#2F6B5A" stroke-width="10"/>
            <path d="M560 220 V140 H720 V220" stroke="#B86B3A" stroke-width="10"/>
            <circle cx="250" cy="70" r="18" fill="#B86B3A" class="ember ember-a"/>
            <circle cx="440" cy="40" r="14" fill="#9B3A3A" class="ember ember-b"/>
            <circle cx="640" cy="90" r="16" fill="#2F6B5A" class="ember ember-c"/>
            <path d="M120 180 H840" stroke="#C9C2B5" stroke-width="2" stroke-dasharray="6 8"/>
        </svg>
    </div>
</section>

<section class="landing-section">
    <h2>Del hallazgo al dossier</h2>
    <p class="lede">Cada pieza queda vinculada: de la conclusión a la página de la fuente.</p>
    <div class="feature-strip">
        <article>
            <h3>Preguntas</h3>
            <p>Definí qué querés responder y medí cobertura.</p>
        </article>
        <article>
            <h3>Evidencia</h3>
            <p>Afirmaciones con citas, a favor y en contra.</p>
        </article>
        <article>
            <h3>Dossiers</h3>
            <p>Ensamblá y exportá Markdown, HTML o JSON.</p>
        </article>
    </div>
</section>
