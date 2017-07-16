<?php
require_once 'includes/functions.php';

if (isset($_POST['login'])) {
    $result = login($_POST);

    if ($result === true) {
        header('Location: '.url('index.php'));
    } else {
        $errors = $result;
    }
} else {
    $errors = array();
}

$title = 'Login';
require_once 'includes/header.php';
?>

<main>
    <h1>Login</h1>

<?php echo render_errors($errors); ?>

    <form method="post" action="<?php echo url($_SERVER['PHP_SELF']); ?>">
        <dl>
<?php echo render_field('username', 'text', 'Username'); ?>
<?php echo render_field('password', 'password', 'Password'); ?>
        </dl>
        <input type="submit" name="login" value="Login" />
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
