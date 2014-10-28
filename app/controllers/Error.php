<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author East
 */
class ErrorController extends Yaf\Controller_Abstract {

	public function errorAction($exception)
    {
        switch($exception -> getCode())
        {
            case YAF\ERR\AUTOLOAD_FAILED:
            case YAF\ERR\NOTFOUND\MODULE:
            case YAF\ERR\NOTFOUND\CONTROLLER:
            case YAF\ERR\NOTFOUND\ACTION:
                header('HTTP/1.0 404 Not Found');
                break;
            default:
                header('HTTP/1.0 500 Internal Server Error');
        }
        $this -> getView() -> e = $exception ;
        $this -> getView() -> e_class = get_class($exception);
        $this -> getView() -> e_string_trace = $exception -> getTraceAsString();
        $params = $this -> getRequest() -> getParams();
        unset($params['exception']);
        $this -> getView() -> params = array_merge(
            array(),
            $params,
            $this -> getRequest() -> getPost(),
            $this -> getRequest() -> getQuery()
        );
        Yaf\Dispatcher::getInstance() -> autoRender(false);
        $conf = Yaf\Registry ::get('config');
        $errview = $conf -> application -> dispatcher -> errorview;
        $this -> getView() -> display($errview);
    }
}
