<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Emoji\Vendor\Symfony\Component\DependencyInjection\Exception;

/**
 * Thrown when a definition cannot be autowired.
 */
class AutowiringFailedException extends \Emoji\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException
{
    private $serviceId;
    public function __construct($serviceId, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->serviceId = $serviceId;
        parent::__construct($message, $code, $previous);
    }
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
