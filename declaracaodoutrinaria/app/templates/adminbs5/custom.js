// Redireciona o botão sair do layout.html

document.addEventListener("DOMContentLoaded", function () {

    const logoutBtn = document.getElementById('logout_btn');
    const pdfBTN = document.getElementById('pdf_btn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', (event) => {
            event.preventDefault();

            if (window.history.length > 1) {
                window.location.href = 'http://ibcvix.com.br';
            } else {
                window.location.href = 'https://www.google.com.br';
            }
        });
    }

    // if (pdfBTN) {
    //     pdfBTN.addEventListener('click', (event) => {
    //         event.preventDefault();

    //         if (window.history.length > 1) {
    //             window.location.href = 'app/control/DeclaracaoPDF.php';
    //         } else {
    //             window.location.href = 'https://www.google.com.br';
    //         }
    //     });
    // }

        

        

})