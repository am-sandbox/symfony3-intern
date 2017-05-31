<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use AppBundle\Form\FormType;


class NewsAdmin extends AbstractAdmin
{
    /**
     * Формирование полей формы
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->add('title', 'text')
            ->add('slug', 'text')
            ->add('publicationDate', 'datetime')
            ->add('content', 'textarea')
            ->add('active', 'checkbox')
            ->add('description', 'text')
            ->add('image', 'file', [
                'label' => 'Изображение',
                'required' => false,
                'data_class' => null,
            ])
            ->add('del', 'checkbox', [
                'label'=> 'Удалить изображение' ,
                'required' => false,
            ]);



    }

    /**
     * Формирование полей списка записей
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->addIdentifier('image')
            ->addIdentifier('slug')
            ->addIdentifier('publicationDate', 'datetime', [
                'format' => 'Y-m-d',
                'timezone' => 'America/New_York'
            ])
            ->addIdentifier('content')
            ->addIdentifier('active')
            ->addIdentifier('description')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    public function preUpdate($image)
    {
        $imgService = $this->getConfigurationPool()->getContainer()->get('app.image_upload');
        $correctPath = $imgService->upload($image);
        $image->setImage($correctPath);

        if($image->getDel() == 1) { $image->setImage(null); }

    }
}