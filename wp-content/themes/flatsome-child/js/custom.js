document.addEventListener('DOMContentLoaded', function() {
    const button = document.querySelector('.button-reservation');
    if (button) {
        button.addEventListener('click', function() {
            const terms = document.querySelector('.terms-condition-text');
            if (terms) {
                terms.style.display = 'none';
            }

            const form = document.querySelector('.form-reservation');
            if (form) {
                form.style.display = 'block';
            }
        });
    }

    const menuButton = document.querySelector('.menu-button');
    const menuClose = document.querySelector('.menu-close');
    const menuOpen = document.querySelector('.menu-open');
    const header = document.querySelector('.header');
    const body = document.body;

    if (menuButton && menuOpen) {
        menuButton.addEventListener('click', function() {
            menuOpen.classList.add('show');
            header.classList.add('show-menu');
            body.classList.add('hidden-show');
            menuOpen.classList.remove('hide'); // Xóa class hide nếu có
        });
    }

    if (menuClose && menuOpen) {
        menuClose.addEventListener('click', function() {
            menuOpen.classList.add('hide');
            body.classList.remove('hidden-show');
            menuOpen.classList.remove('show'); // Xóa class show nếu có
            header.classList.remove('show-menu'); // Xóa class show nếu có
        });
    }

    // Center flickity slider container on delivery page
    const rowSlider = document.querySelector('.row-slider');
    if (rowSlider) {
        // Center the entire carousel container
        rowSlider.style.display = 'flex';
        rowSlider.style.justifyContent = 'center';
        rowSlider.style.alignItems = 'center';

        // Get the flickity slider and center its content
        const flickitySlider = rowSlider.querySelector('.flickity-slider');
        if (flickitySlider) {
            flickitySlider.style.transform = 'translateX(0)';
            flickitySlider.style.left = '0';
            flickitySlider.style.textAlign = 'center';
        }
    }
    fadeOutToGroupCategory();
});

function fadeOutToGroupCategory() {
    alert('Fade out to group category triggered');    
}