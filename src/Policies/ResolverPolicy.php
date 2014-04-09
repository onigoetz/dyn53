<?php

namespace Onigoetz\Dyn53\Policies;

/**
 * Interface for the Policies
 *
 * @author Stéphane Goetz <onigoetz@onigoetz.ch>
 */
interface ResolverPolicy
{
    public function getIP();
}
