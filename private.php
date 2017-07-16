<?php
require_once 'includes/functions.php';

$title = 'Private';
require_once 'includes/header.php';
?>

<main>
    <h1>Private</h1>

<?php if (is_authenticated()): ?>
    <p>
        Ribeye pancetta pork belly salami turkey rump tongue picanha boudin swine brisket kielbasa bresaola. Cow ball tip pancetta, biltong filet mignon spare ribs corned beef pork tenderloin tail. Pork belly capicola shank tenderloin ribeye turducken short ribs beef rump pastrami landjaeger tri-tip. Jowl short loin bresaola tri-tip, shank pork bacon. Alcatra ball tip pig prosciutto swine fatback sirloin.
    </p>
<?php else: ?>
    <p>Please <a href="<?php echo url('login.php'); ?>">login</a> to continue</p>
<?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
