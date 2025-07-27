document.addEventListener('DOMContentLoaded', () => {
    // Remove elementos desnecessários
    ['span.url', '.loading-container', '.scheme-container'].forEach(selector => {
        document.querySelector(selector)?.remove();
    });
});