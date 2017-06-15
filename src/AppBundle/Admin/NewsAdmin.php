<?php

namespace AppBundle\Admin;

use AppBundle\Entity\News;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Cocur\Slugify\Slugify;


class NewsAdmin extends AbstractAdmin
{

    /**
     * Получение названия текущего файла изображения
     *
     */
    protected function getImageThumb($slug)
    {
        $dbservice = $this->getConfigurationPool()->getContainer()->get('app.database_news');
        $resQuery = $dbservice->getNewsBySlug($slug);

        if (!empty($slug)) {
            $imageName = $resQuery[0]->getImage();
            $liipm =  $this->getConfigurationPool()->getContainer()->get('liip_imagine.cache.manager');
            $imageName = !empty($imageName) ? $liipm->getBrowserPath($imageName, 'my_thumb') : '';
        }
        else {
            $imageName = '';
        }

        return $imageName;
    }

    /**
     * Формирование полей формы
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $slug = $formMapper->add('slug', 'text',['required' => false,])->getAdmin()->getSubject()->getSlug();

        $formMapper
            ->add('title', 'text', [
                'label' => 'Заголовок'
            ])
            ->add('publicationDate', 'datetime', [
                'label' => 'Дата публикации'
            ])
            ->add('content', 'textarea', [
                'label' => 'Содержание'
            ])
            ->add('active', 'checkbox', [
                'label' => 'Активен',
                'required' => false,
            ])
            ->add('description', 'text', [
                'label' => 'Описание'
            ])
            ->add('fileImage', 'file', [
                'label' => 'Изображение',
                'required' => false,
                'data_class' => null,
                'help' => '<img src="'.$this->getImageThumb($slug).'" alt="Картинки нет">',
            ])
            ->add('del', 'checkbox', [
                'label'=> 'Удалить изображение',
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
                'format' => 'd.m.Y',
                'timezone' => 'America/New_York'
            ])
            ->addIdentifier('active')
            ->addIdentifier('description')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    public function preValidate($news)
    {
        $path = $this->getConfigurationPool()->getContainer()->getParameter('img_path');
        $imgService = $this->getConfigurationPool()->getContainer()->get('app.image_manager');
        $cocur = $this->getConfigurationPool()->getContainer()->get('cocur_slugify');
        $correctPath = $imgService->upload($news, $path);

        $slug = $news->getSlug();
        $newSlug = $cocur->activateRuleset('russian')->slugify($news->getTitle());
        $news->setSlug($newSlug);

        if ($news->getDel() == 1 && !empty($news->getImage())) {
            $imgService->remove($news->getImage());
            $news->setImage(null);
        }
        elseif ($news->getDel() == 0) {
            if(empty($news->getImage()) && (empty($correctPath) || !empty($correctPath))){
                $news->setImage($correctPath);
            }
            elseif (!empty($news->getImage()) && !empty($correctPath)) {
                if($correctPath != $news->getImage()) {
                    $imgService->remove($news->getImage());
                    $news->setImage($correctPath);
                }
                else { $news->setImage($correctPath); }
            }
        }


    }

}