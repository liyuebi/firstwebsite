<?php 
	$group3StartLvl = 9;		// 第三组开始的等级
	
	$dyNewUserVault = 1000;		// 给新用户的动态返还蜜券总额
	$dyNewAccountVault = 2000;	// 给新用户的静态返还蜜券总额
	$fengzhiValue = 1000;		// 1蜂值等于多少蜜券值
	
	$rewardRate = 0.5;		// 从每日的交易额中取出多少来返还给用户
	
// 	$refererBonusLevel = 13;	// 推荐奖有效层次
	$refererConsumePoint = 900;	// 推荐新用户时需从推荐人账户中减去300积分 
	
	$withdrawFloorAmount = 100;	// 提现最少的积分额度
	$withdrawCeilAmountOneDay = 3000;	// 用户一天可以提现的上限数额
	
	$transferFloorAmount = 300;// 转账最低的积分额度
	
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
	
	// team 1 people count needed according to level
	$team1Cnt = array(0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 2 people count needed according to level
	$team2Cnt = array(0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 3 people count needed according to level
	$team3Cnt = array(0, 0,  0,  0,  0,   0,   0,   0, 300, 600, 1200, 1800, 2400);
	
	// 每一层给的总分红奖励
	$levelBonus = array(1080, 4080, 6060, 10560, 19590, 30150, 39180, 60240, 99300, 150450, 201600, 405900, 606000);
	// 每一层每天的分红奖励额度
	$levelDayBonus = array(36, 136, 202, 352, 653, 1005, 1306, 2008, 3310, 5015, 6720, 13530, 20200);
	
	$levelName = array('工蜂','1级雄峰','2级雄峰','3级雄峰','4级雄峰','5级雄峰','6级雄峰','7级雄峰','8级雄峰','9级雄峰','10级雄峰','11级雄峰','12级雄峰','蜂后');
	
?> 