<?php 
	
function connectToDB()
{
	$con = mysql_connect("127.0.0.1:3306", "root", "123456789");
	if (!$con)
	{
		echo "Could not connect: " . mysql_error();
	}
	/* mifeng_db */
	$db_selected = mysql_select_db("mifeng_db", $con);
	if (!$db_selected) {
		echo "Cannot use mifeng_db : " . mysql_error();
		$con = false;
	}
	return $con;
}
	
function createClientTable()
{
	/*
	 * UserId: Id of user 用户编号 
	 * ParentId: 父节点的UserId
	 * RecoCnt: Recommending count 推荐人数
	 * ChildCnt: 子节点总数
	 * Lvl: level 用户等级
	 * LastPwdModiTime: last password modify time 上次登录密码修改时间
	 * LastPPwdModiTime: last pay password modify time 上次用户支付密码修改时间
	 */
	$sql = "create table if not exists ClientTable
	(
		UserId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(UserId),
		PhoneNum varchar(15) NOT NULL,
		Lvl	int DEFAULT 1,
		Name varchar(16) DEFAULT '',
		NickName varchar(16) DEFAULT '',
		IDNum varchar(18) DEFAULT '',
		Password varchar(256) NOT NULL,
		PayPwd varchar(256) DEFAULT '',
		ReferreeId int DEFAULT 0,
		ParentId int DEFAULT 0,
		RecoCnt int DEFAULT 0,
		ChildCnt int DEFAULT 0,
		RegisterTime int NOT NULL,
		LastLoginTime int DEFAULT 0,
		LastPwdModiTime int DEFAULT 0,
		LastPPwdModiTime int DEFAULT 0,
		DefaultAddressId int DEFAULT 0,
		AccInited int default 0
	) ENGINE=MEMORY AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ClientTable table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCreditTable()
{
	/*
	 * RegiToken: 注册券
	 * Credits: 线上云量
	 * Pnts: 线下资产
	 * Vault: 静态金库
	 * CollChild: 对碰金所在下线的直接子节点
	 * CollVal: 对碰金所在下线的总金额
	 * BPCnt: buy product count 总共购买的产品件数
	 * LastRwdBPCnt: last reward buy product count 上次发放奖励时购买的产品盒数
	 * TotalBonus: 固定总分红，根据用户级别每天固定分红
	 * TotalDBonus: 动态总分红，根据每天订单量按比例给用户的分红
	 * LastCBTime: last collect bonus time 上次收获分红的时间
	 */
	$sql = "create table if not exists Credit
	(
		UserId int NOT NULL,
		PRIMARY KEY(UserId),
		Credits decimal(10,2) DEFAULT 0,
		Pnts decimal(10,2) DEFAULT 0,
		Charity decimal(10,2) DEFAULT 0,
		Vault decimal(10,2) DEFAULT 0,
		CollChild int DEFAULT 0,
		CollVal int DEFAULT 0,
		BPCnt int DEFAULT 0,
		TotalRecharge int DEFAULT 0,
		TotalWithdraw int DEFAULT 0,
		TotalConsumption decimal(10,2) DEFAULT 0,
		TotalFee decimal(10,2) DEFAULT 0,
		TotalBonus decimal(10,2) DEFAULT 0,
		YearRecharge int DEFAULT 0,
		YearWithdraw int DEFAULT 0,
		YearConsumption decimal(10,2) DEFAULT 0,
		MonthRecharge int DEFAULT 0,
		MonthWithdraw int DEFAULT 0,
		MonthConsumption decimal(10,2) DEFAULT 0,
		DayRecharge int DEFAULT 0,
		DayWithdraw int DEFAULT 0,
		DayConsumption decimal(10,2) DEFAULT 0,
		LastRechargeTime int DEFAULT 0,
		LastWithdrawTime int DEFAULT 0,
		LastConsumptionTime int DEFAULT 0,
		DayObtained decimal(10,2) DEFAULT 0,
		LastObtainedTime int DEFAULT 0,
		LastObtainedPntTime int DEFAULT 0,
		DayObtainedPnts decimal(10,2) DEFAULT 0,
		MonObtainedPnts decimal(10,2) DEFAULT 0,
		YearObtainedPnts decimal(10,2) DEFAULT 0,
		TotalObtainedPnts decimal(10,2) DEFAULT 0,
		CurrBonus decimal(10,2) DEFAULT 0,
		LastCBTime int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Credit table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createAdminTable()
{
	$sql = "create table if not exists AdminTable
	(
		AdminId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(AdminId),
		Name varchar(30) not null,
		Password varchar(256) not null,
		Priority int not null,
		LastLoginTime int default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create AdminTable table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createAddressTable()
{
	$sql = "create table if not exists Address
	(
		AddressId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(AddressId),
		UserId int not null,
		Receiver varchar(30) NOT NULL,
		PhoneNum varchar(15) NOT NULL,
		Address varchar(128) NOT NULL,
		ZipCode varchar(8) DEFAULT ''
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Address table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCreditTradeTable()
{
	/*
	* IdxId: 订单序号，按顺序生成
	* TradeId: 交易订单编号，与时间相关的一个字串，后三位随机生成
	* Quantity: 卖家需交易的总数量
	* BuyCnt: 买家准备购买的数量
	* CreateTime: 卖家创建订单时间
	* ReserveTime: 买家下单时间
	* PayTime: 买家确认支付时间
	* ConfirmTime: 卖家确认支付时间
	* CancelTime: 卖家取消订单时间
	* ApplyDelayTime: 申请延迟时间
	* ApplyDelayIdx: 申请延迟次数
	*/
	$sql = "create table if not exists CreditTrade
	(	
		IdxId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IdxId),
		TradeId varchar(18) not null,
		SellerId int not null,
		SellNickN varchar(16) default '',
		SellPhoneNum varchar(15) default '',
		Quantity int NOT NULL,
		HanderRate decimal(10,2) not null,
		BuyerId int default 0,
		BuyerNickN varchar(16) default '',
		BuyCnt int default 0,
		CreateTime int not null,
		CancelTime int default 0,
		ReserveTime int default 0,
		PayTime int default 0,
		ApplyDelayTime int default 0,
		ApplyDelayIdx int default 0,
		ConfirmTime int default 0,
		Status int not null,
		ComplainStatus int default 0,
		ComplainIdx int default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create CreditTrade table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCreditBankTable()
{
	/*
	 * IdxId: 存储序号
	 * Quantity: 总存储额（玩家能拿回的总返还额度），设计时为投资额的3倍减去分到线下资产和慈善金中的部分
	 * Invest: 买家投资额
	 * Balance: 余额
	 * Divident: 实际分红值，目前分红额占的部分
	 * DiviCnt: 每日分红值，根据比例计算得到
	 * SaveTime: 存储的时间
	 * LastDiviT: 上次获取分红的时间
	 * LastChangeT: 上次余额变换的时间
	 * EmptyTime: 存储额度全部消耗完的时间
	 */
	$sql = "create table if not exists CreditBank
	(	
		IdxId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IdxId),
		UserId int not null,
		Quantity decimal(10,2) not null,
		Invest decimal(10,2) not null,
		Balance decimal(10,2) not null,
		Divident decimal(10,2) default 0,
		DiviCnt	decimal(10,2) not null,
		SaveTime int not null,
		LastDiviT int default 0,
		LastChangeT int default 0,
		EmptyTime int default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create CreditBank table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createTransactionTable()
{
	/*
	 * Type: 订单类型，1：初始订单，2:充话费，3:充油费，10：自由集市
	 * ProductId: 产品ID
	 * AddressId: 添加订单后用户可能修改地址，所以应以记录的地址信息为准。添加原因，发现有订单的地址信息为空，所以添加来防错
	 * CellNum: 充话费的手机号码，或充油卡时的油卡关联手机号
	 * CardNum: 充油费的油卡号码
	 * OrderTime: 下单时间
	 * ConfirmTime: 卖家确认订单时间
	 * DeliveryTime: 发货时间
	 * CompleteTime: 收获／完成时间
	 * CancelTime: 买家取消时间
	 * DismissTime: 卖家取消时间
	 */
	$sql = "create table if not exists Transaction
	(
		OrderId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(OrderId),
		UserId int NOT NULL,
		Type int not null,
		ProductId int NOT NULL,
		Price decimal(10,2) NOT NULL,
		HandleFee decimal(10,2) default 0,
		Count int NOT NULL,
		AddressId int default 0,
		Receiver varchar(30) DEFAULT '',
		PhoneNum varchar(15) DEFAULT '',
		Address varchar(128) DEFAULT '',
		ZipCode varchar(12) DEFAULT '',
		CellNum varchar(15) default '',
		CardNum varchar(30) default '',
		OrderTime int NOT NULL,
		ConfirmTime int default 0,
		DeliveryTime int DEFAULT 0,
		CompleteTime int DEFAULT 0,
		CancelTime int default 0,
		DismissTime int default 0,
		CourierNum varchar(24) default '',
		Status int
	)";

	$result = mysql_query($sql);
	if (!$result) {
		echo "create Transaction table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCompanyTable()
{
	$sql = "create table if not exists Company
	(
		CompanyId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(CompanyId),
		Contacter varchar(30),
		PhoneNum varchar(15)
	)";
	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Company table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createOfflineShopTable()
{
	/*
	 * ShopId: 线上商店编号
	 * UserId: 商店所属用户的用户ID
	 * ShopName: 公司名称
	 * Contacter: 联系人／法人
	 * PhoneNum: 联系电话
	 * Address: 商店地址
	 * LicencePic: 营业执照照片
	 * RegisterTime: 注册时间
	 * ModifiedTime: 更新信息时间
	 * ReadyForCheckTime: 提交审查时间
	 * OnlineTime: 上线时间
	 * DeclineReason: 审核失败原因
	 * TradeTimes: 收款次数
	 * TradeAmount: 收款总额
	 * TradeIncome: 实际收入，从首款总额中减去手续费及给推荐人的分红
	 * TradeFee: 手续费，为平台收入
	 * Status: 商店状态
	 */
	$sql = "create table if not exists OfflineShop
	(
		ShopId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(ShopId),
		UserId int not null,
		ShopName varchar(128) default '',
		Contacter varchar(32) default '',
		PhoneNum varchar(16) default '',
		Address varchar(256) default '',
		LicencePic varchar(32) default '',
		RegisterTime int not null,
		ModifiedTime int default 0,
		ReadyForCheckTime int default 0,
		OnlineTime int default 0,
		DeclineReason varchar(256) default '',
		TradeTimes int default 0,
		TradeAmount int default 0,
		TradeIncome int default 0,
		TradeFee int default 0,
		Status int default 0		
	) ENGINE=MEMORY AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create OfflineShop table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createProductTable()
{
	$sql = "create table if not exists Product
	(
		ProductId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(ProductId),
		CompanyId int,
		Price decimal(10,2) NOT NULL,
		ProductName varchar(30),
		ProductDesc varchar(128),
		FirstImg varchar(128) DEFAULT '',
		SecondImg varchar(128) DEFAULT '',
		ExhibitImg varchar(128) DEFAULT '',
		AddTime int NOT NULL,
		LastModifyTime int DEFAULT 0,
		OffTime int DEFAULT 0, 
		LimitOneDay int DEFAULT 0,
		Status int NOT NULL DEFAULT 1
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Product table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createProductDayBoughtTable()
{
	$sql = "create table if not exists ProductDayBought
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		ProductId int NOT NULL,
		LastBoughtTime int DEFAULT 0,
		Count int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ProductDayBought table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createProductLevelBoughtTable()
{
	$sql = "create table if not exists ProductLevelBought
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		Level int not null,
		ProductId int NOT NULL,
		LastBoughtTime int DEFAULT 0,
		Count int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ProductLevelBought table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createRechargeTable()
{
	$sql = "create table if not exists RechargeApplication
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		Amount int NOT NULL,
		ApplyTime int NOT NULL,
		AcceptTime int DEFAULT 0,
		NickName varchar(16) DEFAULT '',
		PhoneNum varchar(15) NOT NULL,
		Method int NOT NULL,
		Account varchar(32) NOT NULL,
		BankUser varchar(16) default '',
		BankName varchar(16) default '',
		BankBranch varchar(32) default '',
		DeclineTime int DEFAULT 0,
		AdminId int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create RechargeApplication table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createWithdrawTable()
{
	$sql = "create table if not exists WithdrawApplication
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		ApplyAmount int NOT NULL,
		ActualAmount int NOT NULL,
		ApplyTime int NOT NULL,
		AcceptTime int DEFAULT 0,
		NickName varchar(16) DEFAULT '',
		PhoneNum varchar(15) NOT NULL,
		Method int NOT NULL,
		Account varchar(32) NOT NULL,
		BankUser varchar(16) default '',
		BankName varchar(16) default '',
		BankBranch varchar(32) default '',
		DeclineTime int DEFAULT 0,
		AdminId int DEFAULT 0
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create WithdrawApplication table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createWechatTable()
{
	$sql = "create table if not exists WechatAccount
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		WechatAcc varchar(32) NOT NULL
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create WechatAccount table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createAlipayTable()
{
	$sql = "create table if not exists AlipayAccount
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		AlipayAcc varchar(32) NOT NULL
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create AlipayAccount table error: " . mysql_error() . "<br>";
	}
	return $result;	
}

function createBankAccountTable()
{
	$sql = "create table if not exists BankAccount
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		BankAcc varchar(32) NOT NULL,
		AccName varchar(16) NOT NULL,
		BankName varchar(24) NOT NULL,
		BankBranch varchar(32) default ''
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create BankAccount table error: " . mysql_error() . "<br>";
	}
	return $result;		
}

function createCreditRecordTable()
{
	/*
	 * Amount - 实际变动值
	 * CurrAmount - 变动后的数值
	 * RelatedAmount - 相关的数值，如线下商家支付时，为买家支付金额
	 * HandleFee - 手续费
	 * ApplyTime - 申请时间，即为变动时间
	 * ApplyIndexId - 相关数据的记录id
	 * AcceptTime - 接受时间
	 * WithUserId - 交易对象的id
	 * Type - 记录类型
	 */
	$sql = "create table if not exists CreditRecord
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		Amount decimal(10,2) NOT NULL,
		CurrAmount decimal(10,2) NOT NULL,
		HandleFee decimal(10,2) DEFAULT 0,
		ApplyTime int NOT NULL,
		ApplyIndexId int DEFAULT 0,
		AcceptTime int DEFAULT 0,
		WithUserId int DEFAULT 0,
		Type int NOT NULL
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create CreditRecord table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createPntsRecordTable()
{
	/*
	 * Amount - 实际变动值
	 * CurrAmount - 变动后的数值
	 * RelatedAmount - 相关的数值，如线下商家支付时，为买家支付金额
	 * HandleFee - 手续费
	 * ApplyTime - 申请时间，即为变动时间
	 * ApplyIndexId - 相关数据的记录id
	 * AcceptTime - 接受时间，暂不使用
	 * WithStoreId - 交易商店的id
	 * WithUserId - 交易对象的id
	 * Type - 记录类型
	 */
	$sql = "create table if not exists PntsRecord
	(
		IndexId int not null AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int not null,
		Amount decimal(10,2) not null,
		CurrAmount decimal(10,2) not null,
		RelatedAmount decimal(10,2) default 0,
		HandleFee decimal(10,2) default 0,
		ApplyTime int not null,
		ApplyIndexId int default 0,
		AcceptTime int default 0,
		WithStoreId int default 0,
		WithUserId int default 0,
		Type int not null
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create PntsRecord table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createComplaintTable()
{
	/*
	 * 投诉信息表
	 * Type - 投诉类型
	 * RelatedIdx - 投诉对应问题的序列号
	 * RelatedIssueId - 投诉对应问题的可见编号（如交易所交易的交易编号）
	 * ComplainantId - 投诉人ID
	 * CompNickname - 投诉人昵称
	 * CompPhoneNum - 投诉人手机号
	 * RespondentId - 被投诉人ID
	 * RespNickname - 被投诉人昵称
	 * RespPhoneNum - 被投诉人手机号
	 * IssueDesc - 投诉详情描述
	 * IssueTime - 发起投诉时间
	 * RespTime - 被投诉人反馈时间
	 * RespCloseTime - 被投诉人关闭反馈时间
	 * CompCloseTime - 投诉人关闭投诉时间
	 * Status - 投诉状态
	 */
	$sql = "create table if not exists Complaint
	(
		IndexId int not null AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		CompId varchar(16) not null,
		Type int not null,
		RelatedIdx int default 0,
		RelatedIssueId varchar(24) not null,
		ComplainantId int not null,
		CompNickname varchar(16) default '',
		CompPhoneNum varchar(15) not null,
		RespondentId int not null,
		RespNickname varchar(16) default '',
		RespPhoneNum varchar(15) not null,		
		IssueDesc varchar(256) default '',		
		IssueTime int not null,
		RespTime int default 0,
		CompCloseTime int default 0,
		RespCloseTime int default 0,
		Status int not null
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Complaint table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createStatisticsTable()
{
	/*
	 * Ye 	
	 * Mon
	 * Day 
	 * NSCount  - New User Count 新用户总数
	 * RecommendTotal - 注册新用户投入了云量总数
	 * ReinventTotal - 存储到银行的云量总数
	 * BonusTotal - 用户每日领取的存储利息总和
 	 * ExchangeNewQuan - 每日挂单的云量总额
 	 * ExchangeNewCnt - 每日挂单单数
	 * ExchangeSuccQuan - 每日成功交易的云量总额
	 * ExchangeSuccCnt - 每日成功交易单数
	 * ExchangeFee - 每日成功交易收取的手续费
	 * WithdrawTotal - 每日使用云量在现实中的实际数额，相当于用户取现，如话费充值，油卡充值
	 * WithdrawFee - 每日使用云量在实际生活中的手续费
	 * OlShopCnt - 每日注册的线下商家数量
	 * OlShopRegiFee - 每日注册的线下商家使用的注册费
	*/
	$sql = "create table if not exists Statistics
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		Ye int NOT NULL,
		Mon int NOT NULL,
		Day int NOT NULL,
		NSCount int DEFAULT 0,
		RecommendTotal decimal(10,2) DEFAULT 0,
		ReinventTotal decimal(10,2) default 0,
		BonusTotal decimal(10,2) default 0,
		ExchangeNewQuan decimal(10,2) default 0,
		ExchangeNewCnt int default 0,
		ExchangeSuccQuan decimal(10,2) default 0,
		ExchangeSuccCnt int default 0,
		ExchangeFee	decimal(10,2) default 0,
		WithdrawTotal decimal(10,2) default 0,
		WithdrawFee	decimal(10,2) default 0,
		OlShopCnt int default 0,
		OlShopRegiFee decimal(10,2) default 0,
		OlShopTradeCnt int default 0,
		OlShopTradeFee decimal(10,2) default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Statistics table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createTotalStatisTable()
{
	/*
	 * CreditsPool - 系统云量池
	 * CharityPool - 慈善金池
	 * UserCount - 用户总数
	 * RecommendTotal - 注册新用户投入了云量总数
	 * ReinventTotal - 存储到银行的云量总数
	 * BonusTotal - 领取的存储利息总和
 	 * ExchangeNewQuan - 挂单的云量总额
 	 * ExchangeNewCnt - 挂单总单数
	 * ExchangeSuccQuan - 成功交易的云量总额
	 * ExchangeSuccCnt - 成功交易总单数
	 * ExchangeFee - 成功交易收取的手续费
	 * WithdrawTotal - 使用云量在现实中的实际数额，相当于用户取现，如话费充值，油卡充值等
	 * WithdrawFee - 使用云量在实际生活中的手续费
	 * OlShopRegiFee - 注册的线下商家使用的注册费总额
	 */
	$sql = "create table if not exists TotalStatis
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		CreditsPool	decimal(10,2) DEFAULT 10000000,
		CharityPool decimal(10,2) default 0,
		UserCount int DEFAULT 0,
		RecommendTotal decimal(10,2) DEFAULT 0,
		ReinventTotal decimal(10,2) default 0,
		BonusTotal decimal(10,2) default 0,
		ExchangeNewQuan decimal(10,2) default 0,
		ExchangeNewCnt int default 0,
		ExchangeSuccQuan decimal(10,2) default 0,
		ExchangeSuccCnt int default 0,
		ExchangeFee	decimal(10,2) default 0,
		WithdrawTotal decimal(10,2) default 0,
		WithdrawFee	decimal(10,2) default 0,
		OlShopRegiFee decimal(10,2) default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create TotalStatis table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createShortStatisTable()
{
	/*
	 * LastCalcTime: 上次计算分红的时间
	 * LastDCalcTime: 上次计算动态分红的时间
	 * !!! 动态分红和静态分红默认一起计算，但也可能出问题而分开，静态分红一天最多一次，动态分红可一日多次 
	 */
	$sql = "create table if not exists ShortStatis
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		Recharge int DEFAULT 0,
		Withdraw int DEFAULT 0,
		Transfer int DEFAULT 0,
		OrderGross decimal(10,2) DEFAULT 0,
		WithdrawFee decimal(10,2) DEFAULT 0,
		TransferFee decimal(10,2) DEFAULT 0,
		BonusTotal decimal(10,2) DEFAULT 0,
		BonusLeft decimal(10,2) DEFAULT 0,
		DBonusTotal decimal(10,2) DEFAULT 0,
		DBonusLeft decimal(10,2) DEFAULT 0,
		LastCalcTime int DEFAULT 0,
		LastDCalcTime int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ShortStatis table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createPostTable()
{
	/*
	 * AddTime: 添加时间
	 * OnlineTime: 上线时间
	 * LMT: 上次更新时间
	 * Ret: 下架时间
	 */
	$sql = "create table if not exists PostTable
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		Title varchar(64) not null,
		TextFile varchar(32) not null,
		Pic varchar(32) default '',
		AddTime int not null,
		OnlineTime int default 0,
		LMT int default 0,
		ReT int default 0,
		Status int default 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create PostTable table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function isInTheSameDay($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$month1 = date("m", $time1);
	$day1 = date("d", $time1);

	$year2 = date("Y", $time2);
	$month2 = date("m", $time2);
	$day2 = date("d", $time2);
	
	return $year1 == $year2 && $month1 == $month2 and $day1 == $day2;
}

function isInTheSameMonth($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$month1 = date("m", $time1);

	$year2 = date("Y", $time2);
	$month2 = date("m", $time2);
	
	return $year1 == $year2 && $month1 == $month2;
}

function isInTheSameYear($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$year2 = date("Y", $time2);
	
	return $year1 == $year2;
}

function getMonthConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["MonthConsumption"] == 0) {
		return 0;
	}

	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameMonth($lasttime, time())) {
		mysql_query("update Credit set MonthConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["MonthConsumption"];

}

function updateMonthConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set MonthConsumption='$consumption' and LastConsumptionTime='$time' where UserId='$userid'");
		return;
	}	

	$dayConsumption = $row["MonthConsumption"];
	if (!isInTheSameMonth($lasttime, $time)) {
		$dayConsumption = 0;
	}
	
	$dayConsumption += $consumption;
	mysql_query("update Credit set isInTheSameMonth='$dayConsumption' and LastConsumptionTime='$time' where UserId='$userid'");
	return;

}

function getDayConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["DayConsumption"] == 0) {
		return 0;
	}

	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameDay($lasttime, time())) {
		mysql_query("update Credit set DayConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["DayConsumption"];
}

function updateDayConsumption($userid, $consumption, $time) 
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set DayConsumption='$consumption' and LastConsumptionTime='$time' where UserId='$userid'");
		return;
	}	

	$dayConsumption = $row["DayConsumption"];
	if (!isInTheSameDay($lasttime, $time)) {
		$dayConsumption = 0;
	}
	
	$dayConsumption += $consumption;
	mysql_query("update Credit set DayConsumption='$dayConsumption' and LastConsumptionTime='$time' where UserId='$userid'");
	return;
}

function getDayObtained($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["DayObtained"] == 0) {
		return 0;
	}

	$lasttime = $row["LastObtainedTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameDay($lasttime, time())) {
		mysql_query("update Credit set DayConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["DayObtained"];
}

function updateDayObtained($userid, $obtained, $time)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastObtainedTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set DayObtained='$obtained' and LastObtainedTime='$time' where UserId='$userid'");
		return;
	}	

	$dayObtained = $row["DayObtained"];
	if (!isInTheSameDay($lasttime, $time)) {
		$dayObtained = 0;
	}
	
	$dayObtained += $obtained;
	mysql_query("update Credit set DayConsumption='$dayObtained' and LastObtainedTime='$time' where UserId='$userid'");
	return;
}

function calcHandleFee($amount, $rate) {
	$fee = floor($amount * $rate * 100) / 100;
	return $fee;
}

function getDayBoughtCount($userid, $productid)
{
	$count = 0;
	$result = mysql_query("select * from ProductDayBought where UserId='$userid' and ProductId='$productid'");
	if ($result && mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_assoc($result);
		$lTime = $row["LastBoughtTime"];
		$now = time();
		if (isInTheSameDay($lTime, $now)) {
			$count = $row["Count"];
		}
	}
	
	return $count;
}

function getLevelBoughtCnt($userid, $lvl, $productid)
{
	$count = 0;
	$res = mysql_query("select * from ProductLevelBought where UserId='$userid' and Level='$lvl' and ProductId='$productid'");
	if ($res && mysql_num_rows($res) > 0) {
		
		$row = mysql_fetch_assoc($res);
		$count = $row["Count"];
	}
	return $count;
}

function updateDayBoughtCount($userid, $productid, $count) 
{
	$result = createProductDayBoughtTable();
	if (!$result) {
		return;
	}
	
	$result = mysql_query("select * from ProductDayBought where UserId='$userid' and ProductId='$productid'");
	if ($result) {
		
		$now = time();
		if (mysql_num_rows($result) > 0) {
		
			$row = mysql_fetch_assoc($result);
			$lTime = $row["LastBoughtTime"];
			if (isInTheSameDay($lTime, $now)) {
				$count += $row["Count"];
			}
			
			mysql_query("update ProductDayBought set Count='$count', LastBoughtTime='$now' where UserId='$userid' and ProductId='$productid'");
		}
		else {
			mysql_query("insert into ProductDayBought (UserId, ProductId, Count, LastBoughtTime)
							VALUES('$userid', '$productid', '$count', '$now')");
		}
	}
}

function updateLevelBoughtCount($userid, $lvl, $productid, $count) 
{
	$result = createProductLevelBoughtTable();
	if (!$result) {
		return;
	}
	
	$result = mysql_query("select * from ProductLevelBought where UserId='$userid' and Level='$lvl' and ProductId='$productid'");
	if ($result) {
		
		$now = time();
		if (mysql_num_rows($result) > 0) {
		
			$row = mysql_fetch_assoc($result);
			$count += $row["Count"];
			
			mysql_query("update ProductLevelBought set Count='$count', LastBoughtTime='$now' where UserId='$userid' and Level='$lvl' and ProductId='$productid'");
		}
		else {
			mysql_query("insert into ProductLevelBought (UserId, Level, ProductId, Count, LastBoughtTime)
							values('$userid', '$lvl', '$productid', '$count', '$now')");
		}
	}
}

function initGeneralStatisTable()
{
	$result = createTotalStatisTable();
	if ($result) {
		
		$res = mysql_query("select * from TotalStatis");
		if (!$res) {
			echo "init general statis error: " . mysql_error() . "<br>";
		}
		else {
			if (mysql_num_rows($res) > 0) {
				// inited
			}
			else {
				$res1 = mysql_query("insert into TotalStatis (CreditsPool) VALUES('9999000')");
				if (!$res1) {
					echo "insert into general statis error: " . mysql_error() . "<br>";
				}
			}
		}
	}
	
/*
	$result = createShortStatisTable();
	if ($result) {
		$res = mysql_query("select * from ShortStatis");
		if (!$res) {
			echo "init short statis error: " . mysql_error() . "<br>";
		}
		else {
			if (mysql_num_rows($res) > 0) {
				// inited
			}
			else {
				$res1 = mysql_query("insert into ShortStatis (LastCalcTime) VALUES('0')");
				if (!$res1) {
					echo "insert into short statis error: " . mysql_error() . "<br>";
				}
			}
		}
	}
*/
}




?>