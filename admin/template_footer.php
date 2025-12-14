</main> </div> </div> 

<script>
        document.addEventListener('DOMContentLoaded', () => {
            
            const notificationCount = document.getElementById('notification-count');

            function checkNotifications() {
                fetch('check_notifications.php')
                    .then(response => response.text())
                    .then(count => {
                        const numCount = parseInt(count.trim(), 10);
                        
                        if (numCount > 0) {
                            notificationCount.textContent = numCount;
                            notificationCount.style.display = 'flex';
                        } else {
                            notificationCount.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking notifications:', error);
                    });
            }

            checkNotifications(); 
            setInterval(checkNotifications, 15000); 

        });
    </script>

</body>
</html>