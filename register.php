<?php
require_once 'includes/functions.php';

if (isset($_POST['register'])) {
    $result = register($_POST);

    if ($result === true) {
        header('Location: '.url('index.php'));
    } else {
        $errors = $result;
    }
} else {
    $errors = array();
}

$title = 'Register';
require_once 'includes/header.php';
?>

<main>
    <h1>Register</h1>

<?php echo render_errors($errors); ?>

    <form method="post" action="<?php echo url($_SERVER['PHP_SELF']); ?>">
        <dl>
            <?php echo render_field('full_name', 'text', 'Full Name', 'Must be alphabetical under 64 characters'); ?>
            <?php echo render_field('email', 'email', 'Email address', 'Must be a valid email address e.g. john@smith.com'); ?>
            <?php echo render_field('username', 'text', 'Username', 'Must be alphabetical between 2 and 32 characters'); ?>
            <?php echo render_field('password', 'password', 'Password', 'Must be at least 6 characters'); ?>
        </dl>

        <input type="submit" name="register" value="Register" />
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
