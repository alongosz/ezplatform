<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\FieldType\Nameable;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value;

class Date implements Nameable
{
    public function getFieldName(
        Value $value,
        FieldDefinition $fieldDefinition,
        $languageCode
    ): string {
        /** @var \eZ\Publish\Core\FieldType\Date\Value $value */
        if ($value === null || $value->date === null) {
            return '';
        }

        return $value->date->format('Ymd');
    }
}
