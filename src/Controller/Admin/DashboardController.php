<?php

namespace App\Controller\Admin;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Entity\UserProfile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private string $siteName;

    /**
     * DashboardController constructor.
     * @param AdminUrlGenerator $adminUrlGenerator
     * @param string $siteName
     */
    public function __construct(AdminUrlGenerator $adminUrlGenerator, string $siteName)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->siteName = $siteName;
    }

    #[Route('/', name: 'app_admin_index')]
    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->siteName);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoRoute('Back to website', 'fas fa-home', 'start');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('User profiles', 'fas fa-user', UserProfile::class);
        yield MenuItem::linkToCrud('API tokens', 'fas fa-user', ApiToken::class);
    }
}
