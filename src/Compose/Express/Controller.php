<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 9:55 PM
 */

namespace Compose\Express;

use Psr\Http\Message\ServerRequestInterface;

class Controller extends Action
{
    protected
        /**
         * @var string
         */
        $defaultAction = 'index';


    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function generateHandlerMethodName(ServerRequestInterface $request) : string
    {
        $params = $this->extractRequestParams($request);
        if(count($params)) {
            $action = reset($params);
        } else {
            $action = $this->defaultAction;
        }

        if(!$this->validateActionName($action)) {

        }

        return sprintf("%s%s",
            strtolower($request->getMethod()),
            ucfirst($action)
            );
    }

    /**
     * Validate action name
     *
     * @todo could use validator class.... thinking...
     * @note regex from php doc for function name: http://php.net/manual/en/functions.user-defined.php
     * @param string $action
     * @return bool
     */
    protected function validateActionName(string $action) : bool
    {
        $regex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/"; // see comment ^
        if(!preg_match($regex, $action)) {
            return false;
        }

        return true;
    }
}