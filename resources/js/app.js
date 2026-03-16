import './bootstrap';
import bookingHandler from './booking-handler';

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingHandler', bookingHandler);
});
