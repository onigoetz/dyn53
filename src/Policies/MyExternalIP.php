<?php

namespace Onigoetz\Dyn53\Policies;

/**
 * Resolve your IP through myexternalip.com
 *
 * @author Stéphane Goetz <onigoetz@onigoetz.ch>
 */
class MyExternalIP implements ResolverPolicy
{

    public function getIP()
    {
        //TODO :: use guzzle
        return trim(file_get_contents('http://myexternalip.com/raw'));
    }
}
