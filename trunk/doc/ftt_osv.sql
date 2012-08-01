SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `ftt_osv` ;
CREATE SCHEMA IF NOT EXISTS `ftt_osv` DEFAULT CHARACTER SET utf8 ;
USE `ftt_osv` ;

-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_bill`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_bill` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_bill` (
  `b_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水号' ,
  `b_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `b_sid` INT(10) UNSIGNED NOT NULL COMMENT '结算方式ID' ,
  `b_snm` VARCHAR(30) NOT NULL COMMENT '结算方式名' ,
  `b_attr` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '账单属性' ,
  `b_name` VARCHAR(100) NULL DEFAULT NULL COMMENT '账单名' ,
  `b_cost` INT(11) NOT NULL DEFAULT '0' COMMENT '应收总额' ,
  `b_paid` INT(11) NOT NULL DEFAULT '0' COMMENT '已收总额' ,
  `b_memo` TEXT NULL DEFAULT NULL COMMENT '备注内容' ,
  `b_ltime` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '保留时间' ,
  `b_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `b_mtime` INT(10) UNSIGNED NOT NULL COMMENT '更新时间' ,
  `b_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '账单状态' ,
  PRIMARY KEY (`b_id`) ,
  INDEX `IDX_HID` (`b_hid` ASC) ,
  INDEX `IDX_SID` (`b_sid` ASC) ,
  INDEX `IDX_CTIME` (`b_ctime` ASC) ,
  INDEX `IDX_COST` (`b_cost` ASC) ,
  INDEX `IDX_PAID` (`b_paid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '账单表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_bill_expense`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_bill_expense` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_bill_expense` (
  `be_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水号' ,
  `be_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `be_bid` INT(10) UNSIGNED NOT NULL COMMENT '账单ID' ,
  `be_uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID' ,
  `be_sum` INT(11) NOT NULL COMMENT '金额，以分为单位' ,
  `be_time` INT(11) NOT NULL COMMENT '创建时间' ,
  `be_user` VARCHAR(20) NULL DEFAULT NULL COMMENT '用户名' ,
  `be_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '备注' ,
  PRIMARY KEY (`be_id`) ,
  INDEX `IDX_HID` (`be_hid` ASC) ,
  INDEX `IDX_BID` (`be_bid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店账单应收流水';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_bill_journal`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_bill_journal` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_bill_journal` (
  `bj_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水号' ,
  `bj_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `bj_bid` INT(10) UNSIGNED NOT NULL COMMENT '账单ID' ,
  `bj_uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID' ,
  `bj_pid` INT(10) UNSIGNED NOT NULL COMMENT '支付渠道ID' ,
  `bj_sum` INT(11) NOT NULL COMMENT '支付金额，以分为单位' ,
  `bj_time` INT(11) NOT NULL COMMENT '收款时间' ,
  `bj_user` VARCHAR(20) NULL DEFAULT NULL COMMENT '用户名' ,
  `bj_pynm` VARCHAR(30) NULL DEFAULT NULL COMMENT '支付渠道名' ,
  `bj_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '备注' ,
  PRIMARY KEY (`bj_id`) ,
  INDEX `IDX_HID` (`bj_hid` ASC) ,
  INDEX `IDX_BID` (`bj_bid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店账单已收流水';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_hotel`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_hotel` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_hotel` (
  `h_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '旅店ID' ,
  `h_attr` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '旅店属性' ,
  `h_iname` VARCHAR(15) NOT NULL COMMENT '旅店唯一标识名' ,
  `h_title` VARCHAR(50) NULL DEFAULT NULL COMMENT '旅店标头' ,
  `h_name` VARCHAR(50) NULL DEFAULT NULL COMMENT '旅店名' ,
  `h_note` VARCHAR(500) NULL DEFAULT NULL COMMENT '预订须知' ,
  `h_country` VARCHAR(50) NULL DEFAULT NULL COMMENT '所在国家' ,
  `h_province` VARCHAR(50) NULL DEFAULT NULL COMMENT '所在省份' ,
  `h_city` VARCHAR(50) NULL DEFAULT NULL COMMENT '所在城市' ,
  `h_website` VARCHAR(50) NULL DEFAULT NULL COMMENT '网址' ,
  `h_domain` VARCHAR(50) NULL DEFAULT NULL COMMENT '旅店指向系统的域名' ,
  `h_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '旅店邮箱' ,
  `h_phone` VARCHAR(20) NULL DEFAULT NULL COMMENT '联系电话' ,
  `h_address` VARCHAR(250) NULL DEFAULT NULL COMMENT '详细地址' ,
  `h_order_default_saleman` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单默认预订员ID' ,
  `h_order_default_typedef` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单默认预订类型' ,
  `h_order_default_channel` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单默认预订渠道' ,
  `h_order_default_payment` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单默认支付渠道' ,
  `h_obill_default_settlem` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单默认结算方式' ,
  `h_order_default_stacode` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '订单创建默认状态' ,
  `h_order_enddays` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '365' COMMENT '预订天数后临界点' ,
  `h_order_minlens` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT '订单最小预订间夜' ,
  `h_order_maxlens` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '31' COMMENT '订单最大预订间夜' ,
  `h_order_enabled` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '预订可用组' ,
  `h_obill_keptime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '账单预订保留时间' ,
  `h_rosta_visible` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '房态可见组' ,
  `h_checkin_time` INT(5) UNSIGNED NOT NULL DEFAULT '50400' COMMENT '订单默认入住时间' ,
  `h_checkout_time` INT(5) UNSIGNED NOT NULL DEFAULT '43200' COMMENT '订单默认退房时间' ,
  `h_prompt_checkin` INT(10) UNSIGNED NOT NULL DEFAULT '30' COMMENT '提前多少分钟提示入住' ,
  `h_prompt_checkout` INT(10) UNSIGNED NOT NULL DEFAULT '30' COMMENT '提前多少分钟提示退房' ,
  `h_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `h_mtime` INT(10) UNSIGNED NOT NULL COMMENT '最后更改时间' ,
  `h_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '帐号状态，0为停用，>1为可用' ,
  PRIMARY KEY (`h_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店信息表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_hotel_channel`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_hotel_channel` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_hotel_channel` (
  `hc_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `hc_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店id' ,
  `hc_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '渠道名称' ,
  `hc_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '附加说明' ,
  `hc_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `hc_mtime` INT(10) UNSIGNED NOT NULL COMMENT '修改时间' ,
  `hc_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '渠道状态，0为停用，>1为可用' ,
  PRIMARY KEY (`hc_id`) ,
  INDEX `IDX_HID` (`hc_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店订单预订渠道';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_hotel_payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_hotel_payment` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_hotel_payment` (
  `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `hp_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店id' ,
  `hp_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '渠道名' ,
  `hp_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '附加说明' ,
  `hp_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `hp_mtime` INT(10) UNSIGNED NOT NULL COMMENT '修改时间' ,
  `hp_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当前状态，0为停用，>1为可用' ,
  PRIMARY KEY (`hp_id`) ,
  INDEX `IDX_HID` (`hp_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店订单支付渠道';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_hotel_settlem`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_hotel_settlem` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_hotel_settlem` (
  `hs_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `hs_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店id' ,
  `hs_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '模式名称' ,
  `hs_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '附加说明' ,
  `hs_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `hs_mtime` INT(10) UNSIGNED NOT NULL COMMENT '修改时间' ,
  `hs_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模式状态，0为停用，>1为可用' ,
  PRIMARY KEY (`hs_id`) ,
  INDEX `IDX_HID` (`hs_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店账单结算方式';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_hotel_typedef`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_hotel_typedef` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_hotel_typedef` (
  `ht_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `ht_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店id' ,
  `ht_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '类型名称' ,
  `ht_memo` VARCHAR(200) NULL DEFAULT NULL COMMENT '附加说明' ,
  `ht_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `ht_mtime` INT(10) UNSIGNED NOT NULL COMMENT '修改时间' ,
  `ht_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类型状态，0为停用，>1为可用' ,
  PRIMARY KEY (`ht_id`) ,
  INDEX `IDX_HID` (`ht_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店订单预订类型';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_log_bill`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_log_bill` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_log_bill` (
  `lb_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `lb_ip` VARCHAR(40) NULL DEFAULT NULL COMMENT '操作IP' ,
  `lb_mid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID' ,
  `lb_xid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '目标ID' ,
  `lb_xnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '目标名' ,
  `lb_pid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人ID' ,
  `lb_pnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作人名' ,
  `lb_act` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作类型' ,
  `lb_name` VARCHAR(200) NULL DEFAULT NULL COMMENT '操作名称' ,
  `lb_memo` TEXT NULL DEFAULT NULL COMMENT '操作事项' ,
  `lb_date` DATETIME NULL DEFAULT NULL COMMENT '操作日期' ,
  `lb_data` TEXT NULL DEFAULT NULL COMMENT '业务数据（json格式）' ,
  PRIMARY KEY (`lb_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '账单操作日志记录表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_log_order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_log_order` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_log_order` (
  `lo_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `lo_ip` VARCHAR(40) NULL DEFAULT NULL COMMENT '操作IP' ,
  `lo_mid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID' ,
  `lo_xid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '目标ID' ,
  `lo_xnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '目标名' ,
  `lo_pid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人ID' ,
  `lo_pnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作人名' ,
  `lo_act` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作类型' ,
  `lo_name` VARCHAR(200) NULL DEFAULT NULL COMMENT '操作名称' ,
  `lo_memo` TEXT NULL DEFAULT NULL COMMENT '操作事项' ,
  `lo_date` DATETIME NULL DEFAULT NULL COMMENT '操作日期' ,
  `lo_data` TEXT NULL DEFAULT NULL COMMENT '业务数据（json格式）' ,
  PRIMARY KEY (`lo_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '订单操作日志记录表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_log_room`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_log_room` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_log_room` (
  `lr_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `lr_ip` VARCHAR(40) NULL DEFAULT NULL COMMENT '操作IP' ,
  `lr_mid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID' ,
  `lr_xid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '目标ID' ,
  `lr_xnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '目标名' ,
  `lr_pid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人ID' ,
  `lr_pnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作人名' ,
  `lr_act` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作类型' ,
  `lr_name` VARCHAR(200) NULL DEFAULT NULL COMMENT '操作名称' ,
  `lr_memo` TEXT NULL DEFAULT NULL COMMENT '操作事项' ,
  `lr_date` DATETIME NULL DEFAULT NULL COMMENT '操作日期' ,
  `lr_data` TEXT NULL DEFAULT NULL COMMENT '业务数据（json格式）' ,
  PRIMARY KEY (`lr_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '房间操作日志记录表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_log_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_log_user` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_log_user` (
  `lu_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `lu_ip` VARCHAR(40) NULL DEFAULT NULL COMMENT '操作IP' ,
  `lu_mid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID' ,
  `lu_xid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '目标ID' ,
  `lu_xnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '目标名' ,
  `lu_pid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人ID' ,
  `lu_pnm` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作人名' ,
  `lu_act` VARCHAR(100) NULL DEFAULT NULL COMMENT '操作类型' ,
  `lu_name` VARCHAR(200) NULL DEFAULT NULL COMMENT '操作名称' ,
  `lu_memo` TEXT NULL DEFAULT NULL COMMENT '操作事项' ,
  `lu_date` DATETIME NULL DEFAULT NULL COMMENT '操作日期' ,
  `lu_data` TEXT NULL DEFAULT NULL COMMENT '业务数据（json格式）' ,
  PRIMARY KEY (`lu_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '用户操作日志记录表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_mail_job`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_mail_job` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_mail_job` (
  `mj_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水号' ,
  `mj_from_name` VARCHAR(255) NULL DEFAULT NULL COMMENT '发件人姓名' ,
  `mj_to_name` VARCHAR(255) NULL DEFAULT NULL COMMENT '收件人姓名' ,
  `mj_to_email` LONGTEXT NULL DEFAULT NULL COMMENT '收件人邮箱' ,
  `mj_title` TEXT NULL DEFAULT NULL COMMENT '标题' ,
  `mj_content` LONGTEXT NULL DEFAULT NULL COMMENT '内容' ,
  `mj_sendtimes` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT '发送次数' ,
  `mj_ecode` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '错误代码' ,
  `mj_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  PRIMARY KEY (`mj_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '邮件发送队列表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_mber`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_mber` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_mber` (
  `m_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `m_no` VARCHAR(30) NOT NULL COMMENT '会员编号' ,
  `m_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `m_name` VARCHAR(30) NOT NULL COMMENT '姓名' ,
  `m_type` VARCHAR(30) NULL DEFAULT NULL COMMENT '类型' ,
  `m_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '邮箱' ,
  `m_phone` VARCHAR(20) NULL DEFAULT NULL COMMENT '电话' ,
  `m_idno` VARCHAR(30) NULL DEFAULT NULL COMMENT '证件号码' ,
  `m_idtype` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '证件类型' ,
  `m_gender` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '性别（0未知 1-男 2-女）' ,
  `m_ctime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间' ,
  `m_mtime` INT(10) NOT NULL DEFAULT '0' COMMENT '修改时间' ,
  `m_status` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '会员状态' ,
  `m_memo` TEXT NULL DEFAULT NULL COMMENT '备注信息' ,
  PRIMARY KEY (`m_id`) ,
  INDEX `IDX_NO` (`m_no` ASC) ,
  INDEX `IDX_HID` (`m_hid` ASC) ,
  INDEX `IDX_NAME` (`m_name` ASC) ,
  INDEX `IDX_IDNO` (`m_idno` ASC) ,
  INDEX `IDX_EMAIL` (`m_email` ASC) ,
  INDEX `IDX_PHONE` (`m_phone` ASC) ,
  INDEX `IDX_TYPE` (`m_type` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '旅店会员表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_order` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_order` (
  `o_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单号' ,
  `o_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店id' ,
  `o_bid` INT(10) UNSIGNED NOT NULL COMMENT '账单ID' ,
  `o_mid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '旅店会员ID' ,
  `o_mno` VARCHAR(30) NULL DEFAULT NULL COMMENT '旅店会员编号' ,
  `o_rid` INT(10) UNSIGNED NOT NULL COMMENT '房间ID' ,
  `o_sid` INT(10) UNSIGNED NOT NULL COMMENT '订单预订员' ,
  `o_snm` VARCHAR(30) NOT NULL COMMENT '预订员姓名' ,
  `o_tid` INT(10) UNSIGNED NOT NULL COMMENT '预订类型ID' ,
  `o_tnm` VARCHAR(30) NULL DEFAULT NULL COMMENT '预订类型名' ,
  `o_cid` INT(10) UNSIGNED NOT NULL COMMENT '预订渠道ID' ,
  `o_cnm` VARCHAR(30) NULL DEFAULT NULL COMMENT '预订渠道名' ,
  `o_room` VARCHAR(30) NOT NULL COMMENT '房间名' ,
  `o_gbker_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '姓名' ,
  `o_gbker_idno` VARCHAR(30) NULL DEFAULT NULL COMMENT '证件号码' ,
  `o_gbker_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '邮箱' ,
  `o_gbker_phone` VARCHAR(30) NULL DEFAULT NULL COMMENT '手机' ,
  `o_gbker_idtype` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '证件类型（0-身份证 1-护照 2-军官证 9-其它）' ,
  `o_glver_name` VARCHAR(30) NULL DEFAULT NULL COMMENT '姓名' ,
  `o_glver_idno` VARCHAR(30) NULL DEFAULT NULL COMMENT '证件号码' ,
  `o_glver_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '邮箱' ,
  `o_glver_phone` VARCHAR(30) NULL DEFAULT NULL COMMENT '手机' ,
  `o_glver_idtype` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '证件类型（0-身份证 1-护照 2-军官证 9-其它）' ,
  `o_price` INT(10) UNSIGNED NOT NULL COMMENT '成交房费' ,
  `o_brice` INT(10) UNSIGNED NOT NULL COMMENT '账单房费' ,
  `o_attr` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单属性' ,
  `o_bdatm` INT(10) UNSIGNED NOT NULL COMMENT '预计入住日期' ,
  `o_edatm` INT(10) UNSIGNED NOT NULL COMMENT '预计离店日期' ,
  `o_btime` INT(10) UNSIGNED NOT NULL COMMENT '预计入住时间' ,
  `o_etime` INT(10) UNSIGNED NOT NULL COMMENT '预计离店时间' ,
  `o_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `o_mtime` INT(10) UNSIGNED NOT NULL COMMENT '更新时间' ,
  `o_memo` TEXT NULL DEFAULT NULL COMMENT '备注' ,
  `o_status` TINYINT(3) UNSIGNED NOT NULL COMMENT '订单状态' ,
  `o_prices` TEXT NOT NULL COMMENT '成交房费，序列化字符串' ,
  `o_brices` TEXT NOT NULL COMMENT '账单房费，序列化字符串' ,
  PRIMARY KEY (`o_id`) ,
  INDEX `IDX_HID` (`o_hid` ASC) ,
  INDEX `IDX_BID` (`o_bid` ASC) ,
  INDEX `IDX_RID` (`o_rid` ASC) ,
  INDEX `IDX_CID` (`o_cid` ASC) ,
  INDEX `IDX_BTIME` (`o_btime` ASC) ,
  INDEX `IDX_ETIME` (`o_etime` ASC) ,
  INDEX `IDX_CTIME` (`o_ctime` ASC) ,
  INDEX `IDX_BDATM` (`o_bdatm` ASC) ,
  INDEX `IDX_EDATM` (`o_edatm` ASC) ,
  INDEX `IDX_TID` (`o_tid` ASC) ,
  INDEX `IDX_SID` (`o_sid` ASC) ,
  INDEX `IDX_STATUS` (`o_status` ASC) ,
  INDEX `IDX_MID` (`o_mid` ASC) ,
  INDEX `IDX_GBKER_NAME` (`o_gbker_name` ASC) ,
  INDEX `IDX_GBKER_IDNO` (`o_gbker_idno` ASC) ,
  INDEX `IDX_GBKER_EMAIL` (`o_gbker_email` ASC) ,
  INDEX `IDX_GBKER_PHONE` (`o_gbker_phone` ASC) ,
  INDEX `IDX_GLVER_NAME` (`o_glver_name` ASC) ,
  INDEX `IDX_GLVER_IDNO` (`o_glver_idno` ASC) ,
  INDEX `IDX_GLVER_EMAIL` (`o_glver_email` ASC) ,
  INDEX `IDX_GLVER_PHONE` (`o_glver_phone` ASC) ,
  INDEX `IDX_MNO` (`o_mno` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '订单表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_room`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_room` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_room` (
  `r_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '房间ID' ,
  `r_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `r_attr` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '附加属性' ,
  `r_name` VARCHAR(30) NOT NULL COMMENT '房间名' ,
  `r_zone` VARCHAR(20) NULL DEFAULT NULL COMMENT '地域/道路名' ,
  `r_address` VARCHAR(200) NULL DEFAULT NULL COMMENT '地址' ,
  `r_area` VARCHAR(50) NULL DEFAULT NULL COMMENT '小区' ,
  `r_floor` VARCHAR(10) NULL DEFAULT NULL COMMENT '楼层' ,
  `r_tfloor` VARCHAR(10) NULL DEFAULT NULL COMMENT '总楼层，楼高' ,
  `r_layout` VARCHAR(10) NULL DEFAULT NULL COMMENT '户型（例如一房一厅）' ,
  `r_no` VARCHAR(10) NULL DEFAULT NULL COMMENT '房间号码' ,
  `r_view` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '景观标签，二进制值' ,
  `r_type` VARCHAR(10) NULL DEFAULT NULL COMMENT '房型' ,
  `r_desc` VARCHAR(500) NULL DEFAULT NULL COMMENT '房间描述 限500字' ,
  `r_mpic` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '主图ID' ,
  `r_lpic` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '户型图ID' ,
  `r_note` VARCHAR(300) NULL DEFAULT NULL COMMENT '房客须知' ,
  `r_price` INT(10) UNSIGNED NOT NULL COMMENT '基础价格（单位：分）' ,
  `r_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '客房状态' ,
  `r_maxnum` INT(10) UNSIGNED NULL DEFAULT '2' COMMENT '可住人数上限' ,
  `r_btime` INT(10) NOT NULL DEFAULT '0' COMMENT '房间停用开始时间' ,
  `r_etime` INT(10) NOT NULL DEFAULT '0' COMMENT '房间停用结束时间' ,
  `r_otime` INT(10) NOT NULL DEFAULT '0' COMMENT '房间实际开张时间' ,
  `r_ctime` INT(10) UNSIGNED NOT NULL COMMENT '房间创建时间' ,
  `r_mtime` INT(10) UNSIGNED NOT NULL COMMENT '最后更改时间' ,
  PRIMARY KEY (`r_id`) ,
  INDEX `IDX_HID` (`r_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '房间信息表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_room_price`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_room_price` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_room_price` (
  `rp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  `rp_hid` INT(10) UNSIGNED NOT NULL COMMENT '旅店ID' ,
  `rp_rid` INT(10) UNSIGNED NOT NULL COMMENT '房间ID' ,
  `rp_uid` INT(10) UNSIGNED NOT NULL COMMENT '用户ID' ,
  `rp_uname` VARCHAR(20) NOT NULL COMMENT '用户姓名' ,
  `rp_value` INT(10) UNSIGNED NOT NULL COMMENT '价格（分）' ,
  `rp_btime` INT(10) UNSIGNED NOT NULL COMMENT '起始时间' ,
  `rp_etime` INT(10) UNSIGNED NOT NULL COMMENT '结束时间' ,
  `rp_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建时间' ,
  `rp_mtime` INT(10) UNSIGNED NOT NULL COMMENT '修改时间' ,
  PRIMARY KEY (`rp_id`) ,
  INDEX `IDX_BTIME` (`rp_btime` ASC) ,
  INDEX `IDX_ETIME` (`rp_etime` ASC) ,
  INDEX `IDX_RID` (`rp_rid` ASC) ,
  INDEX `IDX_HID` (`rp_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '房间价格体系表';


-- -----------------------------------------------------
-- Table `ftt_osv`.`ftt_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ftt_osv`.`ftt_user` ;

CREATE  TABLE IF NOT EXISTS `ftt_osv`.`ftt_user` (
  `u_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID' ,
  `u_hid` INT(10) UNSIGNED NOT NULL COMMENT '所属旅店ID' ,
  `u_sign` VARCHAR(100) NOT NULL COMMENT '帐号' ,
  `u_pswd` VARCHAR(32) NULL DEFAULT NULL COMMENT '密码' ,
  `u_salt` VARCHAR(4) NULL DEFAULT NULL COMMENT '密钥' ,
  `u_role` TINYINT(3) UNSIGNED NULL DEFAULT '0' COMMENT '用户角色' ,
  `u_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '邮箱' ,
  `u_phone` VARCHAR(20) NULL DEFAULT NULL COMMENT '手机' ,
  `u_ctime` INT(10) UNSIGNED NOT NULL COMMENT '创建日期' ,
  `u_mtime` INT(10) UNSIGNED NOT NULL COMMENT '最后活跃日期' ,
  `u_atime` INT(10) UNSIGNED NOT NULL COMMENT '最后操作时间（登录）' ,
  `u_permit` INT(10) NOT NULL DEFAULT '0' COMMENT '附加权限值' ,
  `u_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '0-冻结 1-活跃' ,
  `u_rolename` VARCHAR(20) NULL DEFAULT NULL COMMENT '角色名称' ,
  `u_realname` VARCHAR(20) NULL DEFAULT NULL COMMENT '真实姓名' ,
  `u_nickname` VARCHAR(20) NULL DEFAULT NULL COMMENT '系统昵称' ,
  `u_gender` TINYINT(3) UNSIGNED NOT NULL COMMENT '0-男性 1-女性' ,
  `u_idtype` TINYINT(3) UNSIGNED NOT NULL COMMENT '证件类型' ,
  `u_idno` VARCHAR(30) NULL DEFAULT NULL COMMENT '证件号码' ,
  `u_active` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户活跃标识' ,
  PRIMARY KEY (`u_id`) ,
  UNIQUE INDEX `IDX_SIGN` (`u_sign` ASC) ,
  INDEX `IDX_HID` (`u_hid` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '店员信息表';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
