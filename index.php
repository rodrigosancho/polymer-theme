<?php include 'header.php'; ?>
<!-- App -->
<polymer-theme-shell menus='<?= get_menus(); ?>'
                     templates='<?= json_encode($templates); ?>'
                     state='<?= json_encode($state); ?>'></polymer-theme-shell>
<?php include 'footer.php';
