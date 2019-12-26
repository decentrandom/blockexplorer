CREATE TABLE IF NOT EXISTS `decentrandom_blocks` (
  `height` bigint(20) unsigned NOT NULL,
  `block_hash` varchar(64) NOT NULL,
  `block_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `proposer` varchar(64) NOT NULL,
  `number_of_txs` int(10) unsigned NOT NULL,
  UNIQUE KEY `height` (`height`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `decentrandom_txs` (
  `hash` varchar(64) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tx_type` varchar(64) DEFAULT NULL,
  `height` bigint(20) unsigned NOT NULL,
  `fee` bigint(20) unsigned DEFAULT NULL,
  `gas_used` bigint(20) unsigned DEFAULT NULL,
  `gas_wanted` bigint(20) unsigned DEFAULT NULL,
  `from_address` varchar(64) DEFAULT NULL,
  `to_address` varchar(64) DEFAULT NULL,
  `source_validator_address` varchar(64) DEFAULT NULL,
  `destination_validator_address` varchar(64) DEFAULT NULL,
  `amount` bigint(20) unsigned DEFAULT NULL,
  `memo` text,
  `round_id` varchar(64) DEFAULT NULL,
  `round_owner` varchar(64) DEFAULT NULL,
  `round_msg_type` tinyint(3) unsigned DEFAULT NULL,
  `round_detail` text,
  `event_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `event_data` tinytext,
  `log_data` varchar(100) DEFAULT NULL,
  `proc_time` datetime DEFAULT NULL,
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
