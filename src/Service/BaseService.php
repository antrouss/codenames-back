<?php

/**
 * Abstract class that will be extended by all the services of the project
 *
 * This class has helper methods and constants that all the Services use.
 * PHP version 7.2
 *
 * @category   Service
 * @author     Antony Roussos <anthony@codibee.com>
 * @copyright  2019 codibee
 * @version    0.1
 */

namespace App\Service;

/**
 * Class BaseService is to be extended by all the other services.
 * 
 * In this class there are some very basics that needed to all the other
 * classes.
 */
abstract class BaseService
{
    /**
     * @var int http message code for success.
     */
    const SUCCESS = 0;
    /**
     * @var int http message code for deleted content.
     */
    const DELETED = 1000;
    /**
     * @var int http message code for validation error.
     */
    const VALIDATION_ERR = 1400;
    /**
     * @var int http message code for conflict error.
     */
    const CONFLICT_ERR = 1409;
    /**
     * @var int http message code for not found error.
     */
    const NOT_FOUND_ERR = 1404;
    /**
     * @var int http message code for failure error.
     */
    const FAILED_ERR = 1500;
    /**
     * @var int http message code for forbidden access error.
     */
    const FORBIDDEN_ERR = 1403;

    /**
     * Function calculateOffset() returns an offset integer that will be used
     * in queries to retrieve record from an order position and on.
     * 
     * This function gets current page and limit of each page to return the 
     * offset.
     *
     * @param integer $limit the limit of contents for every page.
     * @param integer $page the number of page to return.
     * @return integer the offset the integer that will be used to
     * retrieve results from a specific order position an on
     * according to the limit.
     */
    protected function calculateOffset(int $limit, int $page): int
    {
        return ($page - 1) * $limit;
    }
}
