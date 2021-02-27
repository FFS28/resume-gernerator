<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Experience;
use App\Entity\KitchenIngredient;

class KitchenIngredientController extends EasyAdminController
{
    /**
     * @param KitchenIngredient $kitchenIngredient
     */
    protected function persistEntity($kitchenIngredient)
    {
        $this->update($kitchenIngredient);
        parent::persistEntity($kitchenIngredient);
    }

    /**
     * @param KitchenIngredient $kitchenIngredient
     */
    protected function updateEntity($kitchenIngredient)
    {
        $this->update($kitchenIngredient);
        parent::updateEntity($kitchenIngredient);
    }

    /**
     * @param KitchenIngredient $kitchenIngredient
     */
    private function update($kitchenIngredient)
    {
        $kitchenIngredient->setSearch($kitchenIngredient->getIngredient()->getName());
    }

    public function updateBatchAction()
    {
        $form = $this->request->request->get('batch_form');
        $ids = explode(',', $form['ids']);

        $repository = $this->em->getRepository(KitchenIngredient::class);

        foreach ($ids as $id) {
            /** @var KitchenIngredient $kitchenIngredient */
            $kitchenIngredient = $repository->find($id);

            if ($kitchenIngredient) {
                $this->update($kitchenIngredient);
            }
        }

        $this->em->flush();
    }
}
