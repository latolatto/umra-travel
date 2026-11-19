# UMRA Travel — faqe statike

Faqja publike është tani HTML, CSS dhe JavaScript statik. Publiko përmbajtjen e dosjes kryesore te një host statik ose hap `index.html` lokalisht. Nuk nevojitet PHP për faqet publike.

## Faqet

- `index.html` — faqja kryesore, paketat e zgjedhura dhe pyetjet e shpeshta
- `paketa.html` — lista dhe hollësitë e paketave
- `rezervo.html` — formular rezervimi me paketën e zgjedhur paraprakisht
- `kontakt.html` — kontakt dhe formular pyetjesh
- `rreth-umres.html` — informacion hyrës për Umren dhe Haxhin

Përmbajtja e paketave që shfaqet në versionin statik gjendet te `assets/js/site-data.js`. Formularët e kontaktit dhe rezervimit përgatisin tekstin dhe hapin WhatsApp-in; vizitori e kontrollon dhe e dërgon vetë. Faqja nuk ruan të dhënat e formularëve dhe nuk ka panel administrimi aktiv në versionin statik.

## Backend-i i ruajtur

Kodi PHP i panelit dhe logjikës së paketave është ruajtur te `backend-saved/` për aktivizim të mëvonshëm. Mos e përfshi këtë dosje në publikimin statik. Ajo ruan kopjet e faqeve PHP, `inc/`, `data/` dhe asetet që i duhen versionit PHP. Backend-i mund të rikthehet në një host me PHP 8+ dhe leje shkrimi në dosjet `data/` dhe `assets/uploads/`.

Data 3–11 Nëntor 2026 nuk shfaqet si ofertë aktive, sepse ka kaluar; të dhënat e saj mbeten në kopjen e backend-it.
