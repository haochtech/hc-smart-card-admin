<?php
 namespace app\admin\service; use app\common\model\GoodsDeleted; class GoodsDelete extends Base { function __construct() { parent::__construct(); $this->goods_del = new GoodsDeleted(); } public function getGoodsViewQueryField($condition, $field, $order = '') { goto QFd7o; V2PJZ: $list = $goods_del->alias("\x6e\x67")->join("\x79\142\x6d\x70\137\151\x6d\141\x67\x65\x73\x20\151\155\141\x67\145\x73", "\x6e\147\56\x69\x6d\141\x67\x65\163\40\x3d\40\151\x6d\x61\x67\145\163\x2e\x69\155\147\137\x69\x64", "\x6c\x65\146\x74")->order("\x6e\147\56\x63\x72\x65\x61\x74\x65\x5f\x74\151\155\145\x20\x64\x65\163\143")->where($condition)->order($order)->field($field)->paginate(20); goto af1fP; af1fP: return $list; goto z0NOP; QFd7o: $goods_del = new GoodsDeleted(); goto V2PJZ; z0NOP: } }
