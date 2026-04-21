<?php
/**
 * Template Name: Articolo – Bomboniere 2026
 * Template Post Type: page
 *
 * Pagina template per l'articolo editoriale
 * "Ha senso fare ancora le bomboniere nel 2026?"
 *
 * ISTRUZIONI DI INSTALLAZIONE
 * ─────────────────────────────────────────────────────────────────────────────
 * 1. Copia questo file nella cartella del tuo tema attivo:
 *    wp-content/themes/NOME-TEMA/page-bomboniere-2026.php
 *
 * 2. Dal pannello WordPress → Pagine → Aggiungi nuova:
 *    - Titolo: "Ha senso fare ancora le bomboniere nel 2026?"
 *    - Slug suggerito: bomboniere-2026
 *    - Attributi pagina → Template: scegli "Articolo – Bomboniere 2026"
 *    - Pubblica
 *
 * 3. (Facoltativo) Per usare le immagini dei prodotti reali Albalù,
 *    sostituisci gli src="" delle <img> con gli URL esatti del tuo
 *    Media Library WordPress oppure delle pagine prodotto WooCommerce.
 *
 * 4. Il font Google (Playfair Display + Nunito) viene caricato via
 *    wp_enqueue_style inline — nessuna modifica al functions.php richiesta.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// Carica i Google Font tramite WordPress (buona pratica SEO/performance)
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'albalu-article-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Nunito:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
}, 20 );

get_header();
?>

<!–– ═══════════════════════════════════════════════════════════
     STILI SCOPED — prefisso .albalu-art per non interferire
     con il tema Albalù esistente
     ═══════════════════════════════════════════════════════════ -->
<style>
/* ─── RESET SCOPED ─────────────────────────────────────────── */
.albalu-art *, .albalu-art *::before, .albalu-art *::after {
  box-sizing: border-box;
}

/* ─── CSS CUSTOM PROPERTIES ────────────────────────────────── */
.albalu-art {
  --white:         #ffffff;
  --bg:            #f7f3ef;
  --tortora:       #ddd0c4;
  --tortora-light: #ede6df;
  --tortora-dark:  #c4b3a5;
  --teal:          #4db3c8;
  --teal-dark:     #339aaf;
  --teal-light:    #7dd0e0;
  --teal-bg:       #edf8fb;
  --orange:        #e87440;
  --orange-bg:     #fff4ee;
  --text:          #2d2d2d;
  --text-mid:      #595959;
  --text-muted:    #8a8a8a;
  --text-light:    #b5a99e;
  --border:        #ddd4ca;
  --border-light:  #ece5de;
  --font-display:  'Playfair Display', Georgia, serif;
  --font-ui:       'Nunito', sans-serif;
  --radius:        8px;
  --shadow-sm:     0 2px 14px rgba(77,179,200,.08);
  --shadow-md:     0 8px 32px rgba(77,179,200,.13);
}

/* ─── WRAPPER ARTICOLO ─────────────────────────────────────── */
.albalu-art {
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-ui);
  font-size: 16px;
  line-height: 1.75;
  padding-bottom: 80px;
}

.albalu-art__inner {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ─── TOP BAR ──────────────────────────────────────────────── */
.albalu-art__topbar {
  background: var(--tortora-light);
  text-align: center;
  padding: 9px 16px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-mid);
  letter-spacing: .03em;
  border-bottom: 1px solid var(--tortora);
}
.albalu-art__topbar span { margin: 0 18px; }
.albalu-art__topbar span::before { content: '✓  '; color: var(--teal-dark); }

/* ─── BREADCRUMB ───────────────────────────────────────────── */
.albalu-art__breadcrumb {
  font-size: 12px;
  color: var(--text-muted);
  padding: 16px 0 0;
  margin-bottom: 28px;
}
.albalu-art__breadcrumb a { color: var(--text-muted); text-decoration: none; }
.albalu-art__breadcrumb a:hover { color: var(--teal); }
.albalu-art__breadcrumb span { margin: 0 6px; }

/* ─── HERO ─────────────────────────────────────────────────── */
.albalu-art__hero {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 48px 48px 44px;
  margin-bottom: 32px;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.albalu-art__hero::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--teal-dark) 0%, var(--teal-light) 100%);
}
.albalu-art__hero::after {
  content: '';
  position: absolute;
  top: -90px; right: -90px;
  width: 280px; height: 280px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(77,179,200,.07) 0%, transparent 70%);
  pointer-events: none;
}
.albalu-art__hero-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--teal-dark);
  background: var(--teal-bg);
  border: 1px solid rgba(77,179,200,.25);
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 18px;
}
.albalu-art__hero h1 {
  font-family: var(--font-display);
  font-size: clamp(26px, 4.5vw, 40px);
  font-weight: 700;
  line-height: 1.2;
  color: var(--text);
  margin-bottom: 14px;
  max-width: 580px;
}
.albalu-art__hero h1 em { font-style: italic; color: var(--teal-dark); }
.albalu-art__hero-intro {
  font-size: 15.5px;
  color: var(--text-mid);
  max-width: 540px;
  margin-bottom: 22px;
}
.albalu-art__hero-meta {
  font-size: 12px;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}
.albalu-art__hero-meta .dot {
  width: 3px; height: 3px;
  background: var(--tortora);
  border-radius: 50%;
}

/* ─── STAT ROW ─────────────────────────────────────────────── */
.albalu-art__stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 44px;
}
.albalu-art__stat {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 22px 16px;
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: border-color .2s, box-shadow .2s, transform .2s;
}
.albalu-art__stat:hover {
  border-color: var(--teal);
  box-shadow: 0 4px 20px rgba(77,179,200,.15);
  transform: translateY(-2px);
}
.albalu-art__stat-value {
  font-family: var(--font-display);
  font-size: 32px;
  font-weight: 700;
  color: var(--teal-dark);
  line-height: 1.1;
}
.albalu-art__stat-label {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 6px;
  line-height: 1.45;
}

/* ─── BODY ─────────────────────────────────────────────────── */
.albalu-art__body h2 {
  font-family: var(--font-display);
  font-size: clamp(20px, 3vw, 26px);
  font-weight: 700;
  color: var(--text);
  margin: 50px 0 16px;
  line-height: 1.25;
  padding-left: 14px;
  border-left: 3px solid var(--teal);
}
.albalu-art__body p {
  margin-bottom: 18px;
  color: var(--text-mid);
  font-size: 15.5px;
}

/* ─── PULL QUOTE ───────────────────────────────────────────── */
.albalu-art__quote {
  background: var(--tortora-light);
  border: 1px solid var(--tortora);
  border-radius: var(--radius);
  padding: 22px 26px 22px 44px;
  margin: 28px 0;
  position: relative;
}
.albalu-art__quote::before {
  content: '\201C';
  position: absolute;
  left: 12px; top: 4px;
  font-family: var(--font-display);
  font-size: 54px;
  color: var(--teal);
  line-height: 1;
  opacity: .5;
}
.albalu-art__quote p {
  font-family: var(--font-display);
  font-style: italic;
  font-size: 16px;
  color: var(--text) !important;
  line-height: 1.6;
  margin: 0 !important;
}

/* ─── DIVIDER ──────────────────────────────────────────────── */
.albalu-art__divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 40px 0 32px;
  color: var(--text-light);
  font-size: 11px;
  letter-spacing: .12em;
  text-transform: uppercase;
  font-weight: 700;
}
.albalu-art__divider::before,
.albalu-art__divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--tortora);
}

/* ─── PRODUCTS INTRO ───────────────────────────────────────── */
.albalu-art__products-intro {
  background: var(--tortora-light);
  border-radius: var(--radius);
  padding: 18px 22px;
  margin-bottom: 24px;
  font-size: 14.5px;
  color: var(--text-mid);
  border-left: 3px solid var(--teal);
}

/* ─── PRODUCT CARD ─────────────────────────────────────────── */
.albalu-art__card {
  display: grid;
  grid-template-columns: 250px 1fr;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  margin: 22px 0;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .25s, border-color .25s, transform .25s;
}
.albalu-art__card:hover {
  box-shadow: var(--shadow-md);
  border-color: rgba(77,179,200,.4);
  transform: translateY(-2px);
}
.albalu-art__card--reverse {
  grid-template-columns: 1fr 250px;
}
.albalu-art__card--reverse .albalu-art__card-img  { order: 2; }
.albalu-art__card--reverse .albalu-art__card-body { order: 1; }

.albalu-art__card-img {
  background: var(--tortora-light);
  position: relative;
  min-height: 210px;
  overflow: hidden;
}
.albalu-art__card-img img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
  transition: transform .4s ease;
}
.albalu-art__card:hover .albalu-art__card-img img { transform: scale(1.05); }

.albalu-art__card-num {
  position: absolute;
  top: 12px; left: 12px;
  background: var(--teal);
  color: #fff;
  font-size: 13px; font-weight: 700;
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  z-index: 2;
  box-shadow: 0 2px 8px rgba(77,179,200,.45);
}
.albalu-art__card-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-style: italic;
  font-size: 13px;
  color: var(--text-muted);
  text-align: center;
  padding: 24px;
}

.albalu-art__card-body {
  padding: 24px 24px 20px;
  display: flex;
  flex-direction: column;
}
.albalu-art__card-tag {
  font-size: 10.5px; font-weight: 700;
  letter-spacing: .13em; text-transform: uppercase;
  color: var(--teal-dark); margin-bottom: 7px;
}
.albalu-art__card-body h3 {
  font-family: var(--font-display);
  font-size: 18px; font-weight: 700;
  color: var(--text); line-height: 1.3; margin-bottom: 10px;
}
.albalu-art__card-body p {
  font-size: 14px !important;
  line-height: 1.68;
  color: var(--text-mid) !important;
  flex: 1;
  margin-bottom: 0 !important;
}
.albalu-art__card-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 16px;
  width: fit-content;
  background: var(--teal);
  color: #fff;
  font-size: 12.5px; font-weight: 700;
  letter-spacing: .04em;
  text-decoration: none;
  padding: 9px 18px;
  border-radius: 4px;
  transition: background .2s, transform .15s;
}
.albalu-art__card-cta:hover {
  background: var(--teal-dark);
  transform: translateY(-1px);
  color: #fff;
}
.albalu-art__card-cta::after { content: ' →'; }

/* ─── COMPARE ──────────────────────────────────────────────── */
.albalu-art__compare {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  margin: 36px 0;
  box-shadow: var(--shadow-sm);
}
.albalu-art__compare-header { background: var(--text); padding: 14px 22px; }
.albalu-art__compare-header h3 { font-family: var(--font-display); font-size: 16px; color: #fff; margin: 0; }
.albalu-art__compare-labels {
  display: grid; grid-template-columns: 1fr 1fr;
  background: var(--tortora-light);
}
.albalu-art__compare-label {
  padding: 10px 18px;
  font-size: 10.5px; font-weight: 700;
  letter-spacing: .1em; text-transform: uppercase;
}
.albalu-art__compare-label--good { color: var(--teal-dark); border-right: 1px solid var(--tortora); }
.albalu-art__compare-label--bad  { color: var(--text-muted); }
.albalu-art__compare-row {
  display: grid; grid-template-columns: 1fr 1fr;
  border-top: 1px solid var(--border);
}
.albalu-art__compare-cell {
  padding: 14px 18px;
  font-size: 14px; color: var(--text-mid); line-height: 1.6;
}
.albalu-art__compare-cell:first-child {
  border-right: 1px solid var(--border);
  background: rgba(77,179,200,.025);
}
.albalu-art__compare-cell .ok { color: var(--teal-dark); font-weight: 700; margin-right: 5px; }
.albalu-art__compare-cell .no { color: #cc4444; font-weight: 700; margin-right: 5px; }

/* ─── TRUST BAR ────────────────────────────────────────────── */
.albalu-art__trust {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  background: var(--tortora-light);
  border: 1px solid var(--tortora);
  border-radius: var(--radius);
  overflow: hidden;
  margin: 40px 0 32px;
}
.albalu-art__trust-item {
  padding: 18px 12px;
  text-align: center;
  border-right: 1px solid var(--tortora);
  font-size: 12.5px; color: var(--text-mid);
}
.albalu-art__trust-item:last-child { border-right: none; }
.albalu-art__trust-icon { font-size: 22px; display: block; margin-bottom: 5px; }
.albalu-art__trust-item strong { display: block; font-size: 11.5px; font-weight: 700; color: var(--text); margin-bottom: 2px; }

/* ─── STEPS ────────────────────────────────────────────────── */
.albalu-art__steps { margin: 44px 0; }
.albalu-art__steps-title {
  font-family: var(--font-display);
  font-size: 22px; color: var(--text);
  margin-bottom: 18px;
  padding-left: 14px;
  border-left: 3px solid var(--teal);
}
.albalu-art__steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}
.albalu-art__step {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 22px 18px;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.albalu-art__step::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--teal);
  opacity: .5;
}
.albalu-art__step::after {
  content: attr(data-n);
  position: absolute;
  right: -4px; bottom: -14px;
  font-family: var(--font-display);
  font-size: 68px; font-weight: 700;
  color: rgba(77,179,200,.07);
  line-height: 1; pointer-events: none;
}
.albalu-art__step-icon { font-size: 24px; margin-bottom: 10px; display: block; }
.albalu-art__step h4 { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
.albalu-art__step p {
  font-size: 13px !important;
  color: var(--text-muted) !important;
  line-height: 1.55; margin: 0 !important;
}

/* ─── CONCLUSION ───────────────────────────────────────────── */
.albalu-art__conclusion {
  background: var(--white);
  border: 1px solid var(--border);
  border-top: 3px solid var(--teal);
  border-radius: var(--radius);
  padding: 30px 32px 26px;
  margin: 40px 0;
  box-shadow: var(--shadow-sm);
}
.albalu-art__conclusion h2 {
  font-family: var(--font-display);
  font-size: 21px; color: var(--text);
  margin-bottom: 10px;
  padding-left: 0; border-left: none; margin-top: 0;
}
.albalu-art__conclusion p {
  font-size: 15px !important;
  color: var(--text-mid) !important;
  line-height: 1.75; margin: 0 !important;
}

/* ─── FAQ ──────────────────────────────────────────────────── */
.albalu-art__faq { margin: 40px 0; }
.albalu-art__faq-title {
  font-family: var(--font-display);
  font-size: 22px; color: var(--text);
  margin-bottom: 14px;
  padding-left: 14px;
  border-left: 3px solid var(--teal);
}
.albalu-art__faq-item {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  margin-bottom: 8px;
  overflow: hidden;
  transition: box-shadow .2s, border-color .2s;
}
.albalu-art__faq-item[open] {
  box-shadow: 0 2px 16px rgba(77,179,200,.12);
  border-color: rgba(77,179,200,.4);
}
.albalu-art__faq-q {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 15px 18px;
  cursor: pointer;
  font-size: 14.5px; font-weight: 700;
  color: var(--text);
  list-style: none; user-select: none;
}
.albalu-art__faq-q::-webkit-details-marker { display: none; }
.albalu-art__faq-icon {
  width: 26px; height: 26px;
  background: var(--teal-bg);
  border: 1px solid rgba(77,179,200,.3);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; color: var(--teal-dark);
  flex-shrink: 0;
  transition: transform .22s, background .2s;
}
.albalu-art__faq-item[open] .albalu-art__faq-icon {
  transform: rotate(45deg);
  background: var(--teal);
  color: #fff;
}
.albalu-art__faq-a {
  padding: 0 18px 14px;
  font-size: 14px; color: var(--text-mid); line-height: 1.7;
  border-top: 1px solid var(--border-light);
}

/* ─── CTA FINALE ───────────────────────────────────────────── */
.albalu-art__cta {
  background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
  border-radius: var(--radius);
  padding: 48px 44px;
  text-align: center;
  margin: 44px 0 0;
  position: relative;
  overflow: hidden;
}
.albalu-art__cta::before {
  content: '';
  position: absolute;
  top: -70px; right: -70px;
  width: 220px; height: 220px;
  border-radius: 50%;
  background: rgba(255,255,255,.08);
}
.albalu-art__cta::after {
  content: '';
  position: absolute;
  bottom: -50px; left: -50px;
  width: 160px; height: 160px;
  border-radius: 50%;
  background: rgba(255,255,255,.06);
}
.albalu-art__cta h2 {
  position: relative;
  font-family: var(--font-display);
  font-size: clamp(20px, 3.5vw, 28px);
  color: #fff; font-weight: 700;
  margin-bottom: 10px;
  padding-left: 0; border-left: none; margin-top: 0;
}
.albalu-art__cta p {
  position: relative;
  color: rgba(255,255,255,.8);
  font-size: 15px;
  margin-bottom: 28px !important;
  max-width: 460px;
  margin-left: auto !important;
  margin-right: auto !important;
}
.albalu-art__cta-btn {
  position: relative;
  display: inline-block;
  background: #fff;
  color: var(--teal-dark);
  font-size: 14px; font-weight: 700;
  letter-spacing: .05em;
  text-decoration: none;
  padding: 13px 32px;
  border-radius: 4px;
  transition: background .2s, transform .15s;
}
.albalu-art__cta-btn:hover {
  background: var(--tortora-light);
  transform: translateY(-2px);
  color: var(--teal-dark);
}

/* ─── RESPONSIVE ───────────────────────────────────────────── */
@media (max-width: 720px) {
  .albalu-art__hero { padding: 32px 22px 28px; }
  .albalu-art__topbar span { display: block; margin: 2px 0; }
  .albalu-art__stats { grid-template-columns: 1fr 1fr; }
  .albalu-art__stats .albalu-art__stat:last-child { grid-column: 1 / -1; }

  .albalu-art__card,
  .albalu-art__card--reverse { grid-template-columns: 1fr; }
  .albalu-art__card--reverse .albalu-art__card-img,
  .albalu-art__card--reverse .albalu-art__card-body { order: unset; }
  .albalu-art__card-img { min-height: 190px; }

  .albalu-art__compare-row,
  .albalu-art__compare-labels { grid-template-columns: 1fr; }
  .albalu-art__compare-cell:first-child { border-right: none; border-bottom: 1px solid var(--border); }
  .albalu-art__compare-label--good { border-right: none; border-bottom: 1px solid var(--tortora); }

  .albalu-art__steps-grid { grid-template-columns: 1fr; }

  .albalu-art__trust { grid-template-columns: 1fr 1fr; }
  .albalu-art__trust-item:nth-child(2) { border-right: none; }
  .albalu-art__trust-item:nth-child(3),
  .albalu-art__trust-item:last-child { border-top: 1px solid var(--tortora); }

  .albalu-art__cta { padding: 32px 22px; }
}
</style>

<!-- ═══════════════════════════════════════════════════════════
     MARKUP ARTICOLO
     ═══════════════════════════════════════════════════════════ -->
<div class="albalu-art">

  <!-- TOP BAR -->
  <div class="albalu-art__topbar">
    <span>Bomboniere 100% Made in Italy</span>
    <span>Spedizione gratuita oltre 149 €</span>
    <span>Produzione propria dal 1991</span>
  </div>

  <div class="albalu-art__inner">

    <!-- BREADCRUMB -->
    <nav class="albalu-art__breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>›</span>
      <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a><span>›</span>
      <?php the_title(); ?>
    </nav>

    <!-- HERO -->
    <header class="albalu-art__hero">
      <div class="albalu-art__hero-label">📖 Guida all'acquisto · <?php echo date_i18n( 'F Y' ); ?></div>
      <h1>Ha senso fare ancora<br><em>le bomboniere</em> nel 2026?</h1>
      <p class="albalu-art__hero-intro">Dati, tendenze e 5 idee che i tuoi ospiti non metteranno mai in un cassetto. Dal laboratorio Albalù di Terlizzi, in Puglia.</p>
      <div class="albalu-art__hero-meta">
        <span>⏱ 6 minuti di lettura</span>
        <span class="dot"></span>
        <span>📅 <?php echo get_the_date( 'F Y' ); ?></span>
        <span class="dot"></span>
        <span>✍️ Team Albalù</span>
      </div>
    </header>

    <!-- STATISTICHE -->
    <div class="albalu-art__stats" role="list">
      <div class="albalu-art__stat" role="listitem">
        <div class="albalu-art__stat-value">79%</div>
        <div class="albalu-art__stat-label">degli sposi italiani sceglie ancora la bomboniera (2025)</div>
      </div>
      <div class="albalu-art__stat" role="listitem">
        <div class="albalu-art__stat-value">3,5 mld</div>
        <div class="albalu-art__stat-label">il valore del mercato matrimoni in Italia ogni anno</div>
      </div>
      <div class="albalu-art__stat" role="listitem">
        <div class="albalu-art__stat-value">+9.700</div>
        <div class="albalu-art__stat-label">coppie intervistate nel Rapporto Nuziale 2025</div>
      </div>
    </div>

    <!-- CORPO ARTICOLO -->
    <div class="albalu-art__body">

      <p>Secondo il <strong>Rapporto sul Settore Nuziale 2025 di Matrimonio.com</strong>, condotto su oltre 9.700 coppie italiane, il 79% di chi si sposa sceglie ancora la bomboniera. Non è nostalgia: è una scelta consapevole, sempre più orientata verso oggetti che durano e che raccontano qualcosa.</p>

      <p>Il settore dei matrimoni vale circa 3,5 miliardi di euro l'anno in Italia. La bomboniera non è un accessorio marginale: è parte del racconto che gli sposi vogliono lasciare a chi li ha accompagnati in quel giorno.</p>

      <p>Il problema, semmai, non è <em>se</em> farla. È <em>come</em> farla senza che finisca in un cassetto entro 48 ore.</p>

      <div class="albalu-art__quote">
        <p>Una bomboniera brutta è un regalo con la scadenza. Una bella bomboniera è un ricordo con un posto fisso in casa.</p>
      </div>

      <h2>Cosa è cambiato davvero</h2>

      <p>Le bomboniere degli anni Novanta — statuine di porcellana, campanelline, cornicine neutre — avevano un problema fondamentale: non appartenevano a nessuno. Erano decorative per definizione, generiche per scelta, intercambiabili per natura.</p>

      <p>Oggi la direzione è esattamente opposta. Chi organizza un matrimonio, un battesimo, una comunione o una cresima vuole che ogni ospite porti a casa qualcosa di <strong>riconoscibilmente loro</strong> — con il loro nome, la loro data, il loro stile. Un oggetto che abbia un senso anche fuori dal contesto della cerimonia: sul ripiano del soggiorno, sulla scrivania, in cucina.</p>

      <div class="albalu-art__quote">
        <p>La differenza tra una bomboniera che si tiene e una che si dona alla prima lotteria parrocchiale? Circa tre centimetri di personalizzazione.</p>
      </div>

      <p>È qui che entra in gioco la vera evoluzione del mercato: non più prodotti anonimi da catalogo, ma oggetti pensati, personalizzati e — nel caso migliore — realizzati da chi li vende.</p>

      <!-- DIVIDER -->
      <div class="albalu-art__divider">Le 5 bomboniere che nel 2026 non finiscono nel cassetto</div>

      <div class="albalu-art__products-intro">
        Da oltre trent'anni nel settore, nel laboratorio di Albalù a Terlizzi (BA) sappiamo riconoscere cosa funziona davvero. Questi sono i prodotti che gli ospiti tengono — e che spesso ci chiedono di rifare anche anni dopo come idee regalo.
      </div>

      <!-- ── PRODOTTO 1 ── -->
      <div class="albalu-art__card">
        <div class="albalu-art__card-img">
          <span class="albalu-art__card-num">1</span>
          <?php
          // Sostituisci l'URL src con l'immagine reale dal tuo Media Library
          $img_1 = get_template_directory_uri() . '/images/bomboniere/calendario-perpetuo.jpg';
          ?>
          <img src="<?php echo esc_url( $img_1 ); ?>"
               alt="Calendario perpetuo personalizzato Albalù"
               loading="lazy"
               onerror="this.style.opacity='0'" />
          <div class="albalu-art__card-placeholder">Calendario perpetuo<br>personalizzato</div>
        </div>
        <div class="albalu-art__card-body">
          <p class="albalu-art__card-tag">Il regalo senza scadenza</p>
          <h3>Calendario perpetuo personalizzato</h3>
          <p>Non diventa inutile il 31 dicembre, non perde significato dopo un anno. Sul ripiano della cucina o della libreria, con la data del matrimonio o del battesimo incisa nel legno, continua a fare il suo lavoro ogni mattina. Uno degli oggetti rari per cui funzione e ricordo si sommano invece di escludersi.</p>
          <a class="albalu-art__card-cta"
             href="<?php echo esc_url( home_url( '/categoria-prodotto/bomboniere-calendario-perpetuo-personalizzabili/' ) ); ?>">
            Scopri i calendari perpetui
          </a>
        </div>
      </div>

      <!-- ── PRODOTTO 2 ── -->
      <div class="albalu-art__card albalu-art__card--reverse">
        <div class="albalu-art__card-img">
          <span class="albalu-art__card-num">2</span>
          <?php $img_2 = get_template_directory_uri() . '/images/bomboniere/piantina-grassa.jpg'; ?>
          <img src="<?php echo esc_url( $img_2 ); ?>"
               alt="Piantina grassa personalizzata Albalù"
               loading="lazy"
               onerror="this.style.opacity='0'" />
          <div class="albalu-art__card-placeholder">Piantina grassa<br>personalizzata</div>
        </div>
        <div class="albalu-art__card-body">
          <p class="albalu-art__card-tag">Viva, letteralmente</p>
          <h3>Piantina grassa personalizzata</h3>
          <p>Le succulente resistono, crescono, non fanno rumore. Il vasetto in ceramica personalizzato con nomi e data le trasforma da "bel gesto verde" a ricordo concreto. Ogni piantina viene confezionata a mano nel nostro laboratorio, con vasetto, sacchettino e bigliettino coordinati al tema della cerimonia.</p>
          <a class="albalu-art__card-cta"
             href="<?php echo esc_url( home_url( '/categoria-prodotto/bomboniere-piantine-grasse/' ) ); ?>">
            Vedi le piantine grasse
          </a>
        </div>
      </div>

      <!-- ── PRODOTTO 3 ── -->
      <div class="albalu-art__card">
        <div class="albalu-art__card-img">
          <span class="albalu-art__card-num">3</span>
          <?php $img_3 = get_template_directory_uri() . '/images/bomboniere/portafoto.jpg'; ?>
          <img src="<?php echo esc_url( $img_3 ); ?>"
               alt="Portafoto personalizzato Albalù"
               loading="lazy"
               onerror="this.style.opacity='0'" />
          <div class="albalu-art__card-placeholder">Portafoto<br>personalizzato</div>
        </div>
        <div class="albalu-art__card-body">
          <p class="albalu-art__card-tag">Il classico che non tradisce mai</p>
          <h3>Portafoto personalizzato</h3>
          <p>I portafoto hanno attraversato mode e decenni senza mai passare di moda. La differenza la fa la qualità del materiale e il dettaglio della personalizzazione. In ceramica dipinta a mano o in legno con incisione laser — con i nomi degli sposi e la data del matrimonio — non è la stessa cosa di una cornicina da scaffale. Si vede, si sente, si tiene.</p>
          <a class="albalu-art__card-cta"
             href="<?php echo esc_url( home_url( '/categoria-prodotto/bomboniere-portafoto-personalizzabili/' ) ); ?>">
            Esplora i portafoto
          </a>
        </div>
      </div>

      <!-- ── PRODOTTO 4 ── -->
      <div class="albalu-art__card albalu-art__card--reverse">
        <div class="albalu-art__card-img">
          <span class="albalu-art__card-num">4</span>
          <?php $img_4 = get_template_directory_uri() . '/images/bomboniere/lampada-led.jpg'; ?>
          <img src="<?php echo esc_url( $img_4 ); ?>"
               alt="Lampada LED personalizzata Albalù"
               loading="lazy"
               onerror="this.style.opacity='0'" />
          <div class="albalu-art__card-placeholder">Lampada personalizzata<br>LED</div>
        </div>
        <div class="albalu-art__card-body">
          <p class="albalu-art__card-tag">Il ricordo che illumina</p>
          <h3>Lampada personalizzata</h3>
          <p>Non occupano spazio inutile, non raccolgono polvere: fanno luce. E ogni sera, con quella luce calda, riportano per un secondo a quel pomeriggio di giugno o a quella domenica di marzo. Disponibili in vetro, ceramica pugliese e basi in legno, con dediche e date incise direttamente sul materiale.</p>
          <a class="albalu-art__card-cta"
             href="<?php echo esc_url( home_url( '/categoria-prodotto/bomboniere-lampade-led-personalizzabili/' ) ); ?>">
            Guarda le lampade
          </a>
        </div>
      </div>

      <!-- ── PRODOTTO 5 ── -->
      <div class="albalu-art__card">
        <div class="albalu-art__card-img">
          <span class="albalu-art__card-num">5</span>
          <?php $img_5 = get_template_directory_uri() . '/images/bomboniere/orologio-albero-vita.jpg'; ?>
          <img src="<?php echo esc_url( $img_5 ); ?>"
               alt="Orologio Albero della Vita Albalù"
               loading="lazy"
               onerror="this.style.opacity='0'" />
          <div class="albalu-art__card-placeholder">Orologio<br>Albero della Vita</div>
        </div>
        <div class="albalu-art__card-body">
          <p class="albalu-art__card-tag">Significato oltre l'estetica</p>
          <h3>Orologio Albero della Vita</h3>
          <p>L'albero della vita è il simbolo perfetto per una cerimonia: mette radici, si ramifica, non si ferma mai. Tra le bomboniere più richieste per comunioni, cresime e matrimoni con un taglio spirituale. Non acquistato e rivenduto: fatto nel nostro laboratorio in ceramica, legno o metallo, con nome, data ed eventuale dedica.</p>
          <a class="albalu-art__card-cta"
             href="<?php echo esc_url( home_url( '/prodotto/bomboniera-orologio-da-tavolo-albero-della-vita-con-cornice-tortora/' ) ); ?>">
            Scopri gli orologi
          </a>
        </div>
      </div>

      <!-- DIVIDER -->
      <div class="albalu-art__divider">Produzione propria vs. importazione</div>

      <h2>Il punto che cambia tutto</h2>

      <p>Esiste una differenza enorme — che non sempre si vede in foto, ma si tocca con mano al momento dell'unboxing — tra una bomboniera prodotta artigianalmente e una importata e riconfezionata. Il mercato delle bomboniere online è pieno di entrambe le cose. Ed è onesto dirlo chiaramente.</p>

      <p>Albalù è una manifattura, fondata nel 1991 a Terlizzi, in Puglia. Non acquistiamo prodotti da fornitori esteri per rivenderli. Ogni pezzo nasce e finisce nel nostro laboratorio — dalla materia prima alla confezione con sacchettino, nastro e confetti.</p>

      <div class="albalu-art__quote">
        <p>Comprare bomboniere importate è come ordinare una torta nuziale online senza assaggiare niente. Può andare bene. Ma è un rischio enorme per un giorno così.</p>
      </div>

      <!-- COMPARE TABLE -->
      <div class="albalu-art__compare">
        <div class="albalu-art__compare-header">
          <h3>Albalù vs. prodotti importati: le differenze concrete</h3>
        </div>
        <div class="albalu-art__compare-labels">
          <div class="albalu-art__compare-label albalu-art__compare-label--good">✓ &nbsp;Albalù · produzione propria</div>
          <div class="albalu-art__compare-label albalu-art__compare-label--bad">✗ &nbsp;Prodotti importati e rivenduti</div>
        </div>
        <div class="albalu-art__compare-row">
          <div class="albalu-art__compare-cell"><span class="ok">✓</span>Personalizzazione vera: forma, materiale, colore, testo e confezione partendo da zero.</div>
          <div class="albalu-art__compare-cell"><span class="no">✗</span>Un'etichetta sopra un prodotto standard, uguale per tutti.</div>
        </div>
        <div class="albalu-art__compare-row">
          <div class="albalu-art__compare-cell"><span class="ok">✓</span>Qualità controllata: stesso laboratorio, stesse mani, stessi standard per ogni pezzo.</div>
          <div class="albalu-art__compare-cell"><span class="no">✗</span>Qualità variabile tra un lotto e l'altro, impossibile da verificare prima della consegna.</div>
        </div>
        <div class="albalu-art__compare-row">
          <div class="albalu-art__compare-cell"><span class="ok">✓</span>Bozza grafica via WhatsApp prima di produrre. Modifiche illimitate fino all'approvazione.</div>
          <div class="albalu-art__compare-cell"><span class="no">✗</span>Nessuna anteprima: il risultato si scopre all'apertura del pacco.</div>
        </div>
      </div>

      <!-- TRUST BAR -->
      <div class="albalu-art__trust" role="list">
        <div class="albalu-art__trust-item" role="listitem">
          <span class="albalu-art__trust-icon">🏭</span>
          <strong>Produzione propria</strong>
          dal 1991, Puglia
        </div>
        <div class="albalu-art__trust-item" role="listitem">
          <span class="albalu-art__trust-icon">📱</span>
          <strong>Bozza WhatsApp</strong>
          prima di produrre
        </div>
        <div class="albalu-art__trust-item" role="listitem">
          <span class="albalu-art__trust-icon">📦</span>
          <strong>Spedizione gratuita</strong>
          oltre 149 €
        </div>
        <div class="albalu-art__trust-item" role="listitem">
          <span class="albalu-art__trust-icon">⭐</span>
          <strong>+10.000 clienti</strong>
          soddisfatti
        </div>
      </div>

      <!-- HOW IT WORKS -->
      <div class="albalu-art__steps">
        <h2 class="albalu-art__steps-title">Come funziona ordinare da Albalù</h2>
        <div class="albalu-art__steps-grid">
          <div class="albalu-art__step" data-n="1">
            <span class="albalu-art__step-icon">💬</span>
            <h4>Ci scrivi</h4>
            <p>Su WhatsApp o via form con nomi, data, colori, quantità e tipo di evento.</p>
          </div>
          <div class="albalu-art__step" data-n="2">
            <span class="albalu-art__step-icon">🎨</span>
            <h4>Ricevi la bozza</h4>
            <p>Ti inviamo la bozza grafica. Modifiche illimitate fino a che sei soddisfatto.</p>
          </div>
          <div class="albalu-art__step" data-n="3">
            <span class="albalu-art__step-icon">📦</span>
            <h4>Approvi e spediamo</h4>
            <p>Solo dopo il tuo ok parte la produzione. Spedizione gratuita sopra i 149 €.</p>
          </div>
        </div>
      </div>

      <!-- CONCLUSIONE -->
      <div class="albalu-art__conclusion">
        <h2>Allora: ha ancora senso?</h2>
        <p>Sì. Ma solo se la bomboniera è pensata per durare — non per fare numero sul vassoio dei confetti. Un oggetto artigianale, personalizzato, realizzato da chi lo conosce davvero non ha bisogno di giustificarsi. Parla da solo, ogni volta che l'ospite lo vede sul ripiano. E ogni volta riporta a quel giorno — che è esattamente il punto di tutta la faccenda.</p>
      </div>

      <!-- FAQ -->
      <div class="albalu-art__faq">
        <h2 class="albalu-art__faq-title">Domande frequenti</h2>

        <details class="albalu-art__faq-item">
          <summary class="albalu-art__faq-q">
            Posso richiedere una bomboniera completamente personalizzata, anche fuori catalogo?
            <span class="albalu-art__faq-icon">+</span>
          </summary>
          <p class="albalu-art__faq-a">Sì. Albalù è un laboratorio di produzione: possiamo sviluppare progetti custom dalla forma al confezionamento. Contattaci via WhatsApp o dal form sul sito per discutere l'idea.</p>
        </details>

        <details class="albalu-art__faq-item">
          <summary class="albalu-art__faq-q">
            Quanto tempo prima devo ordinare?
            <span class="albalu-art__faq-icon">+</span>
          </summary>
          <p class="albalu-art__faq-a">Per ordini personalizzati: almeno 3–4 settimane. Per lavori su misura complessi o quantità elevate, consigliamo 5–6 settimane.</p>
        </details>

        <details class="albalu-art__faq-item">
          <summary class="albalu-art__faq-q">
            Come funziona la bozza prima della produzione?
            <span class="albalu-art__faq-icon">+</span>
          </summary>
          <p class="albalu-art__faq-a">Dopo aver ricevuto i tuoi dettagli (nomi, date, colori), ti inviamo una bozza grafica via WhatsApp. Puoi chiedere tutte le modifiche che vuoi — solo dopo la tua approvazione iniziamo a produrre.</p>
        </details>

        <details class="albalu-art__faq-item">
          <summary class="albalu-art__faq-q">
            La spedizione è sicura? Le bomboniere non si rompono?
            <span class="albalu-art__faq-icon">+</span>
          </summary>
          <p class="albalu-art__faq-a">Utilizziamo imballaggi con spugne e carte protettive specifici per prodotti fragili. La spedizione è gratuita sopra i 149 euro e copre tutta Italia.</p>
        </details>

        <details class="albalu-art__faq-item">
          <summary class="albalu-art__faq-q">
            Qual è la differenza rispetto ai grandi store di bomboniere online?
            <span class="albalu-art__faq-icon">+</span>
          </summary>
          <p class="albalu-art__faq-a">Albalù produce internamente, senza importazioni. Questo garantisce qualità costante, personalizzazione reale e la possibilità di parlare direttamente con chi produce — non con un call center.</p>
        </details>
      </div>

      <!-- CTA FINALE -->
      <div class="albalu-art__cta">
        <h2>Trova la bomboniera giusta per il tuo giorno</h2>
        <p>Sfoglia il catalogo, richiedi un preventivo o scrivici per un'idea completamente su misura. Siamo a Terlizzi, in Puglia — e rispondiamo su WhatsApp.</p>
        <a class="albalu-art__cta-btn"
           href="<?php echo esc_url( home_url( '/vendita-online/bomboniere.html' ) ); ?>">
          Sfoglia il catalogo →
        </a>
      </div>

    </div><!-- /.albalu-art__body -->
  </div><!-- /.albalu-art__inner -->
</div><!-- /.albalu-art -->

<?php get_footer(); ?>
