<?php


namespace app\admin\controller;

use app\admin\service\AdminModel;
use app\common\model\AdminModule;
use app\common\model\Module;
use think\Db;
class Addons extends Base
{
	protected $addons;
	public function __construct()
	{
		$this->addons = new \app\admin\service\Addons();
		parent::__construct();
	}
	public function index()
	{
		$addons = new \app\admin\service\Addons();
		$list = $addons->getAddonsList();
		$this->assign("list", $list);
		return view("addons/index");
	}
	public function install()
	{
		$addons = new \app\admin\service\Addons();
		$addon_name = trim(request()->get("addon_name", ''));
		$class = get_addon_class($addon_name);
		if (!class_exists($class)) {
			$this->error("插件不存在");
		}
		$addons = new $class();
		$info = $addons->info;
		if (!$info) {
			$this->error("插件信息缺失");
		}
		session("addons_install_error", null);
		$install_flag = $addons->install();
		if (!$install_flag) {
			$this->error("执行插件预安装操作失败" . session("addons_install_error"));
		}
		if (is_array($addons->menu_info) && $addons->menu_info !== array()) {
			$menu = $addons->menu_info;
			$website = new AdminModel();
			$module_model = new Module();
			foreach ($menu as $k => $v) {
				$parent_module_info = $module_model->getInfo(["module_name" => $v["parent_module_name"]], "module_id, controller");
				if (empty($parent_module_info)) {
					$controller = $v["parent_module_name"];
				} else {
					$controller = $parent_module_info["controller"];
				}
				$method = "menu_" . $controller;
				$url = "Addons" . "/menu_" . $controller . "?addons=" . $v["hook_name"];
				$last_module_id = $module_model->getInfo(["module_name" => $v["last_module_name"]], "module_id, sort");
				$res = $website->addSytemModule($v["module_name"], $controller, $method, $parent_module_info["module_id"], $url, $v["is_menu"], $last_module_id["sort"], $v["icon_class"], $v["desc"], $v["is_control_auth"]);
				if (!$res) {
					$addons->uninstall();
					$this->error("安装菜单操作失败，请检查菜单配置");
					break;
				}
			}
		}
		$info["config"] = json_encode($addons->getOneConfig());
		$res = $this->addons->addAddons($info["name"], $info["title"], $info["desc"], $info["status"], $info["config"], $info["author"], $info["version"], $info["content"]);
		if ($res) {
			$hooks_update = $this->addons->updateHooks($addon_name);
			if ($hooks_update) {
				cache("hooks", null);
				$this->success("安装成功");
			} else {
				$this->addons->deleteAddons(["name" => $addon_name]);
				$this->error("更新钩子处插件失败,请卸载后尝试重新安装");
			}
		} else {
			$this->error("写入插件数据失败");
		}
	}
	public function uninstall()
	{
		$id = trim(request()->get("id", 0));
		$db_addons = $this->addons->getAddonsInfo(["id" => $id], "*");
		$class = get_addon_class($db_addons["name"]);
		if (!$db_addons || !class_exists($class)) {
			$this->error("插件不存在");
		}
		session("addons_uninstall_error", null);
		$addons = new $class();
		$uninstall_flag = $addons->uninstall();
		if (!$uninstall_flag) {
			$this->error("执行插件预卸载操作失败" . session("addons_uninstall_error"));
		}
		if (is_array($addons->menu_info) && $addons->menu_info !== array()) {
			$menu = $addons->menu_info;
			$module_model = new AdminModule();
			foreach ($menu as $k => $v) {
				$method = "menu_Addons";
				$module_model->destroy(["module_name" => $v["module_name"], "method" => $method]);
			}
		}
		$hooks_update = $this->addons->removeHooks($db_addons["name"]);
		if ($hooks_update === false) {
			$this->error("卸载插件所挂载的钩子数据失败");
		}
		cache("hooks", null);
		$delete = $this->addons->deleteAddons(["name" => $db_addons["name"]]);
		if ($delete === false) {
			$this->error("卸载插件失败");
		} else {
			$this->success("卸载成功");
		}
	}
	public function menu_addons()
	{
		$addons = request()->param("addons");
		$params = request()->get("params", '');
		$this->assign("params", json_decode($params, true));
		$this->assign("hook_name", $addons);
		return view("index/addonmenu");
	}
	public function execute($addons = null, $controller = null, $action = null, $addons_type = null)
	{
		if (!empty($addons) && !empty($controller) && !empty($action)) {
			if ($addons_type == null) {
				$class = get_addon_class($addons, "addon_controller", $controller);
			} else {
				$class = get_addon_class($addons_type, $addons, $controller);
			}
			if (class_exists($class)) {
				if ($model === false) {
					$this->error(lang("addon init fail"));
				}
				return \think\App::invokeMethod([$class, $action]);
			} else {
				$this->error(lang("控制器不存在" . $class));
			}
		}
	}
}