<?php
header('Content-type:text/html;charset=utf-8');

include_once 'game.php';
//session_start();
/* * ************************以下为程序代码*********************************** */
//创建游戏
$game = Game::getInstance();

//N个玩家选人物，起名字
$players = array(
    array(
        'profession' => 'menwarrior',
        'name' => '兽人战士',
    ),
    array(
        'profession' => 'womenassassin',
        'name' => '忍者',
    ),
    array(
        'profession' => 'menassassin',
        'name' => '隐刺',
    ),
    array(
        'profession' => 'menmage',
        'name' => '法神波波',
    ),
    array(
        'profession' => 'menwarrior',
        'name' => '黎明之斧',
    ),
    array(
        'profession' => 'womenmage',
        'name' => '月神',
    ),
    array(
        'profession' => 'womenmage',
        'name' => '法神',
    ),
    array(
        'profession' => 'womenwarrior',
        'name' => '月光剑',
    ),
    array(
        'profession' => 'menassassin',
        'name' => '背后一刀',
    ),
    array(
        'profession' => 'menmage',
        'name' => '圣殿长老',
    ),
    array(
        'profession' => 'menwarrior',
        'name' => '砍刀耍耍',
    ),
    array(
        'profession' => 'womenassassin',
        'name' => '女刺客',
    ),
    array(
        'profession' => 'womenwarrior',
        'name' => '烈火刀',
    ),
    array(
        'profession' => 'menmage',
        'name' => '烈浴火',
    ),
    array(
        'profession' => 'womenassassin',
        'name' => '神偷女孩',
    ),
    array(
        'profession' => 'menwarrior',
        'name' => '野蛮战士',
    ),
);

shuffle($players);

foreach ($players as $player) {
    //玩家挑选职业和起了游戏昵称
    $profession = ProfessionManger::create($player['profession']); //挑选了职业
//玩家加入游戏（同时生成一个该职业的玩家人物）
    $game->addPlayer($player['name'], $profession);
}
/* 游戏进场准备 */
//红队，蓝队列队
$redPlayers = $game->players_red;
$bluePlayers = $game->players_blue;
$redhparr = array();
$bluearr = array();
if (!empty($redPlayers)) {
    foreach ($redPlayers as $red) {
        $redhparr[]['hp'] = $red->profession->hp;
    }
}
if (!empty($bluePlayers)) {
    foreach ($bluePlayers as $blue) {
        $bluearr[]['hp'] = $blue->profession->hp;
    }
}
$log = $game->getLog();
//echo $log;
/* * ************************以下为前端代码*********************************** */
?>

<html>
    <head>
        <script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
        <style>
            *{
                margin: 0px;
                padding: 0px;
            }
            .red_first{
                width: 15%;
                height:100%;
            }
            .red_second{
                width: 15%;
                height:100%;
            }
            .blue_first{
                width: 15%;
                height:100%;
            }
            .blue_second{
                width: 15%;
                height:100%;
            }
            .mid_div{
                width: 40%;
                height:100%;
                background: black;
                text-align: center;
            }
            .mid_text{
                width: 80%;
                height: 100%;
                background: white;
                margin: auto;
                border: 3px solid #aaa;
                vertical-align:bottom;

            }
            .player_box{
                width: 100%;
                height: 180px;
                margin-bottom: 5px;
            }
            .player_box .player1{
                width: 49%;
                height: 100%;
                float: left;
            }
            .player1 .playerhp , .player2 .playerhp{
                width: 100%;
                height: 10%;
                text-align: center;
            }
            .player1 .playername,.player2 .playername{
                width: 100%;
                height: 10%;
                text-align: center;
            }
            .player1 .headpic,.player2 .headpic{
                width: 100%;
                height: 80%;
            }
            .player_box .player2{
                width: 49%;
                height: 100%;
                float: right;
            }



        </style>
        <script>
            var datalog = '';//战斗数据
            $(function() {
                datalog = '<?php echo $log ?>';
                $('#gaobutton').attr('disabled', false);
            });
        </script>
        <script>
            //游戏开始
            function gamestart(obj) {
                $(obj).attr('disabled', 'disabled');
                var templog = eval(datalog);
                $('#mid_text').prepend('<p>游戏开始</p>');
                setTimeout(function() {

                    for (var i = 0; i < templog.length; i++) {
                        var c = function() {
                            var _i = i;
                            var time = 1500 * (_i + 1);
                            setTimeout(function() {
                                fighting(templog[_i]);
                                if (_i == templog.length - 1) {
                                    $('#mid_text').prepend('<p>游戏结束</p>');
                                }
                            }, time);
                        }();
                    }






//                    for (var i = 0; i < templog.length; i++) {
//                        $.post('turn.php', {'time': 1}, function(data) {
//                            var list=eval('('+data+')');
//                            fighting(list);
//                        });
//                    }

                }, 500);

            }

            //人物出战
            function fighting(list) {
                var type = list.logstyle;
                var term = list.term;
                var atk = list.atk;
                var def = list.def;
                var defhp = list.defhp;
                var text = list.text;
                if (term == 'red') {
                    switch (type) {
                        case 'die':
                            $('#' + def + '_2').html('');//死亡者退出比赛
                            $('#mid_text').prepend('<p>' + text + '</p>');
                            break;
                        case 'attack':
                            var html = $('#' + atk + '_1').html();
                            $('#' + atk + '_1').html('');//攻击者离开原位置
                            $('#' + def + '_1').append(html);//攻击者显示在攻击位置
                            $('#mid_text').prepend('<p>' + text + '</p>');

                            setTimeout(function() {
                                $('#' + def + '_1').html('');//攻击者离开攻击位置
                                if (!isNaN(defhp)) {
                                    $('#' + def + '_hp_2').html(defhp);
                                } else {
                                    for (var j = 0; j < defhp.length; j++) {
                                        $('#' + defhp[j].name + '_hp_2').html(defhp[j].hp);
                                    }
                                }
                                $('#' + atk + '_1').append(html);//攻击者回到原位置
                            }, 1000);
                            break;
                    }

                } else {
                    switch (type) {
                        case 'die':
                            $('#' + def + '_1').html('');//死亡者退出比赛
                            $('#mid_text').prepend('<p>' + text + '</p>');
                            break;
                        case 'attack':
                            var html = $('#' + atk + '_2').html();
                            $('#' + atk + '_2').html('');//攻击者离开原位置
                            $('#' + def + '_2').append(html);//攻击者显示在攻击位置
                            $('#mid_text').prepend('<p>' + text + '</p>');
                            setTimeout(function() {
                                $('#' + def + '_2').html('');//攻击者离开攻击位置
                                if (!isNaN(defhp)) {
                                    $('#' + def + '_hp_1').html(defhp);
                                } else {
                                    for (var j = 0; j < defhp.length; j++) {
                                        $('#' + defhp[j].name + '_hp_1').html(defhp[j].hp);
                                    }
                                }
                                $('#' + atk + '_2').append(html);//攻击者回到原位置
                            }, 1000);
                            break;
                    }

                }

                return false;

            }

        </script>
    </head>
    <body>
        <table style="width:100%;height:100%;border: 0px;">
            <tr style="width:100%;height:100%">
                <td class="red_first" id="red_first"> 
                    <?php for ($i = 0; $i < count($redPlayers); $i++): ?>
                        <?php if ($i > 4) break; ?>
                        <div class="player_box">
                            <div class="player1" id='<?php echo $redPlayers[$i]->name ?>_1'>
                                <div class="playerhp"><span id="<?php echo $redPlayers[$i]->name ?>_hp_1"><?php echo $redhparr[$i]['hp']; ?></span>/<?php echo $redhparr[$i]['hp']; ?></div>
                                <div class="playername"><?php echo $redPlayers[$i]->name ?></div>
                                <div class="headpic" style="background:url('<?php echo $redPlayers[$i]->profession->getProImg(); ?>');"></div>
                            </div>
                            <div class="player2" id='<?php echo $redPlayers[$i]->name ?>_2'>
                            </div>
                        </div>
                    <?php endfor; ?>
                </td>
                <td class="red_second">
                    <?php if (count($redPlayers) > 5): ?>
                        <?php for ($i = 5; $i < count($redPlayers); $i++): ?>
                            <?php if ($i > 9) break; ?>
                            <div class="player_box">
                                <div class="player1" id='<?php echo $redPlayers[$i]->name ?>_1'>
                                    <div class="playerhp"><span id="<?php echo $redPlayers[$i]->name ?>_hp_1"><?php echo $redhparr[$i]['hp']; ?></span>/<?php echo $redhparr[$i]['hp']; ?></div>
                                    <div class="playername"><?php echo $redPlayers[$i]->name ?></div>
                                    <div class="headpic" style="background:url('<?php echo $redPlayers[$i]->profession->getProImg(); ?>');"></div>
                                </div>
                                <div class="player2" id='<?php echo $redPlayers[$i]->name ?>_2'>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </td>
                <td class="mid_div">
                    <img src="./img/pk.png">
                    <div style="width:100%;height:386px;margin: auto;overflow: hidden;"><div class="mid_text" id="mid_text" ></div><br><br></div>
                    <input type="button" value="开始游戏" style="width:100px;height: 40px;" id="gaobutton" onclick="gamestart(this);">

                </td>
                <td class="blue_first">
                    <?php if (count($bluePlayers) > 5): ?>
                        <?php for ($i = 5; $i < count($bluePlayers); $i++): ?>
                            <?php if ($i > 9) break; ?>
                            <div class="player_box">
                                <div class="player1" id='<?php echo $bluePlayers[$i]->name ?>_1'>

                                </div>
                                <div class="player2" id='<?php echo $bluePlayers[$i]->name ?>_2'>
                                    <div class="playerhp"><span id="<?php echo $bluePlayers[$i]->name ?>_hp_2"><?php echo $bluearr[$i]['hp']; ?></span>/<?php echo $bluearr[$i]['hp']; ?></div>
                                    <div class="playername"><?php echo $bluePlayers[$i]->name ?></div>
                                    <div class="headpic" style="background:url('<?php echo $bluePlayers[$i]->profession->getProImg(); ?>');"></div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </td>
                <td class="blue_second">
                    <?php for ($i = 0; $i < count($bluePlayers); $i++): ?>
                        <?php if ($i > 4) break; ?>
                        <div class="player_box">
                            <div class="player1" id='<?php echo $bluePlayers[$i]->name ?>_1'>

                            </div>
                            <div class="player2" id='<?php echo $bluePlayers[$i]->name ?>_2'>
                                <div class="playerhp"><span id="<?php echo $bluePlayers[$i]->name ?>_hp_2"><?php echo $bluearr[$i]['hp']; ?></span>/<?php echo $bluearr[$i]['hp']; ?></div>
                                <div class="playername"><?php echo $bluePlayers[$i]->name ?></div>
                                <div class="headpic" style="background:url('<?php echo $bluePlayers[$i]->profession->getProImg(); ?>');"></div>
                            </div>
                        </div>
                    <?php endfor; ?>

                </td>
            </tr>
        </table>

    </body>
</html>
