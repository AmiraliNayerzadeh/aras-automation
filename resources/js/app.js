document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal[data-bs-show="1"]').forEach((el) => {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    });
});
