<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 9:55 PM
 */

namespace Compose\Express;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Compose\Core\Http\Exception\HttpException;

class Controller extends Action
{
    protected
        /**
         * @var string
         */
        $defaultAction = 'index';


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
        $actionMethod = parent::resolveActionName($httpMethod, $httpParams);

        if(!count($httpParams)) {
            $action = $this->defaultAction;
        } else {
            $action = array_shift($httpParams);
        }

        $action = $this->filterActionName($action, ['-']);
        if(!$action) {
            throw new HttpException("Unable to route.");
        }

        $method = $actionMethod . $action;
        if(method_exists($this, $method)) {
            return $method;
        }

        return "{$this->actionPrefix}{$action}";
    }


    /**
     * Validate action name
     *
     * @todo could use validator class.... thinking...
     * @note regex from php doc for function name: http://php.net/manual/en/functions.user-defined.php
     * @param string $action
     * @return bool
     */
    protected function filterActionName(string $action, array $allowedChars = [])
    {
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