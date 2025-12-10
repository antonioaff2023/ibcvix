document.addEventListener('DOMContentLoaded', () => {
    const menuItems = document.querySelectorAll('.menu-item');

    menuItems.forEach(item => {
        const submenu = item.querySelector('.submenu');

        item.addEventListener('click', (e) => {
            e.stopPropagation();
            menuItems.forEach(i => {
                if (i !== item) {
                    i.classList.remove('active');
                    const otherSubmenu = i.querySelector('.submenu');
                    if (otherSubmenu) {
                        otherSubmenu.style.display = 'none';
                    }
                }
            });

            if (submenu) {
                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                    submenu.style.display = 'none';
                } else {
                    item.classList.add('active');
                    submenu.style.display = 'block';
                }
            }
        });
    });

    document.addEventListener('click', () => {
        menuItems.forEach(item => {
            item.classList.remove('active');
            const submenu = item.querySelector('.submenu');
            if (submenu) {
                submenu.style.display = 'none';
            }
        });
    });
});


/*Carrossel */
