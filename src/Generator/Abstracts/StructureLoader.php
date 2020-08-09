<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Abstracts;

abstract class StructureLoader
{
    /**
     * method load structure for all models from some source
     *
     * @return ModelStructure[]
     */
    public function loadModels(): array
    {
        $models = $this->loadStructure();
        $checkedModels = [];

        foreach ($models as $model) {
            if (count($model->getPrimaryKeys()) === 0) {
                throw new \LogicException('For model: "'.$model->getName().'" are primaryKeys empty. All models must have one or more property as primary (unique) key!');
            }

            $notAssignableKeys = 0;
            foreach ($model->getPrimaryKeys() as $primaryKey) {
                if (! $primaryKey->isAssignable()) {
                    $notAssignableKeys++;
                }
            }

            if ($notAssignableKeys > 1) {
                throw new \LogicException('Model: "'.$model->getName().'" has to many not assignable primaryKeys. Maximum of loaded primary keys from persist layer is one!');
            }

            $simpleParams = $model->getSimpleParams();
            usort($simpleParams, function (ModelProperty $a, ModelProperty $b): int {
                if ($a->getDefaultValue() === null && $b->getDefaultValue() !== null) {
                    return 1;
                }

                return -1;
            });

            $checkedModels[] = new ModelStructure($model->getName(), $model->getComment(), $model->getPrimaryKeys(), $model->getAdditionalKeys(), $simpleParams);
        }

        return $checkedModels;
    }

    /**
     * method load structure from some source
     *
     * @return ModelStructure[]
     */
    abstract protected function loadStructure(): array;
}
