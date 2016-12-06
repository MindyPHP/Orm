<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 28/11/16
 * Time: 13:19
 */

namespace Mindy\Bundle\MindyBundle\Dashboard;

use Mindy\Bundle\MindyBundle\Admin\AdminMenu;

class AdminMenuWidget extends AbstractWidget
{
    protected $adminMenu;

    public function __construct(AdminMenu $adminMenu)
    {
        $this->adminMenu = $adminMenu;
    }

    public function getTemplate()
    {
        return 'admin/dashboard/menu.html';
    }

    public function getData()
    {
        return [
            'adminMenu' => $this->adminMenu->getMenu()
        ];
    }
}