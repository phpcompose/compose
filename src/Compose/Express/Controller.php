<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-25
 * Time: 9:55 PM
 */

namespace Compose\Express;

use Compose\Standard\Http\Exception\HttpException;
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
    protected function generateActionMethodName(ServerRequestInterface $request) : string
    {
        $params = $this->extractRequestParams($request);
        if(count($params)) {
            $action = reset($params);
        } else {
            $action = $this->defaultAction;
        }

        $action = $this->filterActionMethodName($action, ['_']);

        if(!$this->validateActionMethodName($action)) {
            throw new HttpException("Bad method request.", 404);
        }

        return sprintf("%s%s",
            strtolower($request->getMethod()),
            ucfirst($action)
            );
    }

    protected function buildActionMethodParams(ServerRequestInterface $request) : array
    {
        $params = $this->extractRequestParams($request);
        if(count($params)) {
            array_shift($params);
        }

        return $params;
    }


    /**
     * @todo should use validation classes/components
     * @param string $action
     * @param null $allowedChars
     * @return string
     */
    protected function filterActionMethodName(string $action, $allowedChars = []) : string
    {
        $str = preg_replace('/[^a-z0-9' . implode("", $allowedChars) . ']+/i', ' ', $action);
        return str_replace(' ', '', ucwords(trim($str)));
    }


    /**
     * Validate action name
     *
     * @todo could use validator class.... thinking...
     * @note regex from php doc for function name: http://php.net/manual/en/functions.user-defined.php
     * @param string $action
     * @return bool
     */
    protected function validateActionMethodName(string $action) : bool
    {
        $regex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/"; // see comment ^
        if(!preg_match($regex, $action)) {
            return false;
        }

        return true;
    }
}