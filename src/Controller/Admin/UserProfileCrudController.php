<?php

namespace App\Controller\Admin;

use App\Entity\UserProfile;
use App\Service\LocaleService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserProfileCrudController extends AbstractCrudController
{
    private LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setFormTypeOption('disabled', true),
            TextField::new('forenames'),
            TextField::new('surname'),
            LocaleField::new('locale')
                ->setFormTypeOptions([
                    'choices' => $this->localeService->getSupportedLocales(true),
                    'choice_loader' => null,
                ]),
            DateTimeField::new('createdAt')
                ->setFormTypeOption('disabled', true),
            DateTimeField::new('updatedAt')
                ->setFormTypeOption('disabled', true),
        ];
    }
}
