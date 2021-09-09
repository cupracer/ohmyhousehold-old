<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->setFormTypeOption('disabled', true),
            EmailField::new('email'),
            ArrayField::new('roles')
                ->onlyOnIndex(),
            ChoiceField::new('roles')
                ->hideOnIndex()
                ->setChoices(User::ROLES)
                ->allowMultipleChoices()
                ->renderExpanded(),
            TextField::new('password'),
            BooleanField::new('isVerified'),
            DateTimeField::new('createdAt')
                ->setFormTypeOption('disabled', true),
            DateTimeField::new('updatedAt')
                ->setFormTypeOption('disabled', true),
        ];
    }
}
