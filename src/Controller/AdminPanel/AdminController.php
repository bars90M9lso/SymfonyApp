<?php

namespace App\Controller\AdminPanel;

use function Symfony\Component\Translation\t;
use Symfony\Component\HttpFoundation\Response;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[AdminDashboard(routePath: '/admin/{_locale}', routeName: 'admin')]
class AdminController extends AbstractDashboardController
{
    public function index(): Response
    {
        // Проверяем, авторизован ли пользователь
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) 
        {
            return $this->redirectToRoute('login');
        }

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator->setController(TableCrudController::class)->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Административная панель')
            ->renderContentMaximized()
            ->setDefaultColorScheme('dark')

            // Локализация
            ->setTranslationDomain('messages')
            ->useEntityTranslations()
            ->setLocales(['ru', 'en'])
        ;
    }

    public function configureMenuItems(): iterable
    {   
        $menuItems = [
            MenuItem::section("section.user"),
            MenuItem::linkTo(TableCrudController::class, t('menu.tables'), 'fa-solid fa-tablet-screen-button'),
            MenuItem::linkTo(ListGuestCrudController::class, t('menu.guests'), 'fa fa-users'),
        ];

        if ($this->isGranted('ROLE_ADMIN'))
        {
            $menuItems[] = MenuItem::section("section.admin");
            $menuItems[] = MenuItem::linkTo(UserCrudController::class, t('menu.user'), 'fa fa-users');   
        }

        return $menuItems;
    }
}
