document.addEventListener("DOMContentLoaded", function() {
    const updateCartForms = document.querySelectorAll('form');

    updateCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(form);
            fetch('update_cart.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.cart-total').innerText = 'Total: $' + data.total.toFixed(2);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
