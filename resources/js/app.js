import './bootstrap';

import Alpine from 'alpinejs';
import moneyInput from './money-input';

Alpine.data('moneyInput', moneyInput);

window.Alpine = Alpine;

Alpine.start();
