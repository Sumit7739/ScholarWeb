
document.getElementById('hamburger').onclick = function() {
    const menu = document.getElementById('menu');
    const hamburger = document.getElementById('hamburger');
    menu.classList.toggle('show'); // Toggle the 'show' class
    hamburger.classList.toggle('active'); // Toggle the 'active' class
};
