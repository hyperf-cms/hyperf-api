<?php
declare(strict_types=1);

namespace App\Foundation\Factory;

use App\Foundation\Facades\Log;
use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Log::channel('default', 'app');
    }
}