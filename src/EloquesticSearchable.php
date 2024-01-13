<?php

namespace mehrab\eloquestic;

use mehrab\eloquestic\Exceptions\ModelSearchableFieldsNotFoundException;

trait EloquesticSearchable
{
    /**
     * @return mixed
     * @throws ModelSearchableFieldsNotFoundException
     */
    public static function elastic(): Eloquestic
    {
        if (!isset(self::$eloquesticSearchAbles)) {
            throw new ModelSearchableFieldsNotFoundException();
        }


        $index = self::$eloquesticIndex ?? resolve(self::class)->getTable();
        $eloquestic = new Eloquestic();
        return $eloquestic->setIndex($index)->setSearchables(self::$eloquesticSearchAbles);
    }
}
