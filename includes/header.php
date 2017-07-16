<!doctype html>
<html>
    <head>
        <meta charset="utf-8">

		<?php if (isset($title)): ?>
        <title>FMA - <?php echo escape($title); ?></title>
		<?php else: ?>
        <title>FMA</title>
		<?php endif; ?>
    </head>
    <body>

        <nav>
            <ul>
                <li>
                    <a href="<?php echo url("index.php"); ?>">Index</a>
                </li>
                <?php if (is_authenticated()): ?>
                <li>
                    <a href="<?php echo url("logout.php"); ?>">Logout</a>
                </li>
                <?php else: ?>
                <li>
                    <a href="<?php echo url("register.php"); ?>">Register</a>
                </li>
                <li>
                    <a href="<?php echo url("login.php"); ?>">Login</a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo url("private.php"); ?>">Private page</a>
                </li>
                <li>
                    <a href="<?php echo url("public.php"); ?>">Public page</a>
                </li>
            </ul>
        </nav>

<?php
if (is_authenticated()) {
    $user = get_session_user();

    if (isset($user)) {
        echo "Welcome ".escape($user['full_name']).' you are logged in as '.escape($user['username']);
    } else {
        echo "Welcome guest";
    }
}
?>
