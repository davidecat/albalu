# AUDIT CORE WEB VITALS — ALBALU.IT

Data: 2026-06-27
Sito: https://www.albalu.it/

---

## SITUAZIONE ATTUALE

| Metrica | Valore | Target | Stato |
|---------|--------|--------|-------|
| TTFB | 1.96s | <0.8s (mobile) | 🔴 Pessimo |
| HTML size | 303-508 KB | <100 KB | 🟡 Alto |
| CSS totale | 388 KB | <100 KB | 🔴 |
| ↳ main.css | 314 KB | | 🔴 non minificato |
| ↳ FontAwesome | 74 KB | | 🟡 |
| JS render-blocking | 29 script sincroni | 0 | 🔴 |
| Third-party domains | 10 | <5 | 🔴 |

Third-party attivi: Iubenda, GTM, FB Pixel, TrustIndex, Brevo, Pinterest, Google Fonts, jsdelivr CDN, ecc.

### Metriche CWV stimate (attuali)
- LCP: ~2.5s
- INP: ~250ms
- CLS: ~0.18
- TTFB: 1.96s

---

## TOP 5 PROBLEMI PER IMPATTO

### #1 — jQuery + 29 Script Sincroni Bloccano Rendering
- **Impatto:** LCP +800-1200ms | INP +400-600ms
- **Sforzo:** MEDIUM
- **Descrizione:** jQuery 86KB (sincrono), WooCommerce core, GTM4WP, Bootstrap, Swiper, Custom JS — tutti senza async/defer
- **Azione:** Filtro `script_loader_tag` in functions.php per aggiungere `defer` a tutti gli script eccetto jQuery core (richiesto da PEWC)
- **Risultato atteso:** LCP -600ms

### #2 — Iubenda Cookie Banner Sincrono
- **Impatto:** TTFB +500-800ms | LCP +300-500ms | CLS +150-250ms
- **Sforzo:** SMALL
- **Descrizione:** `//cs.iubenda.com/sync/3760186.js` è sincrono in `<head>`, blocca painting fino a che banner CSS sia injected
- **Azione:** Aggiungi `async` a script Iubenda + CSS placeholder `.iubenda-cs-banner { min-height: 60px; }`
- **Risultato atteso:** LCP -200ms, TTFB -300ms

### #3 — main.css 314 KB Senza Minification
- **Impatto:** LCP +200-400ms
- **Sforzo:** LARGE
- **Descrizione:** 81% del CSS totale, sourcemap 76 KB incluso in production, probabilmente non minificato
- **Azione:**
  1. Minifica main.css (~180 KB, -43%)
  2. Rimuovi sourcemap dalla produzione
  3. Estrai 8-12 KB CSS critico inline
  4. Async load resto con `preload` + `onload`
- **Risultato atteso:** LCP -300ms

### #4 — 15+ Immagini Senza Width/Height
- **Impatto:** CLS +400-800ms
- **Sforzo:** SMALL
- **Descrizione:** Grid immagini (Comunione, Cresima, ecc.) senza dimensioni → browser non riserva spazio
- **Azione:**
  1. Aggiungi `width`/`height` a tutte le img grid
  2. `loading="lazy"` per off-viewport
  3. Ottimizza `sizes` srcset
- **Risultato atteso:** CLS -500ms

### #5 — Doppio Caricamento Swiper (CDN + Locale)
- **Impatto:** LCP +100-150ms
- **Sforzo:** SMALL
- **Descrizione:** Swiper caricato da CDN jsdelivr + locale bs-swiper, entrambi sincroni
- **Azione:**
  1. Dequeue Swiper locale (o CDN)
  2. Defer `swiper-init.min.js` con `wp_script_add_data(..., 'strategy', 'defer')`
- **Risultato atteso:** LCP -80ms

---

## STRATEGIA DI IMPLEMENTAZIONE

### FASE 1 — Quick Wins (1 giorno)
Guadagno atteso: **-450ms LCP**

- [ ] Aggiungi `async` a Iubenda
- [ ] Rimuovi Swiper duplicato
- [ ] Aggiungi dimensioni immagini grid

### FASE 2 — Ottimizzazioni medie (2-3 giorni)
Guadagno atteso: **-600ms TTFB/LCP**

- [ ] Defer su 25+ script (tranne jQuery core)
- [ ] Minifica main.css
- [ ] Rimuovi sourcemap dalla produzione
- [ ] CSS placeholder Iubenda per CLS

### FASE 3 — Strutturale (1 settimana)
Guadagno atteso: **-300ms LCP**

- [ ] Critical CSS inline (8-12 KB) + async load resto
- [ ] Valuta spostamento jQuery in footer (rischia PEWC — testare)

---

## IMPATTO TOTALE ATTESO (Fase 1+2)

| Metrica | Prima | Dopo | Guadagno |
|---------|-------|------|----------|
| TTFB | 1.96s | ~1.3s | -33% (-660ms) |
| LCP | ~2.5s | ~1.8s | -28% (-700ms) |
| INP | ~250ms | ~140ms | -44% |
| CLS | ~0.18 | ~0.08 | -55% |

---

## NOTE TECNICHE

### TTFB alto (1.96s) — priorità massima
1.96s è il vero collo di bottiglia. La cache HTML potrebbe non funzionare:
- Verifica hit rate Redis: `redis-cli INFO stats | grep keyspace`
- Controlla slow query log MySQL
- PHP-FPM pool: attualmente 4 processi, potrebbero essere pochi
- Cloudflare cache HTML: attualmente probabilmente in bypass (per PEWC)

### Redis
- 110MB/2GB attivo
- Da verificare hit rate > 90%

### Cloudflare
- Cache webp funzionante con Vary: Accept
- Verifica cache bypass HTML (deve essere dinamico per PEWC)

### WebP + nginx
- Rewrite Accept header attivo e funzionante
- 41K webp generati su 32K immagini

---

## COSA È GIÀ STATO FATTO (non ripetere)

- Font Awesome sostituito con SVG inline nel footer
- Preload di fa-solid-900.woff2 attivo
- `font-display: swap` forzato
- BLOCCO 1 dequeue Gutenberg/dashicons commentato (per non rompere PEWC)
- Webp conversion massa (29K+ files)
- nginx rewrite webp + Vary: Accept
- W3TC disattivato completamente
- Redis Object Cache attivo

---

## PROSSIMI PASSI

Priorità consigliata:
1. **Iniziare da Fase 1 (quick wins)** — massimo ROI
2. **Poi indagare TTFB 1.96s** — vero problema strutturale
3. **Fase 2 in parallelo** — minify main.css e defer script

Comandi diagnostica TTFB da lanciare per capire origine ritardo:
```bash
# Redis hit rate
ssh -p 9222 root@167.86.111.217 'redis-cli INFO stats | grep -E "keyspace_hits|keyspace_misses"'

# PHP-FPM status
ssh -p 9222 root@167.86.111.217 'cat /run/php/php8.4-fpm.status 2>/dev/null || echo "status non attivo"'

# Slow query
ssh -p 9222 root@167.86.111.217 'mysqldumpslow /var/lib/mysql/*-slow.log 2>/dev/null | head -30'
```
