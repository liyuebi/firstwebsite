<?php 
	$group3StartLvl = 9;		// 第三组开始的等级
	
// 	$dyNewUserVault = 1200;		// 给新用户的动态返还线上云量总额
	$dyNewAccountVault = 1000;	// 给新用户的静态返还线上云量总额
	$fengzhiValue = 1000;		// 1蜂值等于多少线上云量值
	
	$rewardRate =0.717;		// 从每日的交易额中取出多少来返还给用户
	$rewardVal = 15.02;		// 不计算交易额，直接按照值进行分红
	
	$pntInRewardRate = 0.7;	// 从分红中取多少比例返还成采线上云量，剩下的依旧返还成线上云量

	$regiCreditLeast =300;		// 推荐用户时最少存储积分数
	$regiCreditMost =2000;		// 推荐用户时最多存储积分数
	$saveCreditLeast =100;		// 存储云量时最少存储的云量数
	$saveCreditMost =2000;		// 存储云量时最多存储的云量数
	$exchangeLeast =100;		// 挂单最小额度
	$exchangeMost =2200;		// 挂单最大额度
	$phoneChargeLeast =100;		// 话费充值最小额度
	$phoneChargeMost =300;		// 话费充值最大额度
	$oilChargeLeast =10;		// 油费充值最小额度
	$oilChargeMost =500;		// 油费充值最大额度

	$offlineShopRegisterFee =1000;	// 注册线下商店所需线上云量

	$charityRate =0.05;			// 慈善基金比例
	$pntsRate =0.45;			// 线下云量比例
	$pntsReturnDirRate =0.5;	// 线下云量直接返还的比例，剩下部分分期返还
	$referBonusRate =0.1;		// 直推奖励比例
	$colliBonusRateRefer =0.1;	// 推荐碰撞奖励比例
	$colliBonusRateReinv =0.08; // 复投碰撞奖励比例
	$dayBonusRate =0.008;		// 每笔存储每日返还的额度
	$dayPntsBonusRate =0.017;	// 每笔线下云量存储每日返还的额度
	$phoneChargeRate =0.1;		// 手机充值手续费
	$oilChargeRate =0.1;		// 加油卡充值手续费
	
	$offlineTradeCeilOneDay =200;	// 线下交易付款每日限额
	$offlineTradeRate =0.1;			// 线下交易手续费
	$offlineTradeUpDiviRate =0.05;	// 线下交易给推荐人的分红
	
	$exchangeBuyHours = 24;		// 云量交易挂单的有效期，单位为小时
	$exchangePayHours = 1;		// 云量交易下单后支付的有效期，单位为小时
	$exchangeDeliveryHours = 2;	// 云量交易付款后确认收款的有效期，单位为小时
	$exchangeComplainHours = 24;// 云量交易自动完成后，卖家的投诉时间
	
	$pntWithdrawFloorAmt =2000;	// 提现最少需要的线下云量额度
	$pntWithdrawCeilAmtOneDay =0;	// 用户一天可以提现线下云量的上限数额，暂时为零
	$pntWithdrawHandleRate =0.1;	// 线下云量提现手续费率

	$profitWithdrawFloorAmt =1000;	// 提现最少的消费积分额度
	$profitWithdrawCeilAmtOneDay =2000;	// 用户一天可以提现消费云量的上限数额
	$profitWithdrawHandleRate =0;	// 消费云量提现手续费率
	
	$transferFloorAmount =1;// 转账最低的积分额度
	
	$transferHandleRate = 0.05;	// 转账手续费率 
	
	$recoBonusTillLevel = 5;	// 几级前有推荐奖励线上云量，包括此级别
	$recoBonus = 100; 		// 推荐奖励线上云量额度
	
	// credit code
	$codeBuy = 1;			// 购买积分
	$codeSell = 2;			// 卖出积分
	$codeDivident = 3;		// 固定分红导致积分变化
	$codeReferer = 4; 		// 推荐用户，扣除积分
	$codeReferBonus = 5;	// 直推奖励
	$codeColliBonusNew = 6;	// 碰撞奖励，推荐新用户
	$codeColliBonusRe = 7;	// 碰撞奖励，用户复投
	$codeSave = 8;			// 存储金币	
// 	$codeDailyBonus = 9;	// 每日分红
	$codeCreTradeInit = 10;	// 创建交易，扣除积分
	$codeCreTradeSucc = 11;	// 交易成功，卖家退回未购买的积分和手续费
	$codeCreTradeCancel = 12;//	交易取消，返还积分
	$codeCreTradeRec = 13;	// 交易成功，买家收款
	$codeTryChargePhone = 14;// 提交手机充值申请，扣除积分
	$codeStopChargePhone = 15;// 取消手机充值申请，返还积分
	$codeTryChargeOil = 16;	// 提交加油卡充值申请，扣除积分
	$codeStopChargeOil = 17;// 取消加油卡充值申请，返还积分
	$codeRegiOlShop = 20;	// 注册线下商店
	$codeFromProfit = 22;	// 消费云量转到线上云量
	
	// pnts code
	$code2Save = 1; 		// 用户存储云量时直接获得的线下云量（包括注册为新用户时的存储）
	$code2OlShopPay = 2;	// 向线下商家支付
	$code2OlShopReceive = 3;// 线下商家收款
	$code2OlShopBonus = 4;	// 线下商家分红，推荐人收到商家交易额一定比例作为推荐奖励
	$code2OlShopWdApply = 5;// 线下商家提现申请
	$code2OlShopWdCancel = 6;	// 线下商家提现撤销
	$code2OlShopWdAccept = 7;	// 线下商家提现通过
	$code2OlSHopWdDecline = 8;	// 线下商家提现申请被拒 
	$code2Divident = 9;		// 线下积分每日分红导致积分变化
	$code2TryCP = 10;		// 提交手机充值申请，扣除积分
	$code2CancelCP = 11;	// 用户取消手机充值申请，返还积分
	$code2StopCP = 12;		// 后台停止手机充值申请，返还积分
	$code2FromProfit = 15;	// 消费云量转到线下云量

	// profit pnt code
	$code3OlShopReceive = 1;// 线下商家收款
	$code3OlShopBonus = 2;	// 线下商家分红，推荐人收到商家交易额一定比例作为推荐奖励
	$code3OlShopWdApply = 3;// 线下商家提现申请
	$code3OlShopWdCancel = 4;	// 线下商家提现撤销
	$code3OlShopWdAccept = 5;	// 线下商家提现通过
	$code3OlSHopWdDecline = 6;	// 线下商家提现申请被拒 
	$code3ToCredit = 9;		// 消费云量转换为线上云量
	$code3ToPnts = 10;		// 消费云量转换为线下云量
	$code3ToShareCredit = 11;	// 消费云量转换为分享云量

	// share credit code
	$code4CreTradeRec = 1;	// 交易成功，买家收款
	$code4Referer = 2; 		// 推荐用户，扣除积分
	$code4Save = 3;			// 存储分享云量
	$code4FromProfit = 4;	// 消费云量转到分享云量
	
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
	
	// 订单状态
	$OrderStatusBuy = 1; 	// 订单状态，用户已下单
	$OrderStatusDefault = 2; 	// 订单状态，默认给新用户添加的订单，状态是已付款，但需要添加地址信息
	$OrderStatusPaid = 3;		// 订单状态，买家完成支付
	$OrderStatusCanceled = 4;	// 订单状态，买家取消订单
	$OrderStatusDelivery = 6;	// 订单状态，卖家已发货
	$OrderStatusAccept = 8; 	// 订单状态，用户已收货
	
	// 公告状态
	$postStatusWait = 0;	// 公告状态，新添加后等待发布
	$postStatusOnline = 1;	// 公告状态，已发布，用户可见
	$postStatusDown = 2;	// 公告状态，删除公告，用户不可见
	
	// 线下积分提取状态
	$olShopWdApplied = 1;	// 线下积分申请中
	$olShopWdCancelled = 2; // 线下积分申请取消
	$olShopWdAccepted = 3;	// 线下积分申请通过
	$olShopWdDeclined = 4;	// 线下积分申请被拒绝
	
	$paymentWechat = 1;
	$paymentAlipay = 2;
	$paymentBank = 3;
	
	// 投诉类型
	$complainTCreditTrade = 1; // 投诉交易问题

	// 投诉状态
	$complainSOPen = 1;		// 投诉人发起投诉
	$complainBOpen = 2;		// 投诉人发起投诉，被投诉人反诉
	$complainROpen = 3;		// 投诉人撤诉，但被投诉人依然在反诉
	$complainClose = 4;		// 投诉解决，以关闭
	
	// 线下商店状体啊
	$olshopRegistered = 1;	// 已注册
	$olshopApplied = 2;		// 提交审核
	$olshopDeclined = 3;	// 审核失败
	$olshopAccepted = 4;	// 审核通过
	$olshopClosed = 6;		// 线下商店下线 
	$olshopSuspended = 8;	// 线下商店被停止
	
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
