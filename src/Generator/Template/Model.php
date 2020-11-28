<?php

declare(strict_types=1);

/**
 * @var string $modelName
 * @var string $modelComment
 * @var string $modelNamespace
 * @var bool $stupidDeveloper
 */

echo "<?php\n";

?>

declare(strict_types=1);

namespace <?= $modelNamespace ?>;

<?php
    if ($modelComment !== '') {
        echo
            "/**\n" .
            " * " . $modelComment . "\n" .
            " */\n"
        ;
    }
?>
final class <?= $modelName ?> extends Generated\<?= $modelName."\n" ?>
{
<?php if ($stupidDeveloper) { ?>
    // here can be some custom getters
<?php } ?>
}
