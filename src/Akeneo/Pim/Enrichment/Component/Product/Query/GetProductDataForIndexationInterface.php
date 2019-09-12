<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductDataForIndexationInterface
{
    /**
     * Returns an associative array for product indexing
     *
     * @param int $productId
     * @return array
     */
    public function fromProductId(int $productId): array;
}
