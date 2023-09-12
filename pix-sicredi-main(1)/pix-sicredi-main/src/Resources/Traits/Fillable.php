<?php


namespace PixSicredi\Resources\Traits;

use PixSicredi\Util\Support;

trait Fillable
{

    public function fill(array $data): self
    {
        foreach ($data as $key => $value)
        {
            $function = (is_array($value) ? 'add' : 'set' ) . Support::camel_case($key);
            if (method_exists($this, $function)) {
                $this->$function($value);
            }
        }

        return $this;
    }

}
