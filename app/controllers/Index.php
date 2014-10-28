<?php
/**
 * @name IndexController
 * @desc 默认控制器
 * charset:utf-8
 */
class IndexController extends Ctrl_Base {

	public function indexAction($name = 'a') {
		Yaf\Dispatcher::getInstance() -> autoRender(false);
		#echo time();
		#$this -> forward('weitool', 'index', 'index');
		$this -> redirect(URL_ENTRANCE . '/');
	}
}
