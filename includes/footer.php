    </div> <!-- Cierra container -->
    
    <script>
        // Animación de entrada para las tarjetas
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.bento-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Función para copiar credenciales al formulario de login
        function fillCredentials(email, password) {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            if (emailField && passwordField) {
                emailField.value = email;
                passwordField.value = password;
            }
        }
    </script>
</body>
</html>