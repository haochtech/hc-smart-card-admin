<?php
 namespace app\admin\service; use app\common\model\AdminGroup; use app\common\model\AdminModule; use think\Session; class AdminMordel extends Base { function __construct() { parent::__construct(); $this->admin = new \app\common\model\Admin(); } }
