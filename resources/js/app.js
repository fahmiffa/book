import './bootstrap';
import bookingHandler from './booking-handler';
import dropdownSearch from './dropdown-search';

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingHandler', bookingHandler);
    Alpine.data('dropdownSearch', dropdownSearch);
});
