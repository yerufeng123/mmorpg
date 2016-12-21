<?php

/**
 * 游戏类，单例模式
 * 游戏可以让红蓝双方玩家进入
 * 游戏胜利条件：对方全部战死，或总时间到达时，剩余人数多的一方胜利
 */
class Game {

    const TERM1 = 'red';
    const TERM2 = 'blue';
    const LOGTYPE1 = 'attack'; //攻击类型日志
    const LOGTYPE2 = 'die'; //死亡类型日志

    public static $gameName = "生死决斗"; //游戏名
    public static $totalTime = "100"; //游戏总时间(秒);
    public static $game; //游戏单例
    public $log = array(); //战斗日志
    public $players_red = array(); //红方玩家数组
    public $players_blue = array(); //蓝方玩家数组

    private function __constract() {
        
    }

    //获取游戏对象单例
    static function getInstance() {
        if (empty(self::$game)) {
            self::$game = new Game();
        }
        return self::$game;
    }

    //玩家加入
    public function addPlayer($name, Profession $profession) {
        $playernum = array(self::TERM1 => count($this->players_red), self::TERM2 => count($this->players_blue));
        $playerstyle = array_keys($playernum, min($playernum));
        switch ($playerstyle[0]) {
            case self::TERM1:
                $this->_addPlayerRed($name, $profession);
                break;
            case self::TERM2:
                $this->_addPlayerBlue($name, $profession);
                break;
        }
    }

    //红方玩家加入
    private function _addPlayerRed($name, Profession $profession) {
        $player = new RedPlayer($name, $profession);
        array_push($this->players_red, $player);
        $player->term = Game::TERM1;
    }

    //蓝方玩家加入
    private function _addPlayerBlue($name, Profession $profession) {
        $player = new BluePlayer($name, $profession);
        array_push($this->players_blue, $player);
        $player->term = Game::TERM2;
    }

    //玩家死亡退出
    public function exitPlayer(&$players, Player $die_player) {
        foreach ($players as $key => $player) {
            if ($player === $die_player) {
                array_splice($players, $key, 1);
                break;
            }
        }
    }

    //出战玩家
    public function turnPlayer() {
        $players = array_merge($this->players_red, $this->players_blue); //合并双方玩家
        foreach ($players as $player) {
            $players_array[$player->name] = $player;
            $agilesIndex[$player->name] = $player->agileIndex;
        }
        asort($agilesIndex);
        foreach ($agilesIndex as $k => $v) {
            $newplayers[] = $players_array[$k];
        }
        $player = array_pop($newplayers);
        foreach ($newplayers as $newplayer) {
            $newplayer->agileIndex = ($newplayer->agileIndex) + 50;
        }
        return $player;
    }

    //被攻击玩家
    //$player 攻击者
    public function attackedPlayer(Player $player) {
        $game = Game::getInstance();
        switch ($player->term) {
            case Game::TERM1 :

                $term2 = $game->players_blue;
                $num = rand(0, count($term2) - 1);
                return $term2[$num];
            case Game::TERM2 :

                $term2 = $game->players_red;
                $num = rand(0, count($term2) - 1);
                return $term2[$num];
        }
    }

    //获取战斗过程日志
    public function getLog() {
        $i = 0;
        while ($this->_attackProcess()) {
            
        }
        return json_encode($this->log);
        //$a = '[{"logstyle":"attack","term":"blue","atk":"\u6708\u795e","def":"\u9ece\u660e\u4e4b\u65a7","defhp":872.6,"atkindex":102,"text":"\u9ece\u660e\u4e4b\u65a7\u554a\u4e86\u4e00\u58f0\uff0c\u6708\u795e\u7684\u706b\u7403\u672f\u7ed9\u4ed6\u7167\u6210\u4e86127.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u6cd5\u795e","def":"\u6cd5\u795e\u6ce2\u6ce2","defhp":716.4,"atkindex":152,"text":"\u6cd5\u795e\u6ce2\u6ce2\u554a\u4e86\u4e00\u58f0\uff0c\u6cd5\u795e\u7684\u711a\u5929\u706d\u5730\u7ed9\u4ed6\u7167\u6210\u4e86283.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6708\u5149\u5251","def":"\u9ece\u660e\u4e4b\u65a7","defhp":786,"atkindex":202,"text":"\u9ece\u660e\u4e4b\u65a7\u554a\u4e86\u4e00\u58f0\uff0c\u6708\u5149\u5251\u7684\u91cd\u51fb\u7ed9\u4ed6\u7167\u6210\u4e8686.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u80cc\u540e\u4e00\u5200","def":"\u6708\u5149\u5251","defhp":852.6,"atkindex":252,"text":"\u6708\u5149\u5251\u554a\u4e86\u4e00\u58f0\uff0c\u80cc\u540e\u4e00\u5200\u7684\u540c\u5f52\u4e8e\u5c3d\u7ed9\u5979147.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u9ece\u660e\u4e4b\u65a7","def":"\u6708\u795e","defhp":739,"atkindex":301,"text":"\u6708\u795e\u554a\u4e86\u4e00\u58f0\uff0c\u9ece\u660e\u4e4b\u65a7\u7684\u5f00\u5929\u8f9f\u5730\u7ed9\u5979261\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u517d\u4eba\u6218\u58eb","def":"\u5fcd\u8005","defhp":867.4,"atkindex":351,"text":"\u5fcd\u8005\u554a\u4e86\u4e00\u58f0\uff0c\u517d\u4eba\u6218\u58eb\u7684\u91cd\u51fb\u7ed9\u5979132.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6cd5\u795e\u6ce2\u6ce2","def":"\u6cd5\u795e","defhp":886.4,"atkindex":400,"text":"\u6cd5\u795e\u554a\u4e86\u4e00\u58f0\uff0c\u6cd5\u795e\u6ce2\u6ce2\u7684\u706b\u7403\u672f\u7ed9\u5979113.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5fcd\u8005","def":"\u9ece\u660e\u4e4b\u65a7","defhp":689.8,"atkindex":450,"text":"\u9ece\u660e\u4e4b\u65a7\u554a\u4e86\u4e00\u58f0\uff0c\u5fcd\u8005\u7684\u5272\u5589\u7ed9\u4ed6\u7167\u6210\u4e8696.2\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u9690\u523a","def":"\u6cd5\u795e\u6ce2\u6ce2","defhp":542.6,"atkindex":500,"text":"\u6cd5\u795e\u6ce2\u6ce2\u554a\u4e86\u4e00\u58f0\uff0c\u9690\u523a\u7684\u540c\u5f52\u4e8e\u5c3d\u7ed9\u4ed6\u7167\u6210\u4e86173.8\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5723\u6bbf\u957f\u8001","def":"\u9ece\u660e\u4e4b\u65a7","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":821.5},{"name":"\u9690\u523a","hp":765.5},{"name":"\u9ece\u660e\u4e4b\u65a7","hp":488.3},{"name":"\u6cd5\u795e","hp":669.9},{"name":"\u80cc\u540e\u4e00\u5200","hp":771.5}],"atkindex":550,"text":"\u5723\u6bbf\u957f\u8001\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u70c8\u706b\u71ce\u539f\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86322.5\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6708\u795e","def":"\u80cc\u540e\u4e00\u5200","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":655},{"name":"\u9690\u523a","hp":543},{"name":"\u9ece\u660e\u4e4b\u65a7","hp":298.8},{"name":"\u6cd5\u795e","hp":465.4},{"name":"\u80cc\u540e\u4e00\u5200","hp":555}],"atkindex":552,"text":"\u6708\u795e\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u70c8\u706b\u71ce\u539f\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86310.5\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u6cd5\u795e","def":"\u6708\u5149\u5251","defhp":590,"atkindex":602,"text":"\u6708\u5149\u5251\u554a\u4e86\u4e00\u58f0\uff0c\u6cd5\u795e\u7684\u711a\u5929\u706d\u5730\u7ed9\u5979262.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6708\u5149\u5251","def":"\u9ece\u660e\u4e4b\u65a7","defhp":108.4,"atkindex":652,"text":"\u9ece\u660e\u4e4b\u65a7\u554a\u4e86\u4e00\u58f0\uff0c\u6708\u5149\u5251\u7684\u5f00\u5929\u8f9f\u5730\u7ed9\u4ed6\u7167\u6210\u4e86190.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u80cc\u540e\u4e00\u5200","def":"\u5fcd\u8005","defhp":685,"atkindex":702,"text":"\u5fcd\u8005\u554a\u4e86\u4e00\u58f0\uff0c\u80cc\u540e\u4e00\u5200\u7684\u540c\u5f52\u4e8e\u5c3d\u7ed9\u5979182.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u9ece\u660e\u4e4b\u65a7","def":"\u5fcd\u8005","defhp":544,"atkindex":751,"text":"\u5fcd\u8005\u554a\u4e86\u4e00\u58f0\uff0c\u9ece\u660e\u4e4b\u65a7\u7684\u91cd\u51fb\u7ed9\u5979141\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u517d\u4eba\u6218\u58eb","def":"\u5723\u6bbf\u957f\u8001","defhp":779.6,"atkindex":801,"text":"\u5723\u6bbf\u957f\u8001\u554a\u4e86\u4e00\u58f0\uff0c\u517d\u4eba\u6218\u58eb\u7684\u5f00\u5929\u8f9f\u5730\u7ed9\u4ed6\u7167\u6210\u4e86220.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6cd5\u795e\u6ce2\u6ce2","def":"\u80cc\u540e\u4e00\u5200","defhp":319.6,"atkindex":850,"text":"\u80cc\u540e\u4e00\u5200\u554a\u4e86\u4e00\u58f0\uff0c\u6cd5\u795e\u6ce2\u6ce2\u7684\u711a\u5929\u706d\u5730\u7ed9\u4ed6\u7167\u6210\u4e86235.4\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5fcd\u8005","def":"\u6cd5\u795e","defhp":354.2,"atkindex":900,"text":"\u6cd5\u795e\u554a\u4e86\u4e00\u58f0\uff0c\u5fcd\u8005\u7684\u5272\u5589\u7ed9\u5979111.2\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"red","atk":"\u9690\u523a","def":"\u6708\u795e","defhp":641.8,"atkindex":950,"text":"\u6708\u795e\u554a\u4e86\u4e00\u58f0\uff0c\u9690\u523a\u7684\u5272\u5589\u7ed9\u597997.2\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5723\u6bbf\u957f\u8001","def":"\u6cd5\u795e","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":476.5},{"name":"\u9690\u523a","hp":308.5},{"name":"\u6cd5\u795e","hp":137.7},{"name":"\u80cc\u540e\u4e00\u5200","hp":91.1}],"atkindex":1000,"text":"\u5723\u6bbf\u957f\u8001\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u70c8\u706b\u71ce\u539f\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86322.5\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6708\u795e","def":"\u517d\u4eba\u6218\u58eb","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":310},{"name":"\u9690\u523a","hp":86}],"atkindex":1002,"text":"\u6708\u795e\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u70c8\u706b\u71ce\u539f\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86310.5\u70b9\u4f24\u5bb3"},{"logstyle":"die","term":"blue","atk":"\u6708\u795e","def":"\u80cc\u540e\u4e00\u5200","defhp":0,"atkindex":1002,"text":"\u6708\u795e\u6740\u6b7b\u4e86\u80cc\u540e\u4e00\u5200"},{"logstyle":"attack","term":"blue","atk":"\u6708\u5149\u5251","def":"\u9690\u523a","defhp":0,"atkindex":1052,"text":"\u9690\u523a\u554a\u4e86\u4e00\u58f0\uff0c\u6708\u5149\u5251\u7684\u91cd\u51fb\u7ed9\u4ed6\u7167\u6210\u4e8686\u70b9\u4f24\u5bb3"},{"logstyle":"die","term":"blue","atk":"\u6708\u5149\u5251","def":"\u9690\u523a","defhp":0,"atkindex":1052,"text":"\u6708\u5149\u5251\u6740\u6b7b\u4e86\u9690\u523a"},{"logstyle":"attack","term":"red","atk":"\u517d\u4eba\u6218\u58eb","def":"\u5723\u6bbf\u957f\u8001","defhp":672,"atkindex":1101,"text":"\u5723\u6bbf\u957f\u8001\u554a\u4e86\u4e00\u58f0\uff0c\u517d\u4eba\u6218\u58eb\u7684\u91cd\u51fb\u7ed9\u4ed6\u7167\u6210\u4e86107.6\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5fcd\u8005","def":"\u517d\u4eba\u6218\u58eb","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":182.5}],"atkindex":1150,"text":"\u5fcd\u8005\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u81f4\u547d\u4e00\u51fb\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86271.5\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u6cd5\u795e\u6ce2\u6ce2","def":"\u517d\u4eba\u6218\u58eb","defhp":[{"name":"\u517d\u4eba\u6218\u58eb","hp":52}],"atkindex":1200,"text":"\u6cd5\u795e\u6ce2\u6ce2\u5927\u558a\u4e00\u58f0\u770b\u6211\u7684\u201c\u70c8\u706b\u71ce\u539f\u201d,\u7ed9\u5927\u5bb6\u9020\u6210\u4e86274.5\u70b9\u4f24\u5bb3"},{"logstyle":"attack","term":"blue","atk":"\u5723\u6bbf\u957f\u8001","def":"\u517d\u4eba\u6218\u58eb","defhp":0,"atkindex":1250,"text":"\u517d\u4eba\u6218\u58eb\u554a\u4e86\u4e00\u58f0\uff0c\u5723\u6bbf\u957f\u8001\u7684\u706b\u7403\u672f\u7ed9\u4ed6\u7167\u6210\u4e8652\u70b9\u4f24\u5bb3"},{"logstyle":"die","term":"blue","atk":"\u5723\u6bbf\u957f\u8001","def":"\u517d\u4eba\u6218\u58eb","defhp":0,"atkindex":1250,"text":"\u5723\u6bbf\u957f\u8001\u6740\u6b7b\u4e86\u517d\u4eba\u6218\u58eb"}]';
        //return $a;
    }

    //攻击过程计算
    private function _attackProcess() {
        //如果红队或蓝队剩余人数为0，游戏中止
        if (count($this->players_red) <= 0 || count($this->players_blue) <= 0) {
            return false;
        }
        $attackPlayer = $this->turnPlayer(); //攻击者
        $defensePlayer = $this->attackedPlayer($attackPlayer); //防御者
        $attackPlayer->releaseSkill($defensePlayer); //攻击者对防御者释放了技能
        return true;
    }

	//玩家阵营判断
	public function checkTerm($act){
		$namestr='';
		switch($act->term){
			case Game::TERM1:
				$namestr='<font color=\"red\">'.$act->name.'</font>';
				break;
			case Game::TERM2:
				$namestr='<font color=\"blue\">'.$act->name.'</font>';
				break;
		}
		return $namestr;
	}

    //将战斗过程加入日志
    public function addLog($arrlog) {
        if (!is_array($arrlog)) {
            return;
        }
        switch ($arrlog['logstyle']) {
            case Game::LOGTYPE1:
                $newlog['logstyle'] = Game::LOGTYPE1;
                $newlog['term'] = $arrlog['atk']->term;
                $newlog['atk'] = $arrlog['atk']->name;
                $newlog['def'] = $arrlog['def']->name;
                $newlog['defhp'] = $arrlog['def']->profession->hp;
                $newlog['atkindex'] = $arrlog['atk']->agileIndex;
                if ($arrlog['skillstyle'] == Profession::SKILLLEVEL1) {
                    if ($arrlog['def']->profession->sex == '男') {
                        //$newlog['text'] = $newlog['def'] . '啊了一声，' . $newlog['atk'] . '的' . $arrlog['skill'] . '给他照成了' . $arrlog['harmvalue'] . '点伤害';
						 $newlog['text'] = $newlog['def'] . '啊了一声，' . $this->checkTerm($arrlog['atk']) . '的' . $arrlog['skill'] . '给他照成了' . $arrlog['harmvalue'] . '点伤害';
                    } else {
                        //$newlog['text'] = $newlog['def'] . '啊了一声，' . $newlog['atk'] . '的' . $arrlog['skill'] . '给她' . $arrlog['harmvalue'] . '点伤害';
						$newlog['text'] = $newlog['def'] . '啊了一声，' . $this->checkTerm($arrlog['atk']) . '的' . $arrlog['skill'] . '给她' . $arrlog['harmvalue'] . '点伤害';

                    }
                } elseif ($arrlog['skillstyle'] == Profession::SKILLLEVEL2) {
                    switch ($newlog['term']) {
                        case Game::TERM1:
                            $newlog['defhp'] = array();
                            foreach ($this->players_blue as $k => $blue) {
                                $name = $blue->name;
                                $newlog['defhp'][$k]['name'] = $name;
                                $newlog['defhp'][$k]['hp'] = $blue->profession->hp;
                            }
                            break;
                        case Game::TERM2:
                            $newlog['defhp'] = array();
                            foreach ($this->players_red as $k => $red) {
                                $name = $red->name;
                                $newlog['defhp'][$k]['name'] = $name;
                                $newlog['defhp'][$k]['hp'] = $red->profession->hp;
                            }
                            break;
                    }
                    //$newlog['text'] = $newlog['atk'] . '大喊一声看我的“' . $arrlog['skill'] . '”,给大家造成了' . $arrlog['harmvalue'] . '点伤害';
					$newlog['text'] = $this->checkTerm($arrlog['atk']) . '大喊一声看我的“' . $arrlog['skill'] . '”,给大家造成了' . $arrlog['harmvalue'] . '点伤害';
                } else {
                    
                }

                break;
            case Game::LOGTYPE2:
                $newlog['logstyle'] = Game::LOGTYPE2;
                $newlog['term'] = $arrlog['atk']->term;
                $newlog['atk'] = $arrlog['atk']->name;
                $newlog['def'] = $arrlog['def']->name;
                $newlog['defhp'] = $arrlog['def']->profession->hp;
                $newlog['atkindex'] = $arrlog['atk']->agileIndex;
                //$newlog['text'] = $newlog['atk'] . '杀死了' . $newlog['def'];
				$newlog['text'] = $this->checkTerm($arrlog['atk']) . '<font color=\"#ccc\">杀死了' . $newlog['def'].'</font>';
                break;
        }
        array_push($this->log, $newlog);
    }

}

/**
 * 玩家类
 * 玩家拥有职业属性，能释放本职业的技能,给敌方照成技能伤害
 */
abstract class Player {

    public $name; //玩家昵称
    public $profession; //玩家职业
    public $agileIndex; //出战权值(权值越大，越先出战,出战权值初始值等于敏捷值，出站后，其他玩家权值变1.2倍，重新排名)
    public $term; //阵营

    public function __construct($name, Profession $profession) {
        $this->name = $name;
        $this->profession = $profession;
        $this->agileIndex = $this->profession->getAgile();
    }

    //释放技能
    abstract public function releaseSkill(Player $player);
}

class RedPlayer extends Player {

    //$player被攻击者
    public function releaseSkill(Player $player) {
        $game = Game::getInstance();
        $loginfo = array();
        $loginfo['logstyle'] = Game::LOGTYPE1; //攻击类

        $attack = $this->profession->getAttack(); //攻击者的攻击值
        $skillinfo = $this->profession->getSkill(); //攻击值的技能信息
        $loginfo['atk'] = $this; //攻击者
        $loginfo['def'] = $player; //防御者
        $loginfo['skill'] = $skillinfo['skillname']; //攻击技能
        $loginfo['skillstyle'] = $skillinfo['rangestyle']; //攻击技能范围
        $harmvalue = $attack * $skillinfo['strength'];
        $loginfo['harmvalue'] = $harmvalue < 0 ? 0 : $harmvalue; //攻击伤害值
        switch ($skillinfo['rangestyle']) {
            case Profession::SKILLLEVEL1:
                $defense = $player->profession->getDefense(); //被攻击者的防御值
                $harmvalue1 = $harmvalue - $defense;
                $harmvalue2 = $harmvalue1 < 0 ? 0 : $harmvalue1;
                $hp = $player->profession->hp;
                $loginfo['harmvalue'] = min(array($harmvalue2, $hp)) < 0 ? 0 : min(array($harmvalue2, $hp));
                $player->profession->hp = ($hp - $harmvalue2 < 0) ? 0 : ($hp - $harmvalue2);
                $game->addLog($loginfo);
                if ($player->profession->hp == 0) {
                    $loginfo['logstyle'] = Game::LOGTYPE2; //死亡信息
                    $game->addLog($loginfo);
                    $game->exitPlayer($game->players_blue, $player);
                }
                break;
            case Profession::SKILLLEVEL2:
                $diearr = array();
                foreach ($game->players_blue as $k => $blue) {
                    $defense = $blue->profession->getDefense(); //被攻击者的防御值
                    $harmvalue1 = $harmvalue - $defense;
                    $harmvalue2 = $harmvalue1 < 0 ? 0 : $harmvalue1;
                    $hp = $blue->profession->hp; //被攻击者的血量
                    $blue->profession->hp = ($hp - $harmvalue2 < 0) ? 0 : ($hp - $harmvalue2);
                    if ($blue->profession->hp == 0) {
                        $loginfo['defdie'] = $blue; //死亡者
                        $game->exitPlayer($game->players_blue, $blue);
                        $diearr[] = $loginfo;
                    }
                };
                $game->addLog($loginfo); //先扣血，再通报
                foreach ($diearr as $die) {
                    $die['logstyle'] = Game::LOGTYPE2; //死亡信息
                    $die['def'] = $die['defdie']; //死亡者
                    $game->addLog($die); //先通报攻击，再通报死亡
                }
                break;
        }
    }

}

class BluePlayer extends Player {

    //$player被攻击者
    public function releaseSkill(Player $player) {
        $game = Game::getInstance();
        $loginfo = array();
        $loginfo['logstyle'] = Game::LOGTYPE1; //攻击类

        $attack = $this->profession->getAttack(); //攻击者的攻击值
        $skillinfo = $this->profession->getSkill(); //攻击值的技能信息
        $loginfo['atk'] = $this; //攻击者
        $loginfo['def'] = $player; //防御者
        $loginfo['skill'] = $skillinfo['skillname']; //攻击技能
        $loginfo['skillstyle'] = $skillinfo['rangestyle']; //攻击技能范围
        $harmvalue = $attack * $skillinfo['strength'];
        $loginfo['harmvalue'] = $harmvalue < 0 ? 0 : $harmvalue; //攻击伤害值
        switch ($skillinfo['rangestyle']) {
            case Profession::SKILLLEVEL1:
                $defense = $player->profession->getDefense(); //被攻击者的防御值
                $harmvalue1 = $harmvalue - $defense;
                $harmvalue2 = $harmvalue1 < 0 ? 0 : $harmvalue1;
                $hp = $player->profession->hp;
                $loginfo['harmvalue'] = min(array($harmvalue2, $hp)) < 0 ? 0 : min(array($harmvalue2, $hp));
                $player->profession->hp = ($hp - $harmvalue2 < 0) ? 0 : ($hp - $harmvalue2);
                $game->addLog($loginfo);
                if ($player->profession->hp == 0) {
                    $loginfo['logstyle'] = Game::LOGTYPE2; //死亡信息
                    $game->exitPlayer($game->players_red, $player);
                    $game->addLog($loginfo);
                }
                break;
            case Profession::SKILLLEVEL2:
                $diearr = array();
                foreach ($game->players_red as $k => $red) {
                    $defense = $red->profession->getDefense(); //被攻击者的防御值
                    $harmvalue1 = $harmvalue - $defense;
                    $harmvalue2 = $harmvalue1 < 0 ? 0 : $harmvalue1;
                    $hp = $red->profession->hp; //被攻击者的血量
                    $red->profession->hp = ($hp - $harmvalue2 < 0) ? 0 : ($hp - $harmvalue2);
                    if ($red->profession->hp == 0) {
                        $loginfo['defdie'] = $red; //死亡者
                        $game->exitPlayer($game->players_red, $red);
                        $diearr[] = $loginfo;
                    }
                }
                $game->addLog($loginfo); //群攻技能的被攻击者，为最后一个死亡者
                foreach ($diearr as $die) {
                    $die['logstyle'] = Game::LOGTYPE2; //死亡信息
                    $die['def'] = $die['defdie']; //死亡者
                    $game->addLog($die);
                }
                break;
        }
    }

}

/**
 * 职业管理类，工厂模式
 * 负责生产各种职业（战士（防）、法师（攻）、刺客（敏））
 *
 */
class ProfessionManger {

    const MENWARRIOR = 'menwarrior';
    const WOMENWARRIOR = 'womenwarrior';
    const MENMAGE = 'menmage';
    const WOMENMAGE = 'womenmage';
    const MENASSASSIN = 'menassassin';
    const WOMENASSASSIN = 'womenassassin';

    static function create($professionStyle) {
        switch ($professionStyle) {
            case self::MENWARRIOR :
                return new MenWarriorProfession();
                break;
            case self::WOMENWARRIOR :
                return new WomenWarriorProfession();
                break;
            case self::MENMAGE :
                return new MenMageProfession();
                break;
            case self::WOMENMAGE :
                return new WomenMageProfession();
                break;
            case self::MENASSASSIN :
                return new MenAssassinProfession();
                break;
            case self::WOMENASSASSIN :
                return new WomenAssassinProfession();
                break;
            default :
                return new MenWarriorProfession();
        }
    }

}

class Profession {

    const SKILLLEVEL1 = 1; //单体
    const SKILLLEVEL2 = 2; //群攻

    public $hp; //血量属性
    public $sex; //职业性别
    protected $attack; //攻击属性
    protected $defense; //防御属性
    protected $agile; //敏捷属性
    protected $skills; //技能
    protected $proImg; //职业头像

    public function getAttack() {
        return $this->attack;
    }

    public function getDefense() {
        return $this->defense;
    }

    public function getAgile() {
        return $this->agile;
    }

    public function getSkill() {
        return $this->skills[array_rand($this->skills)];
    }

    public function getProImg() {
        return $this->proImg;
    }

}

/**
 * 战士职业
 */
class WarriorProfession extends Profession {

    const SATTACK = 150; //攻击下限
    const EATTACK = 200; //攻击上限
    const SDEFENSE = 100; //防御上限
    const EDEFENSE = 150; //防御下限
    const SAGILE = 100; //敏捷上限
    const EAGILE = 102; //敏捷下限
    const HP = 1000; //血量

    public function __construct() {
        $this->attack = rand(self::SATTACK, self::EATTACK);
        $this->defense = rand(self::SDEFENSE, self::EDEFENSE);
        $this->agile = rand(self::SAGILE, self::EAGILE);
        $this->hp = self::HP;
        $this->skills = array(
            array(
                'skillname' => '重击', //单体攻击
                'strength' => 1.2,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
            array(
                'skillname' => '横少千军', //群攻
                'strength' => 1.5,
                'rangestyle' => Profession::SKILLLEVEL2, //群攻攻击
            ),
            array(
                'skillname' => '开天辟地', //单体攻击
                'strength' => 1.8,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
        );
    }

}

//男战士
class MenWarriorProfession extends WarriorProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/7.jpg';
        $this->sex = '男';
    }

}

//女战士
class WomenWarriorProfession extends WarriorProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/6.jpg';
        $this->sex = '女';
    }

}

/**
 * 法师职业
 */
class MageProfession extends Profession {

    const SATTACK = 180; //攻击下限
    const EATTACK = 220; //攻击上限
    const SDEFENSE = 80; //防御上限
    const EDEFENSE = 120; //防御下限
    const SAGILE = 100; //敏捷上限
    const EAGILE = 102; //敏捷下限
    const HP = 1000; //血量

    public function __construct() {
        $this->attack = rand(self::SATTACK, self::EATTACK);
        $this->defense = rand(self::SDEFENSE, self::EDEFENSE);
        $this->agile = rand(self::SAGILE, self::EAGILE);
        $this->hp = self::HP;
        $this->skills = array(
            array(
                'skillname' => '火球术',
                'strength' => 1.2,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
            array(
                'skillname' => '烈火燎原',
                'strength' => 1.5,
                'rangestyle' => Profession::SKILLLEVEL2, //群攻攻击
            ),
            array(
                'skillname' => '焚天灭地',
                'strength' => 1.8,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
        );
    }

}

//男法师
class MenMageProfession extends MageProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/4.jpg';
        $this->sex = '男';
    }

}

//女法师
class WomenMageProfession extends MageProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/5.jpg';
        $this->sex = '女';
    }

}

/**
 * 刺客职业
 */
class AssassinProfession extends Profession {

    const SATTACK = 150; //攻击下限
    const EATTACK = 200; //攻击上限
    const SDEFENSE = 50; //防御上限
    const EDEFENSE = 100; //防御下限
    const SAGILE = 100; //敏捷上限
    const EAGILE = 102; //敏捷下限
    const HP = 1000; //血量

    public function __construct() {
        $this->attack = rand(self::SATTACK, self::EATTACK);
        $this->defense = rand(self::SDEFENSE, self::EDEFENSE);
        $this->agile = rand(self::SAGILE, self::EAGILE);
        $this->hp = self::HP;
        $this->skills = array(
            array(
                'skillname' => '割喉',
                'strength' => 1.2,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
            array(
                'skillname' => '致命一击',
                'strength' => 1.5,
                'rangestyle' => Profession::SKILLLEVEL2, //群攻攻击
            ),
            array(
                'skillname' => '同归于尽',
                'strength' => 1.8,
                'rangestyle' => Profession::SKILLLEVEL1, //单体攻击
            ),
        );
    }

}

//男刺客
class MenAssassinProfession extends AssassinProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/8.jpg';
        $this->sex = '男';
    }

}

//女刺客
class WomenAssassinProfession extends AssassinProfession {

    public function __construct() {
        parent::__construct();
        $this->proImg = './img/2.jpg';
        $this->sex = '女';
    }

}
