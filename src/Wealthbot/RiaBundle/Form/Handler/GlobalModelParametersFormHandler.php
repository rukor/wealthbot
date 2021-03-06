<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.06.13
 * Time: 19:15
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Handler;


use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Entity\ModelAssumption;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class GlobalModelParametersFormHandler
{
    private $form;
    private $request;
    private $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
    }

    public function process(User $ria, array $models)
    {
        if ($this->request->isMethod('post')) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->success($ria, $models);

                return true;
            }
        }

        return false;
    }

    private function success(User $ria, array $models)
    {
        /** @var ModelAssumption $data */
        $data = $this->form->getData();
        $repo = $this->em->getRepository('WealthbotAdminBundle:ModelAssumption');

        $data->setOwner($ria);
        $data->setOwnerType('ria');
        $data->setModel(null);

        foreach ($models as $model) {
            $assumption = $repo->findOneBy(
                array(
                    'owner_id' => $ria->getId(),
                    'owner_type' => 'ria',
                    'model_id' => $model->getId()
                )
            );

            if (!$assumption) {
                $assumption = new ModelAssumption();
            }

            $assumption->setModel($model);
            $assumption->setOwner($ria);
            $assumption->setOwnerType('ria');
            $assumption->setCommissionMin($data->getCommissionMin());
            $assumption->setCommissionMax($data->getCommissionMax());
            $assumption->setForecast($data->getForecast());
            $assumption->setGenerousMarketReturn($data->getGenerousMarketReturn());
            $assumption->setLowMarketReturn($data->getLowMarketReturn());

            $this->em->persist($assumption);
        }

        $this->em->persist($data);
        $this->em->flush();
    }
}