<?php
use Workerman\Worker;

require_once './Workerman/Autoloader.php';
// 初始化一个worker容器，监听1234端口
$worker = new Worker('websocket://0.0.0.0:6666');
// 这里进程数必须设置为1
$worker->count = 1;
// worker进程启动后建立一个内部通讯端口
$worker->onWorkerStart = function ($worker) {
    // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
    $inner_text_worker = new Worker('Text://0.0.0.0:8888');
    $inner_text_worker->onMessage = function ($connection, $buffer) {
        global $worker;

    //如果没有登陆，这里需要司机端留一个API验证司机token
        if (checkIfUserLogin($token) != 'ok') {
            $buffer = "0";
        }
        // $data数组格式，里面有uid，表示向那个uid的页面推送数据
        $data = json_decode($buffer, true);
        $uid = $data['uid'];
        // 通过workerman，向uid的页面推送数据
        $ret = sendMessageByUid($uid, $buffer);
        // 返回推送结果
        $connection->send($ret ? 'ok' : 'fail');
    };
    $inner_text_worker->listen();
};
// 新增加一个属性，用来保存uid到connection的映射
$worker->uidConnections = array();
// 当有客户端发来消息时执行的回调函数
$worker->onMessage = function ($connection, $data) use ($worker) {
    // 判断当前客户端是否已经验证,既是否设置了uid
    if (!isset($connection->uid)) {
    // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
        $connection->uid = $data;
       /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
        * 实现针对特定uid推送数据
        */
        $worker->uidConnections[$connection->uid] = $connection;
        return;
    }
};

// 当有客户端连接断开时
$worker->onClose = function ($connection) use ($worker) {
    global $worker;
    if (isset($connection->uid)) {
    // 连接断开时删除映射
        unset($worker->uidConnections[$connection->uid]);
    }
};

// 向所有验证的用户推送数据
function broadcast($message)
{
    global $worker;
    foreach ($worker->uidConnections as $connection) {
        $connection->send($message);
    }
}

// 针对uid推送数据
function sendMessageByUid($uid, $message)
{
    global $worker;
    if (isset($worker->uidConnections[$uid])) {
        $connection = $worker->uidConnections[$uid];
        $connection->send($message);
        return true;
    }
    return false;
}

function checkIfUserLogin($token)
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_PORT => "8000",
      CURLOPT_URL => "http://localhost:8000/api/checkDriverLocation",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_HTTPHEADER => array(
        "authorization: Bearer  $token",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}
// 运行所有的worker（其实当前只定义了一个）
Worker::runAll();
