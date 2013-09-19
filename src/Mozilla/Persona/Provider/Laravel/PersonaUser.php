<?php
namespace Mozilla\Persona\Provider\Laravel;

use Illuminate\Auth\GenericUser;

class PersonaUser extends GenericUser
{
    public function getAuthIdentifier()
    {
        return $this->attributes['email'];
    }
}
