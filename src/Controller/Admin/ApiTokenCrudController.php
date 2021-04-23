<?php

namespace App\Controller\Admin;

use App\Entity\ApiToken;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ApiTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApiToken::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
