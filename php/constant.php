<?php 
	$retRate = 5;				// 用户使用积分后相应增加返还额度的比例
	$fengzhiValue = 1500;		// 1蜂值等于多少蜜券值
	
	$rewardRate = 11/30;		// 从每日的充值额中取出多少来返还给用户
	$rewardStaticRate = 0.55;	// 在每日返还用户的总积分中给静态用户的比例
	$rewardDynamicRate = 0.45;	// 在每日返还用户的总积分中给动态用户的比例
	
// 	$refererBonusLevel = 13;	// 推荐奖有效层次
	$refererConsumePoint = 300;	// 推荐新用户时需从推荐人账户中减去300积分 
	
	$withdrawFloorAmount = 300;	// 提现最少的积分额度
	$withdrawCeilAmountOneDay = 3000;	// 用户一天可以提现的上限数额
	
	$transferFloorAmount = 300;// 转账最低的积分额度
	
	$withdrawHandleRate = 0.05;	// 提现手续费率
	$transferHandleRate = 0.05;	// 转账手续费率 
	
	// credit code
	$codeRecharge = 1;	// 充值积分变化
	$codeWithdraw = 2;	// 取现积分变化
	$codeDivident = 3;	// 分红积分变化，根据充值额取一部分返还给用户
	$codeBonus    = 4;	// 奖励积分，推荐的用户每购物一笔，有一部分奖励
	$codeConsume = 5;	// 消费消耗积分
	$codeCancelPurchase = 6; // 取消购物，积分返还
	$codeRecommend = 7;	// 推荐用户，扣除积分
	$codeTransferTo = 8;// 向用户转积分
	$codeTransferFrom = 9;	// 收到其他人转的积分
	
	$OrderStatusBuy = 1; 	// 订单状态，用户已下单
	$OrderStatusDefault = 2; 	// 订单状态，默认给新用户添加的订单，状态是已付款，但需要添加地址信息
	$OrderStatusDelivery = 3;	// 订单状态，卖家已发货
	$OrderStatusAccept = 5; 	// 订单状态，用户已收货
	
?> 