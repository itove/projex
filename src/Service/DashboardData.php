<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @author Al Zee <z@alz.ee>
 * @version
 * @todo
 */

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardData
{
    public function __construct(private ManagerRegistry $doctrine, private ChartBuilderInterface $chartBuilder)
    {
    }
    
    public function get()
    {
        $tz = new \DateTimeZone('Asia/Shanghai');

        return [
            'charts' => [
            ],
        ];
    }
}
