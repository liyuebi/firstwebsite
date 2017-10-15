<?php 
	$group3StartLvl = 9;		// 第三组开始的等级
	
// 	$dyNewUserVault = 1200;		// 给新用户的动态返还蜜券总额
	$dyNewAccountVault = 1000;	// 给新用户的静态返还蜜券总额
	$fengzhiValue = 1000;		// 1蜂值等于多少蜜券值
	
	$rewardRate =0.717;		// 从每日的交易额中取出多少来返还给用户
	$rewardVal = 15.02;		// 不计算交易额，直接按照值进行分红
	
	$pntInRewardRate = 0.7;	// 从分红中取多少比例返还成采蜜券，剩下的依旧返还成蜜券
	
// 	$refererBonusLevel = 13;	// 推荐奖有效层次
	$refererConsumePoint = 300;	// 推荐新用户时需从推荐人账户中减去300积分 
	$rewardBPCnt = 1;			// 购买产生奖品需要的产品盒数，目前奖励的就是产生一个关联账号
	
	$withdrawFloorAmount =300;	// 提现最少的积分额度
	$withdrawCeilAmountOneDay =300;	// 用户一天可以提现的上限数额
	
	$transferFloorAmount =1;// 转账最低的积分额度
	
	$withdrawHandleRate = 0.05;	// 提现手续费率
	$transferHandleRate = 0.05;	// 转账手续费率 
	
	$recoBonusTillLevel = 5;	// 几级前有推荐奖励蜜券，包括此级别
	$recoBonus = 100; 		// 推荐奖励蜜券额度
	
	// credit code
	$codeBuy = 1;			// 购买积分
	$codeSell = 2;			// 卖出积分
	$codeDivident = 3;		// 固定分红导致积分变化
	$codeReferer = 4; 		// 推荐用户，扣除积分
	$codeReferBonus = 5;	// 直推奖励
	$codeColliBonus = 6;	// 碰撞奖励
	$codeSave = 7;			// 存储金币	
	$codeDailyBonus = 8;	// 每日分红
	$codeCreTradeInit = 9;	// 创建交易，扣除积分
	$codeCreTradeSucc = 10;	// 交易成功，卖家退回未购买的积分和手续费
	$codeCreTradeCancel = 11;//	交易取消，返还积分
	$codeCreTradeRec = 12;	// 交易成功，买家收款
	
	$code2Divident = 1; // 固定分红导致采蜜券变化
	$code2DynDivident = 2; // 动态分红导致采蜜券变化，
	$code2TransferTo = 3;	// 向用户转采蜜券
	$code2TransferFrom = 4;	// 收到其他人转的采蜜券
	$cdoe2TransferToCredit = 11;   // 采蜜券转到蜜券，目前的一种情形是用户升级奖励，若固定蜂值不够，从采蜜券划拨
	$code2TransferFromCredit = 12; // 从蜜券转换而来
	$code2TransferFromVault = 13; // 升到第二级时，固定蜂值余额转到采蜜券
	
	// credit trade status
	$creditTradeInited = 1;		// 卖家创建了交易
	$creditTradeCancelled = 2;	// 卖家取消了交易
	$creditTradeReserved = 3;	// 买家下单
	$creditTradeAbandoned = 4;	// 买家弃单
	$creditTradePayed = 5;		// 买家确认付款
	$creditTradeNotPayed = 6;	// 买家超时未支付
	$creditTradeConfirmed = 7;	// 卖家确认支付，交易完成
	$creditTradeAutoConfirmed = 8;	// 卖家超时未确认支付，交易自动完成
	$creditTradeExpired = 9;	// 卖家创建交易指定时间无人购买，过期
	
	
	$OrderStatusBuy = 1; 	// 订单状态，用户已下单
	$OrderStatusDefault = 2; 	// 订单状态，默认给新用户添加的订单，状态是已付款，但需要添加地址信息
	$OrderStatusDelivery = 3;	// 订单状态，卖家已发货
	$OrderStatusAccept = 5; 	// 订单状态，用户已收货
	
	$postStatusWait = 0;	// 公告状态，新添加后等待发布
	$postStatusOnline = 1;	// 公告状态，已发布，用户可见
	$postStatusDown = 2;	// 公告状态，删除公告，用户不可见
	
	$paymentWechat = 1;
	$paymentAlipay = 2;
	$paymentBank = 3;
	
	// team 1 people count needed according to level
	$team1Cnt = array(0, 0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 2 people count needed according to level
	$team2Cnt = array(0, 0, 6, 20, 40, 75, 150, 250, 375, 350, 700, 1400, 2100, 2800);
	
	// team 3 people count needed according to level
	$team3Cnt = array(0, 0, 0,  0,  0,  0,   0,   0,   0, 300, 600, 1200, 1800, 2400);
	
	// 每一层给的总分红奖励
	$levelBonus = array(1200, 360, 1360, 2020, 3520, 6530, 10050, 13060, 20080, 33100, 50150, 67200, 135300, 202000);
	// 每一层每天的分红奖励额度
	$levelDayBonus = array(5, 12, 45, 68, 118, 218, 335, 435, 670, 1103, 1672, 2240, 4510, 6733);
	// 每一层给的奖励
	$levelUpBonus = array(0, 0, 300, 300, 300, 400, 400, 400, 400, 500, 500, 500, 500, 600);
	// 每一层分红分配到采蜜券的比例
	$levelPntsRate = array(0, 0.1, 0.12, 0.14, 0.16, 0.18, 0.2, 0.22, 0.24, 0.26, 0.28, 0.3, 0.32, 0.34);
	// 每一层可以复投的单数
	$levelReinvestTime = array(0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
	
	$levelName = array('蜂粉','一级工蜂','二级工蜂','三级工蜂','四级工蜂','一级雄蜂','二级雄蜂','三级雄蜂','四级雄蜂','一级蜂王','二级蜂王','三级蜂王','四级蜂王','蜂后');
	
?> 
