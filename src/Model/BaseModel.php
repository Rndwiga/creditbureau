<?php

namespace Rndwiga\CreditBureau\Model;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('metropol.database_connection');
    }
}
