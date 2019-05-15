#tp5 多模块使用
	####在application目录 按照index目录创建
		访问  域名/public/index.php/创建的模块名/控制器名/方法名xu

#虚拟域名的配置
	
#三种url访问模式 （默认是混合的）
    开启全集匹配
        route_complete_match 设为true
	路由实例 
	要 引入use think\Route; 这个类
	//这个表示 域名/test  ==> 域名/sample/Test/index访问
	Route::rule('test','sample/Test/index');


	path_info 模式

	路由模式 

	强制路由 参数	url_route_must  设置为true 强制使用路由

#定义路由
	Route::rule('路由表达式','路由地址','请求类型','路由参数（数组）','变量规则（数组）');
	
	请求类型 ： get post delete ....
			Route::rule('test','sample/Test/index','GET'); //只允许get请求
			或
			Route::get('test','sample/Test/index');
			Route::pos('test','sample/Test/index');

			Route::rule('test','sample/Test/index','GET|POST'); //只允许get,post请求


	路由参数（数组）:https://www.kancloud.cn/manual/thinkphp5/118034
			Route::rule('test','sample/Test/index','GET',['https'=>true]); //只允许get请求

	参数的获取
		//没有的参数用?传递
		域名/test/123?name=zhangsan&age=99 //id是123
			Route::get('test/:id','sample/Test/index');

			Route::post('test/:id','sample/Test/index');

		获取参数 https://www.kancloud.cn/manual/thinkphp5/118044
			使用类 Requset
			use think\Request;

			获取所有的参数
				Request::instance()->param();

			获取路由中的参数
				Request::instance()->route();
			获取? get后面的参数
				Request::instance()->get();	
		//对于这种做法是 很好的
		public function index(Request $request)
    	{
        	return 'Hello world';
    	}
#validate
		要用 validate 就要use think\Validate
		独立验证

			$data = [
			    'name'  => 'thinkphp',
			    'email' => 'thinkphp@qq.com'
			];

			
			$validate = new Validate([
			    'name'  => 'require|max:25',
			    'email' => 'email'
			]);

			//batch 批量验证
			if (!$validate->batch()->check($data)) {
			    dump($validate->getError());
			}

		验证器
			use think\Loader;

			class TestValidate extends Validate

			$validate = Loader::validate('TestValidate');

			if(!$validate->check($data)){
			    dump($validate->getError());
			}

#REST

#异常处理
        try {
            BannerModel::getBannerById($id);
        } catch (Exception $ex) {
            $ex = [
                'code' => '10001',
                'msg' => $ex->getMessage()
            ];

            //返回json数据 并http的状态是400
            return json($ex, 400);
        }

    2.自定义 异常处理类
        默认的是使用config.php 中的  // 异常处理handle类 留空使用 \think\exception\Handle
        中的 render 方法

        要使用自己自定义的异常类
            必须'exception_handle' => 'app\lib\exception\ExceptionHandler'

            在app\lib\exception\ExceptionHandler中改写 render方法

#查询构建器
    fetchSql() 可以看到使用的sql 并不会执行sql

    sql 执行自动记录日志
        database.php 开启debug 选项 为 true
        config.php 中配置
            appdebug  改为true
            'log' => [
                    // 日志记录方式，内置 file socket 支持扩展
                    //'type' => 'File',
                    'type' => 'test',
                    // 日志保存目录
                    'path' => LOG_PATH,
                    // 日志记录级别
                    'level' => ['mysql'],
                ],
        在入口文件 中引入日志的初始化
#ORM
    对象关系映射

    配置文件config.php
        default_return_type 默认输出的是html 可以改成json


	关系模型 定义
	    protected $table = '';

	hasMany('关联模型','关联模型的外键','当前模型的主键id')

	在一个模型中添加
	public function items()
    {
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    控制器中使用 with方法  说明当前的这个model 有几个字段 要去关联 其他表

    嵌套关联
        A关联B  B关联C

        hasMany 1对多
        belongsTo 1对1
        belongsToMany 多对多


#隐藏属性
    调用hidden方法


#创建文件夹 application 目录
    extra  创建的文件都会自动加载

#读取器的使用方法
    getUrlAttr($value,$data)  get字段名Attr
        $value  读取当前字段的值
        $data   当前记录的所有值
	
	
#前置操作 
    const user   = 16;
    const super   = 32;
    
    // 1.只有second这个方法才会执行first这个前置方法 
    protected $beforeActionList = [
        'first' =>['only' => 'second']
    ];
    
    // 2.只有second或three这个方法才会执行first这个前置方法 
    protected $beforeAction = [
        'first' =>['only' => 'second,three']
    ];
        
    public function first(){}
    public function second(){}
    public function three(){}
    
    Address控制器 添加修改地址 要执行前置方法
    checkPrimaryScope

#订单管理
  用户在选择商品后 向API提交包含它所选择商品的相关信息
  API在接收收到信息后 需要检查订单相关商品的库存量
  有库存 把订单数据存入数据库 等于下单成功了返回客户端信息 ，告诉客户端可以支付了
  调用我们的支付接口 进行支付
  还需要再次进行库存量检测  
  服务器这边就可以调用微信的支付接口进行支付
  微信会返回给我们一个支付结果 
  成功也需要进行库存量检查
  根据支付结果 是支付成功了才会扣库存量 失败返回一个支付失败的结果
  
  
  
  ##
  

        
    
                            
             
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  