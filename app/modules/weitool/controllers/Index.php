<?php
/**
 * @name IndexController
 * @author East
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 * charset:utf-8
 */
class IndexController extends Ctrl_Base {
    protected $layout = 'easyui';
    
    public function init() {
        parent::init();
        $this -> getView() -> assign('pagetitle', $this -> getRequest() -> getModuleName());
    }
    
	public function indexAction() {
        $wikilist = $this -> buildwikimenu();
        $this -> getView() -> assign('wikilist', $wikilist);
	}
	
	public function wikiAction() {
        $title = empty($_REQUEST['title']) ? '' : $_REQUEST['title'];
        $str = $this -> getwikicontent($title);
        $this -> getView() -> assign('content', $str);
        
	}
	
	public function weisimulatorAction() {
	}
	
	public function simurlAction() {
        Yaf\Dispatcher::getInstance() -> autoRender(false);
        $act = empty($_REQUEST['act']) ? '' : $_REQUEST['act'];
        $token = empty($_REQUEST['mptoken']) ? '' : $_REQUEST['mptoken'];
        $url = empty($_REQUEST['mpurl']) ? '' : $_REQUEST['mpurl'];

        $timestamp = time();
        $nonce = time();
        $echostr = time();
        $tmparr = array($token, $timestamp, $nonce);
        sort($tmparr, SORT_STRING);
        $tmpstr = implode($tmparr);
        $signature = sha1($tmpstr);
        $concat = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $concat . 'token=' . $token . '&signature=' . $signature . '&timestamp=' . $timestamp . '&nonce=' . $nonce ;
        
        if($act == 'send') {
            $postxml = $_REQUEST['mpxml'];
            $header = "Content-Type: application/xml\r\n";
            $result = $this -> sendhttp($url, $postxml, 'POST', $header);
            $result = trim($result);
            echo $result;
        }
        if($act == 'verify') {
            $url .= '&echostr=' . $echostr;
            $result =$this -> httpcurl($url, '', false);
            $result = trim($result);
            header('content-type:application/json;charset=utf-8');
            $status = 0;
            if($result == $echostr) {
                $status = 1;
            }
            echo json_encode(array('status' => $status, 'result' => $result, 'url' => $url));
        }
	}
	
	private function sendhttp($url, $content = '', $method = 'POST', $header = '') {
        $opt = array("http" => array(
            "method" => $method,
            "header" => $header,
            "content" => $content
        ));
        $ctx = stream_context_create($opt);
        $str = file_get_contents($url, false, $ctx);
        return $str;
	}
	
	private function httpcurl($url, $post_data, $ispost = true) {
        $ua = 'MicroMessenger/6.0 NetType/WIFI';
        $ch = curl_init($url); //初始化
        curl_setopt($ch, CURLOPT_HEADER, 0); //返回header部分
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //返回字符串，而非直接输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        #curl_setopt($ch , CURLOPT_COOKIEFILE,  'cookie0.txt' );  //读取   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml", "User-Agent: ". $ua));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST,1 );
            curl_setopt($ch , CURLOPT_POST, count ( $post_data ));
            curl_setopt($ch , CURLOPT_POSTFIELDS, $post_data );
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
	}
	
	public function wikimenuAction() {
        Yaf\Dispatcher::getInstance() -> autoRender(false);
        $jsonfile = PATH_BASE . '/static/wiki/index.json';
        $str = '';
        if (file_exists($jsonfile)) $str = file_get_contents($jsonfile);
        $json = json_decode($str);
        $arr = array();
        $arr[0] = array('id' => 0, 'text' => '首页', 'iconCls' => 'icon-home', 'attributes' => array('haschild' => false));
        if($json) {
            foreach($json as $k1 => $v1) {
                $arr[$v1 -> itemid] = array('id' => $v1 -> itemid,'text' => $v1 -> title, 'attributes' => array('haschild' => (count($v1 -> child) > 0) ));
                if(count($v1 -> child) > 0) {
                    $arr[$v1 -> itemid]['state'] = 'closed';
                    $arr[$v1 -> itemid]['children'] = array();
                    foreach($v1 -> child as $k2 => $v2) {
                        $arr[$v1 -> itemid]['children'][$k2] = array('id' => $v1 -> itemid . '0' . $v2 -> itemid + 1,'text' => $v2 -> title, 'attributes' => array('haschild' => false));
                        if(isset( $v2 -> url)) $arr[$v1 -> itemid]['children'][$k2]['attributes']['url'] = $v2 -> url;
                    }
                } else {
                    $arr[$v1 -> itemid]['children'] = array();
                }
            }
        }
        header('content-type:application/json;charset=utf-8');
        exit(json_encode($arr));
	}
	
	private function buildwikimenu() {
        $jsonfile = PATH_BASE . '/static/wiki/index.json';
        $menu = '';
        $str = '';
        if (file_exists($jsonfile)) $str = file_get_contents($jsonfile);
        $json = json_decode($str);
        $menu .= '<li><span><a class="ealink1" href="'. Helper\Html::url(array('module' => $this -> _request -> module, 'controller' => $this -> _request -> controller, 'action'=>'wiki')) .'?title=首页">首页</a></span><ul></ul></li>';
        if($json) {
            foreach($json as $k1 => $v1) {
                $menu .= '<li  data-options="state:\'closed\'">';
                #$menu .= '<a href="">'.$v1 -> title.'</a><ul>';
                if(count($v1 -> child) < 1) {
                    $menu .= '<span><a class="ealink1" href="'. Helper\Html::url(array('module' => $this -> _request -> module, 'controller' => $this -> _request -> controller, 'action'=>'wiki')) .'?title='. $v1 -> title .'">'.$v1 -> title.'</a></span>';
                } else {
                    $menu .= '<span>';
                    $menu .= '<a>'.$v1 -> title.'</a>';
                    #$menu .= '<a class="ealink1" href="'. Helper\Html::url(array('module' => $this -> _request -> module, 'controller' => $this -> _request -> controller, 'action'=>'wiki')) .'?title='. $v1 -> title .'">'.$v1 -> title.'</a>';
                    $menu .= '</span>';
                    $menu .= '<ul>';
                    foreach($v1 -> child as $k2 => $v2) {
                        $menu .= '<li>';
                        $menu .= '<span>';
                        $menu .= '<a class="ealink1" href="'. Helper\Html::url(array('module' => $this -> _request -> module, 'controller' => $this -> _request -> controller, 'action'=>'wiki')) .'?title='. $v2 -> title .'">'.$v2 -> title.'</a>';
                        $menu .= '</span>';
                        $menu .= '</li>';
                    }
                    $menu .= '</ul>';
                }
                $menu .= '</li>';
            }
        }
        return $menu;
	}
	
	private function getwikicontent($title) {
        $title1 = $title;
        if(PHP_OS == 'WINNT') $title1 = mb_convert_encoding($title1, 'gb2312', 'utf-8');
        $this -> getView() -> assign('pagetitle', $title);
        $this -> getView() -> assign('content', $title);
        $file = PATH_BASE . '/static/wiki/txt/' . $title1 . '.txt';
        $str = '';
        if(file_exists($file)) $str = file_get_contents($file);
        if(!empty($str)) {
            $pattern = '|src=\"(?<img>.+?)\"|';
            $str = preg_replace_callback($pattern, 'self::modifywikiimg', $str);
        }
        return $str;
	}
	
	private function modifywikiimg($img) {
        return 'src="' . URL_BASE . 'static/' .$img['img'].'"';
	}
	
	
}
