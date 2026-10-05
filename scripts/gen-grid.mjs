// Genera assets/css/grid.css: grilla de 12 columnas con la misma API que Bootstrap 4
// (.row, .col, .col-N, .col-sm-N, .col-md-N, .col-lg-N, .col-xl-N, .offset-*, .no-gutters),
// para que las plantillas existentes mantengan su maquetación sin reescribirlas.
import { writeFileSync } from 'node:fs';

const gutter = '0.75rem';
const bps = { sm: 576, md: 768, lg: 992, xl: 1200 };
let css = `/* GENERADO por scripts/gen-grid.mjs — no editar a mano. */
.container-fluid, .container-xl { width: 100%; margin-inline: auto; padding-inline: ${gutter}; }
.row { display: flex; flex-wrap: wrap; margin-inline: calc(${gutter} * -1); }
.no-gutters { margin-inline: 0; }
.no-gutters > .col, .no-gutters > [class*='col-'] { padding-inline: 0; }
.col, [class*='col-'] { position: relative; width: 100%; min-height: 1px; padding-inline: ${gutter}; }
.col { flex-basis: 0; flex-grow: 1; max-width: 100%; }
.col-auto { flex: 0 0 auto; width: auto; max-width: 100%; }
`;
const pct = (n) => `${(n / 12 * 100).toFixed(6).replace(/\.?0+$/, '')}%`;
for (let n = 1; n <= 12; n++) css += `.col-${n} { flex: 0 0 ${pct(n)}; max-width: ${pct(n)}; }\n`;
for (let n = 0; n <= 11; n++) css += `.offset-${n} { margin-left: ${n === 0 ? '0' : pct(n)}; }\n`;
for (const [bp, px] of Object.entries(bps)) {
  css += `@media (min-width: ${px}px) {\n  .col-${bp} { flex-basis: 0; flex-grow: 1; max-width: 100%; }\n  .col-${bp}-auto { flex: 0 0 auto; width: auto; max-width: 100%; }\n`;
  for (let n = 1; n <= 12; n++) css += `  .col-${bp}-${n} { flex: 0 0 ${pct(n)}; max-width: ${pct(n)}; }\n`;
  for (let n = 0; n <= 11; n++) css += `  .offset-${bp}-${n} { margin-left: ${n === 0 ? '0' : pct(n)}; }\n`;
  css += `}\n`;
}
writeFileSync(new URL('../assets/css/grid.css', import.meta.url), css);
console.log('grid.css generado');
