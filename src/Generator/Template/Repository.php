<?php

declare(strict_types=1);

/**
 * @var string $modelName
 * @var string $modelComment
 * @var string $repositoryNamespace
 * @var bool $stupidDeveloper
 */

echo "<?php\n";

?>

declare(strict_types=1);

namespace <?= $repositoryNamespace ?>;

<?php
if ($modelComment !== '') {
    echo
        "/**\n" .
        " * " . $modelComment . "\n" .
        " */\n"
    ;
}
?>
class <?= $modelName ?>Repository extends Generated\<?= $modelName ?>Repository
{
<?php if ($stupidDeveloper) { ?>
    // here can be some method with awesome sql
<?php } ?>
}
