(function(){
    const sidebar = document.getElementById('sidebar');
    const menu = document.querySelector('.menu-btn');
    const close = document.querySelector('.sidebar-close');

    if (!sidebar) return;

    window.toggleSidebar = function(){
        sidebar.classList.toggle('open');
    };

    // Tutup menu saat pengguna memilih halaman di HP.
    // Untuk Logout, navigasi tetap berjalan normal.
    sidebar.querySelectorAll('a').forEach(function(link){
        let moved = false;
        let startY = 0;

        link.addEventListener('touchstart', function(e){
            moved = false;
            startY = e.touches[0].clientY;
        }, {passive: true});

        link.addEventListener('touchmove', function(e){
            if (Math.abs(e.touches[0].clientY - startY) > 8) {
                moved = true;
            }
        }, {passive: true});

        link.addEventListener('click', function(){
            if (window.innerWidth <= 850 && !moved) {
                sidebar.classList.remove('open');
            }
        });
    });

    // Klik di luar sidebar menutup menu mobile.
    document.addEventListener('click', function(e){
        if (
            window.innerWidth <= 850 &&
            sidebar.classList.contains('open') &&
            menu &&
            !sidebar.contains(e.target) &&
            !menu.contains(e.target)
        ) {
            sidebar.classList.remove('open');
        }
    });

    // Saat kembali ke desktop, reset posisi sidebar.
    window.addEventListener('resize', function(){
        if (window.innerWidth > 850) {
            sidebar.classList.remove('open');
        }
    });
})();
