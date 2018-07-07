<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use Gamegos\JWT\Token;
use Gamegos\JWT\Encoder;
use \Gamegos\JWT\Validator;
use \Gamegos\JWT\Exception\JWTException;

class User extends Controller
{
	public function index()
	{
		$key = '21F01979E4880E3A14C132A251C5C6CB';
		$alg = 'HS256';

		$token = new Token();
		$encoder = new Encoder();


		header('Access-Control-Allow-Origin:*'); //跨域问题
		$res['result'] = 'failed';

		$action = isset($_GET['action']) ? $_GET['action'] : '';

		if ($action == 'login') {
		    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		        $username = htmlentities($_POST['user']);
		        $password = htmlentities($_POST['pass']);

		        if ($username == 'demo' && $password == 'demo') { //用户名和密码正确，则签发tokon

					$nowtime = time();
					$token->setClaim('iss', 'sss.mf680.com');
					$token->setClaim('sub', 'sss.mf680.com');
					$token->setClaim('aud', 'world');
					$token->setClaim('iat', $nowtime);
					$token->setClaim('nbf', $nowtime + 10);
					$token->setClaim('exp', $nowtime + 60*5);
					$token->setClaim('data',array('userid'=>1,'username'=>$username));
					$encoder->encode($token, $key, $alg);

		            $res['result'] = 'success';
		            $res['jwt'] = $token->getJWT();
		        } else {
		            $res['msg'] = '用户名或密码错误!';
		        }
		    }
		    echo json_encode($res);

		} else {
		    $jwt = isset($_SERVER['HTTP_X_TOKEN']) ? $_SERVER['HTTP_X_TOKEN'] : '';
		    if (empty($jwt)) {
		        $res['msg'] = 'You do not have permission to access.';
		        echo json_encode($res);
		        exit;
		    }
			try {
			    $validator = new Validator();
			    $token = $validator->validate($jwt, $key);
			    if ($token->getExpirationTime() < time()) {
			        $res['msg'] = '请重新登录';
			    } else {
			        $res['result'] = 'success';
			        $res['info'] = $token->getClaim('data');
			    }
			} catch (JWTException $e) {
			    printf("Invalid Token:\n  %s\n", $e->getMessage());
			    //var_dump($e->getToken());
			}
			echo json_encode($res);
		}
	}
}