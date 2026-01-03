<?php
/**
 * Admin Footer Component
 */
?>
    </div> <!-- End main content wrapper -->
    
    <!-- JavaScript -->
    <script src="<?php echo ASSETS_URL; ?>/js/admin.js"></script>
    
    <?php if ($flash = getFlashMessage()): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info'); ?>',
            title: '<?php echo ucfirst($flash['type']); ?>',
            text: '<?php echo addslashes($flash['message']); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php endif; ?>
</body>
</html>