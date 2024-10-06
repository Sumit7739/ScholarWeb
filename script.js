document.getElementById('hamburger').onclick = function(event) {
    event.stopPropagation();
    const menu = document.getElementById('menu');
    const hamburger = document.getElementById('hamburger');
    menu.classList.toggle('show');
    hamburger.classList.toggle('active');
};


document.addEventListener('click', function(event) {
    const menu = document.getElementById('menu');
    const hamburger = document.getElementById('hamburger');


    if (!hamburger.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.remove('show');
        hamburger.classList.remove('active');
    }
});
