<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 9:55 PM
 */

namespace Compose\Mvc;

/**
 * Class Controller
 *
 * MVC Controller.  Supports multiple actions and SCRUD for each actions
 * @package Compose\Mvc
 */
class Controller extends Action
{
    /**
     * Overrides to provide action name for the controller
     *
     * @inheritdoc
     * @param string $httpMethod
     * @param array $httpParams
     * @return string
     */
    protected function resolveActionName(string $httpMethod, array &$httpParams = []) : string
    {
        if(!count($httpParams)) { // index page
            $action = $this->defaultAction;
            $httpMethod = null;
        } else {
            $action = array_shift($httpParams);
            if(!count($httpParams) && $httpMethod == 'get') {
                $httpMethod = $this->defaultAction;
            }
        }

        $action = $this->filterActionName($action);
        if(!$action) {
            throw new \Exception("Unable to route.");
        }

        return $this->buildActionName($httpMethod, $action);
    }


    /**
     * Validate action name
     *
     * @note regex from php doc for function name: http://php.net/manual/en/functions.user-defined.php
     * @param string $action
     * @return bool
     */
    protected function filterActionName(string $action)
    {
        $allowedChars = ['-'];

        // if allowed chars are provided,
        // then we will need to remove them first
        if(count($allowedChars)) {
            $action = str_replace(' ', '', str_replace($allowedChars, ' ', $action));
        }

        $regex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/"; // see comment ^
        if(!preg_match($regex, $action)) {
            return null;
        }

        return $action;
    }
}