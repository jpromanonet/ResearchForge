# ResearchForge

Laboratorio de investigación para organizar **preguntas**, **fuentes**, **afirmaciones**, **citas**, **evidencias** y **conclusiones**, y exportar **dossiers** (Markdown, HTML, JSON).

Stack alineado a CultureGraph / LifeQuest: **PHP 8 + MySQL + HTML/CSS/JS** sin frameworks.

## Requisitos

- PHP 8.1+
- MySQL 5.7+ / 8.x
- Extensiones: `pdo_mysql`, `mbstring`

## Instalación

1. Copiá `.env.example` a `.env` y ajustá credenciales.
2. Creá la base (opción A o B):

```bash
# Opción A — instalador web
# Abrí http://localhost/.../ResearchForge/install.php
```

```sql
-- Opción B — manual
CREATE DATABASE researchforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Serví la carpeta con Apache/nginx o:

```bash
php -S localhost:8080
```

4. Abrí la app, **registrate** y empezá una investigación.

## Paleta

- Tinta `#1A2332`
- Papel `#F3EFE6`
- Cobre forja `#B86B3A`
- Evidencia `#2F6B5A`
- Contradicción `#9B3A3A`
- Fuente `#3A5F8A`

## MVP incluido

- Login / registro / logout (sesiones + CSRF + rate limit)
- Investigaciones con tabs
- CRUD completo del núcleo epistémico
- Dossiers con selección de piezas
- Export MD / HTML / JSON
- Búsqueda global, tema claro/oscuro, configuración de perfil
