<?php
namespace Tecnotek\Bundle\AsiloBundle\Twig\Extensions;

use Tecnotek\Bundle\AsiloBundle\Entity\User;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item5Enum;
use Tecnotek\Bundle\AsiloBundle\Util\Enum\Item6Enum;

/**
 *
 */
class MenuExtension extends \Twig_Extension
{
    private $em;
    private $conn;
    private $translator;
    private $session;
    private $securityContext;
    private $logger;
    private $router;

    public function __construct(\Doctrine\ORM\EntityManager $em, $translator, $session, $securityContext,
                                $logger, $router) {
        $this->em = $em;
        $this->conn = $em->getConnection();
        $this->translator = $translator;
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->logger = $logger;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            'renderMenu' => new \Twig_Function_Method($this, 'renderMenu'),
        );
    }

    public function renderMenu( ) {
        $html = "";
        $user = $this->securityContext->getToken()->getUser();
        $isAdmin = $this->securityContext->isGranted('ROLE_ADMIN');
        $allowed = array();
        foreach($user->getMenuOptions() as $menuOption){
            array_push($allowed, $menuOption->getId());
        }

        $dql = "SELECT e FROM TecnotekAsiloBundle:ActionMenu e WHERE e.parent is null order by e.sortOrder";
        $query = $this->em->createQuery($dql);
        $headers = $query->getResult();

        $html = '';
        $menu = '';
        foreach($headers as $header){
            $menu = '';
            foreach($header->getChildrens() as $children){
                $submenuHtml = '';
                if(sizeof($children->getChildrens()) > 0){
                    foreach($children->getChildrens() as $submenu){
                        if($isAdmin || in_array($submenu->getId(), $allowed)){
                            $submenuHtml .= '<li class=""><a href="' .
                                $submenu->getUrl($children) . '">'
                                . $this->translator->trans($submenu->getLabel()) . '</a>';
                            $submenuHtml .= '</li>';
                        }
                    }

                    if($submenuHtml != ''){
                        $menu .= '<li class="has-submenu"><a href="' .
                            $this->getUrl($children) . '">'
                            . $this->translator->trans($children->getLabel()) . '</a>';

                        $submenuHtml = '<ul class="dropdown">' . $submenuHtml . '</ul>';

                        $menu .= $submenuHtml . '</li>';
                    }

                } else { // The children (second level) do not has a submenu
                    if($isAdmin || in_array($children->getId(), $allowed)){
                        $menu .= '<li><a href="' .
                            $this->getUrl($children) . '">'
                            . $this->translator->trans($children->getLabel()) . '</a></li>';
                    }
                }
            }//End of childrens

            if($menu != ''){
                $menu = '<ul class="sub">' . $menu . '</ul>';

                $html .= '<li id="menu_users_item" class="sub-menu"><a href="' .
                    $this->getUrl($header) . '">' .
                    ($header->getCssClass() != ""? '<i class="' . $header->getCssClass() . '"></i>':'') .
                    '<span>' . $this->translator->trans($header->getLabel()) . '</span>' .
		            '<span class="menu-arrow arrow_carrot-right"></span>' .
                    '</a>';
                $html .= $menu;
                $html .= '</li>';
            }
        }

        return $html;
    }

    private function getUrl($actionMenu) {
        $url = ($actionMenu->getRoute() == "#"? "#":$this->router->generate($actionMenu->getRoute()));
        $url .= $actionMenu->getAdditionalPath();
        return $url;
    }

    public function twig_include_raw($file) {
        return file_get_contents($file);
    }

    public function getFilters() {
        return array(
            'renderPercentage' => new \Twig_Filter_Method($this, 'renderPercentage'),
        );
    }

    public function renderPercentage( $value ) {
        return round($value, 1) . "%";
    }

    public function getName()
    {
        return 'menu_twig_extension';
    }
}