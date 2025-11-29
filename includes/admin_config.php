// Add this function to includes/config.php
function admin_url($path = '') {
    return BASE_URL . 'admin/' . $path;
}

function site_url($path = '') {
    return BASE_URL . $path;
}