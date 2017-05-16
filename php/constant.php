<?php 
	$group3StartLvl = 9;		// 第三组开始的等级
	
	$dyNewUserVault = 1000;		// 给新用户的动态返还蜜券总额
	$dyNewAccountVault = 2000;	// 给新用户的静态返还蜜券总额
	$fengzhiValue = 1000;		// 1蜂值等于多少蜜券值
	
	$rewardRate =0.4;		// 从每日的交易额中取出多少来返还给用户
	
// 	$refererBonusLevel = 13;	// 推荐奖有效层次
	$refererConsumePoint = 300;	// 推荐新用户时需从推荐人账户中减去300积分 
	$rewardBPCnt = 3;			// 购买产生奖品需要的产品盒数，目前奖励的就是产生一个关联账号
	
	$withdrawFloorAmount = 100;	// 提现最少的积分额度
	$withdrawCeilAmountOneDay =1000;	// 用户一天可以提现的上限数额
	
	$transferFloorAmount =1;// 转账最低的积分额度
	
	$withdrawHandleRate = 0.05;	// 提现手续费率
	$transferHandleRate = 0.05;	// 转账手续费率 
	
	// credit code
	$codeRecharge = 1;	// 充值积分变化
	$codeWithdraw = 2;	// 取现积分变化
	$codeDivident = 3;	// 固定分红导致积分变化，根据用户等级每日返还部分积分
	$codeBonus    = 4;	// 奖励积分，推荐的用户每购物一笔，有一部分奖励
	$codeConsume = 5;	// 消费消耗积分
	$codeCancelPurchase = 6; // 取消购物，积分返还
	$codeRecommend = 7;	// 推荐用户，扣除积分
	$codeTransferTo = 8;// 向用户转积分
	$codeTransferFrom = 9;	// 收到其他人转的积分
	$codeDynDivident = 10;	// 动态分红导致积分变化，根据每日订单总额去一定比例，再除以动态蜂值总和，返还给用户
	
	$OrderStatusBuy = 1; 	// 订单状态，用户已下单
	$OrderStatusDefault = 2; 	// 订单状态，默认给新用户添加的订单，状态是已付款，但需要添加地址信息
	$OrderStatusDelivery = 3;	// 订单状态，卖家已发货
	$OrderStatusAccept = 5; 	// 订单状态，用户已收货
	
	$paymentWechat = 1;
	$paymentAlipay = 2;
	$paymentBank = 3;
	
	// team 1 people count needed according to level
	$team1Cnt = array(0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 2 people count needed according to level
	$team2Cnt = array(0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 3 people count needed according to level
	$team3Cnt = array(0, 0,  0,  0,  0,   0,   0,   0, 300, 600, 1200, 1800, 2400);
	
	// 每一层给的总分红奖励
	$levelBonus = array(360, 1360, 2020, 3520, 6530, 10050, 13060, 20080, 33100, 50150, 67200, 135300, 202000);
	// 每一层每天的分红奖励额度
	$levelDayBonus = array(12, 55, 68, 118, 218, 335, 435, 670, 1103, 1672, 2240, 4510, 6733);
	
	$levelName = array('蜂粉','一级工蜂','二级工蜂','三级工蜂','四级工蜂','一级雄蜂','二级雄蜂','三级雄蜂','四级雄蜂','一级蜂王','二级蜂王','三级蜂王','四级蜂王','蜂后');
	
?> 
