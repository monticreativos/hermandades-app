import './bootstrap';

import Alpine from 'alpinejs';
import registerEconomiaAsientoLibroDiario from './economia-asiento-libro-diario';
import registerEconomiaFacturas from './economia-facturas';

document.addEventListener('alpine:init', () => {
    registerEconomiaAsientoLibroDiario(Alpine);
    registerEconomiaFacturas(Alpine);
});

window.Alpine = Alpine;

Alpine.start();
