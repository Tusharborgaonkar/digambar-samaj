<?php
if (copy('registration.php', 'edit-profile.php')) {
    echo 'success';
} else {
    echo 'fail';
}
?>
